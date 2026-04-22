package com.example.al_kutub.data.repository

import android.util.Log
import com.example.al_kutub.api.ApiService
import com.example.al_kutub.model.KitabTranscriptPayload
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class KitabTranscriptRepository @Inject constructor(
    private val apiService: ApiService
) {
    suspend fun getTranscript(idKitab: Int): Result<KitabTranscriptPayload> {
        return try {
            val response = apiService.getKitabTranscript(idKitab)

            if (response.isSuccessful) {
                val payload = response.body()?.data
                if (payload != null) {
                    Result.success(payload)
                } else {
                    Result.failure(Exception("Transcript tidak tersedia."))
                }
            } else {
                Result.failure(Exception("Gagal memuat transcript: ${response.code()}"))
            }
        } catch (error: Exception) {
            Log.e("KitabTranscriptRepo", "Failed to load transcript", error)
            Result.failure(error)
        }
    }
}
