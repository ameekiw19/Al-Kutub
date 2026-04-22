package com.example.al_kutub.data.repository

import android.content.Context
import android.util.Log
import com.example.al_kutub.api.ApiService
import com.example.al_kutub.data.local.dao.DownloadTaskDao
import com.example.al_kutub.data.local.dao.DownloadedKitabDao
import com.example.al_kutub.data.local.entity.DownloadTaskEntity
import com.example.al_kutub.data.local.entity.DownloadedKitabEntity
import com.example.al_kutub.model.DownloadTaskUiState
import com.example.al_kutub.utils.SessionManager
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.Job
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.cancel
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.launch
import kotlinx.coroutines.sync.Mutex
import kotlinx.coroutines.sync.withLock
import java.io.File
import java.io.RandomAccessFile
import java.util.concurrent.ConcurrentHashMap
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class DownloadManagerRepository @Inject constructor(
    private val apiService: ApiService,
    private val downloadTaskDao: DownloadTaskDao,
    private val downloadedKitabDao: DownloadedKitabDao,
    private val sessionManager: SessionManager,
    @ApplicationContext private val context: Context
) {
    private val tag = "DownloadManagerRepository"
    private val scope = CoroutineScope(SupervisorJob() + Dispatchers.IO)
    private val activeJobs = ConcurrentHashMap<Long, Job>()
    private val taskMutex = Mutex()

    fun observeTasks(): Flow<List<DownloadTaskUiState>> {
        val userId = sessionManager.getUserId()
        return downloadTaskDao.observeForUser(userId).map { tasks ->
            tasks.map { task ->
                DownloadTaskUiState(
                    taskId = task.taskId,
                    kitabId = task.kitabId,
                    title = task.title,
                    status = task.status,
                    downloadedBytes = task.downloadedBytes,
                    totalBytes = task.totalBytes,
                    progressPercent = task.progressPercent,
                    errorMessage = task.errorMessage
                )
            }
        }
    }

    suspend fun enqueueDownload(kitabId: Int, title: String): Result<Long> {
        return taskMutex.withLock {
            val userId = sessionManager.getUserId()
            val token = sessionManager.getToken()
            if (userId <= 0 || token.isNullOrBlank()) {
                return@withLock Result.failure(IllegalStateException("Silakan login ulang"))
            }

            val pdfDir = File(context.filesDir, "pdfs").apply { mkdirs() }
            val safeName = "kitab_${kitabId}.pdf"
            val targetFile = File(pdfDir, safeName)
            val tempFile = File(pdfDir, "$safeName.part")

            val existing = downloadTaskDao.getByUserAndKitab(userId, kitabId)
            val base = existing ?: DownloadTaskEntity(
                userId = userId,
                kitabId = kitabId,
                title = title,
                fileName = safeName,
                targetPath = targetFile.absolutePath,
                tempPath = tempFile.absolutePath
            )

            val updated = base.copy(
                title = title,
                status = DownloadTaskEntity.STATUS_QUEUED,
                errorMessage = null,
                updatedAt = System.currentTimeMillis(),
                downloadedBytes = if (tempFile.exists()) tempFile.length() else 0L
            )
            val taskId = if (base.taskId > 0) {
                downloadTaskDao.upsert(updated)
                base.taskId
            } else {
                downloadTaskDao.upsert(updated)
            }

            startDownload(taskId, "Bearer $token")
            Result.success(taskId)
        }
    }

    suspend fun pauseTask(taskId: Long) {
        activeJobs.remove(taskId)?.cancel()
        val task = downloadTaskDao.getById(taskId) ?: return
        downloadTaskDao.update(
            task.copy(
                status = DownloadTaskEntity.STATUS_PAUSED,
                updatedAt = System.currentTimeMillis()
            )
        )
    }

    suspend fun resumeTask(taskId: Long): Result<Unit> {
        val token = sessionManager.getToken() ?: return Result.failure(IllegalStateException("Silakan login ulang"))
        val task = downloadTaskDao.getById(taskId) ?: return Result.failure(IllegalArgumentException("Task tidak ditemukan"))
        if (task.status == DownloadTaskEntity.STATUS_COMPLETED) return Result.success(Unit)
        startDownload(taskId, "Bearer $token")
        return Result.success(Unit)
    }

    suspend fun retryTask(taskId: Long): Result<Unit> {
        val task = downloadTaskDao.getById(taskId) ?: return Result.failure(IllegalArgumentException("Task tidak ditemukan"))
        downloadTaskDao.update(
            task.copy(
                status = DownloadTaskEntity.STATUS_QUEUED,
                errorMessage = null,
                updatedAt = System.currentTimeMillis()
            )
        )
        return resumeTask(taskId)
    }

    suspend fun cancelTask(taskId: Long) {
        activeJobs.remove(taskId)?.cancel()
        val task = downloadTaskDao.getById(taskId) ?: return
        File(task.tempPath).delete()
        downloadTaskDao.update(
            task.copy(
                status = DownloadTaskEntity.STATUS_CANCELED,
                updatedAt = System.currentTimeMillis(),
                errorMessage = null
            )
        )
    }

    suspend fun processPendingTasks() {
        val userId = sessionManager.getUserId()
        val token = sessionManager.getToken() ?: return
        val tasks = downloadTaskDao.getPendingTasks(userId)
        tasks.forEach { task ->
            if (task.status != DownloadTaskEntity.STATUS_COMPLETED && !activeJobs.containsKey(task.taskId)) {
                startDownload(task.taskId, "Bearer $token")
            }
        }
    }

    suspend fun clearDownloadCacheForCurrentUser() {
        val userId = sessionManager.getUserId()
        activeJobs.values.forEach { it.cancel() }
        activeJobs.clear()

        val taskRows = downloadTaskDao.getAllForUser(userId)
        taskRows.forEach { task ->
            File(task.tempPath).delete()
            File(task.targetPath).delete()
        }

        downloadTaskDao.clearForUser(userId)
        downloadedKitabDao.clearAllDownloadedKitabs()
    }

    suspend fun clearAllLocalDownloads() {
        val userId = sessionManager.getUserId()
        activeJobs.values.forEach { it.cancel() }
        activeJobs.clear()

        val pdfDir = File(context.filesDir, "pdfs")
        if (pdfDir.exists()) {
            pdfDir.listFiles()?.forEach { it.delete() }
        }

        downloadTaskDao.clearForUser(userId)
        downloadedKitabDao.clearAllDownloadedKitabs()
    }

    private fun startDownload(taskId: Long, authorization: String) {
        if (activeJobs.containsKey(taskId)) return
        val job = scope.launch {
            runCatching {
                performDownload(taskId, authorization)
            }.onFailure { e ->
                Log.e(tag, "Download task $taskId gagal", e)
                markFailed(taskId, e.message ?: "Unknown error")
            }.also {
                activeJobs.remove(taskId)
            }
        }
        activeJobs[taskId] = job
    }

    private suspend fun performDownload(taskId: Long, authorization: String) {
        val task = downloadTaskDao.getById(taskId) ?: return
        val tempFile = File(task.tempPath)
        val targetFile = File(task.targetPath)
        tempFile.parentFile?.mkdirs()

        var startBytes = if (tempFile.exists()) tempFile.length() else 0L
        val rangeHeader = if (startBytes > 0) "bytes=$startBytes-" else null

        downloadTaskDao.update(
            task.copy(
                status = DownloadTaskEntity.STATUS_DOWNLOADING,
                downloadedBytes = startBytes,
                updatedAt = System.currentTimeMillis(),
                errorMessage = null
            )
        )

        val response = apiService.downloadKitab(task.kitabId, authorization, rangeHeader)
        if (!response.isSuccessful || response.body() == null) {
            throw IllegalStateException("Download HTTP ${response.code()}: ${response.message()}")
        }

        val body = response.body() ?: throw IllegalStateException("Body kosong")

        if (response.code() == 200 && startBytes > 0) {
            // Server mengirim full response, lanjut dari awal supaya file tidak korup.
            tempFile.delete()
            startBytes = 0L
        }

        val contentRange = response.headers()["Content-Range"]
        val totalBytes = parseTotalBytes(contentRange, body.contentLength(), startBytes)

        RandomAccessFile(tempFile, "rw").use { output ->
            if (startBytes > 0) {
                output.seek(startBytes)
            }
            body.byteStream().use { input ->
                val buffer = ByteArray(8192)
                var read: Int
                var downloaded = startBytes
                var lastProgressUpdate = System.currentTimeMillis()
                while (input.read(buffer).also { read = it } != -1) {
                    output.write(buffer, 0, read)
                    downloaded += read

                    val now = System.currentTimeMillis()
                    if (now - lastProgressUpdate >= 250) {
                        updateProgress(taskId, downloaded, totalBytes)
                        lastProgressUpdate = now
                    }
                }
                updateProgress(taskId, downloaded, totalBytes)
            }
        }

        if (targetFile.exists()) {
            targetFile.delete()
        }
        if (!tempFile.renameTo(targetFile)) {
            throw IllegalStateException("Gagal menyimpan file final")
        }

        val finalTask = downloadTaskDao.getById(taskId) ?: task
        downloadTaskDao.update(
            finalTask.copy(
                status = DownloadTaskEntity.STATUS_COMPLETED,
                downloadedBytes = targetFile.length(),
                totalBytes = targetFile.length(),
                progressPercent = 100,
                updatedAt = System.currentTimeMillis(),
                errorMessage = null
            )
        )

        downloadedKitabDao.insertDownloadedKitab(
            DownloadedKitabEntity(
                kitabId = task.kitabId,
                title = task.title,
                filePath = targetFile.absolutePath,
                coverPath = ""
            )
        )
    }

    private suspend fun updateProgress(taskId: Long, downloadedBytes: Long, totalBytes: Long) {
        val current = downloadTaskDao.getById(taskId) ?: return
        val percent = if (totalBytes > 0) {
            ((downloadedBytes * 100) / totalBytes).toInt().coerceIn(0, 100)
        } else {
            0
        }
        downloadTaskDao.update(
            current.copy(
                downloadedBytes = downloadedBytes,
                totalBytes = totalBytes,
                progressPercent = percent,
                updatedAt = System.currentTimeMillis()
            )
        )
    }

    private suspend fun markFailed(taskId: Long, reason: String) {
        val current = downloadTaskDao.getById(taskId) ?: return
        downloadTaskDao.update(
            current.copy(
                status = DownloadTaskEntity.STATUS_FAILED,
                errorMessage = reason,
                updatedAt = System.currentTimeMillis()
            )
        )
    }

    private fun parseTotalBytes(contentRange: String?, contentLength: Long, startBytes: Long): Long {
        if (!contentRange.isNullOrBlank()) {
            val total = contentRange.substringAfter("/").toLongOrNull()
            if (total != null && total > 0) return total
        }
        return if (contentLength > 0) startBytes + contentLength else 0L
    }

    fun close() {
        scope.cancel()
    }
}
