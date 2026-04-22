package com.example.al_kutub.data.repository

import android.content.Context
import android.util.Log
import com.example.al_kutub.api.ApiService
import com.example.al_kutub.data.local.dao.DownloadedKitabDao
import com.example.al_kutub.data.local.entity.DownloadedKitabEntity
import com.example.al_kutub.model.Kitab
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.withContext
import okhttp3.ResponseBody
import java.io.File
import java.io.FileOutputStream
import java.io.InputStream
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class DownloadRepository @Inject constructor(
    private val apiService: ApiService,
    private val downloadedKitabDao: DownloadedKitabDao,
    @ApplicationContext private val context: Context
) {
    private val TAG = "DownloadRepository"

    /**
     * Check if kitab is downloaded
     */
    fun isKitabDownloaded(kitabId: Int): Flow<Boolean> {
        return downloadedKitabDao.isKitabDownloaded(kitabId)
    }

    /**
     * Get downloaded kitab info
     */
    suspend fun getDownloadedKitab(kitabId: Int): DownloadedKitabEntity? {
        return downloadedKitabDao.getDownloadedKitab(kitabId)
    }

    /**
     * Download kitab file and save to local storage
     */
    suspend fun downloadKitab(kitab: Kitab, token: String): Result<File> {
        return withContext(Dispatchers.IO) {
            try {
                Log.d(TAG, "Starting download for kitab: ${kitab.judul}")
                
                // 1. Call API to get file stream
                val response = apiService.downloadKitab(kitab.idKitab, "Bearer $token")
                
                if (!response.isSuccessful || response.body() == null) {
                    return@withContext Result.failure(Exception("Download failed: ${response.message()}"))
                }

                // 2. Prepare file destination
                val fileName = "kitab_${kitab.idKitab}.pdf"
                val pdfDir = File(context.filesDir, "pdfs") // Use internal storage for security
                if (!pdfDir.exists()) pdfDir.mkdirs()
                val file = File(pdfDir, fileName)

                // 3. Save stream to file
                saveToFile(response.body()!!, file)
                
                // 4. Save metadata to Room Database
                val entity = DownloadedKitabEntity(
                    kitabId = kitab.idKitab,
                    title = kitab.judul,
                    filePath = file.absolutePath,
                    coverPath = kitab.cover // We might want to download cover too later
                )
                downloadedKitabDao.insertDownloadedKitab(entity)
                
                Log.d(TAG, "Download complete and saved to DB: ${file.absolutePath}")
                Result.success(file)
            } catch (e: Exception) {
                Log.e(TAG, "Download exception", e)
                Result.failure(e)
            }
        }
    }

    private fun saveToFile(body: ResponseBody, file: File) {
        var inputStream: InputStream? = null
        var outputStream: FileOutputStream? = null

        try {
            inputStream = body.byteStream()
            outputStream = FileOutputStream(file)
            
            val buffer = ByteArray(4096)
            var bytesRead: Int
            
            while (inputStream.read(buffer).also { bytesRead = it } != -1) {
                outputStream.write(buffer, 0, bytesRead)
            }
            outputStream.flush()
        } finally {
            inputStream?.close()
            outputStream?.close()
        }
    }
}
