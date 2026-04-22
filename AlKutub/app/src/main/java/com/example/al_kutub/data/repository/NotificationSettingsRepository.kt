package com.example.al_kutub.data.repository

import com.example.al_kutub.api.ApiService
import com.example.al_kutub.model.NotificationPreferences
import com.example.al_kutub.model.toApiRequest
import com.example.al_kutub.utils.SessionManager
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class NotificationSettingsRepository @Inject constructor(
    private val apiService: ApiService,
    private val sessionManager: SessionManager
) {

    fun getCachedPreferences(): NotificationPreferences {
        return sessionManager.getNotificationPreferences()
    }

    fun saveToCache(preferences: NotificationPreferences) {
        sessionManager.saveNotificationPreferences(preferences)
    }

    suspend fun getFromServer(): Result<NotificationPreferences> {
        return try {
            val token = sessionManager.getToken()
                ?: return Result.failure(Exception("AUTH_401: Silakan login terlebih dahulu"))

            val response = apiService.getNotificationSettings("Bearer $token")
            if (response.isSuccessful) {
                val body = response.body()
                if (body?.success == true && body.data != null) {
                    Result.success(body.data)
                } else {
                    Result.failure(Exception(body?.message ?: "Gagal memuat pengaturan notifikasi"))
                }
            } else {
                Result.failure(Exception("HTTP ${response.code()}: ${response.message()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun updateOnServer(preferences: NotificationPreferences): Result<NotificationPreferences> {
        return try {
            val token = sessionManager.getToken()
                ?: return Result.failure(Exception("AUTH_401: Silakan login terlebih dahulu"))

            val response = apiService.updateNotificationSettings(
                request = preferences.toApiRequest(),
                authorization = "Bearer $token"
            )

            if (response.isSuccessful) {
                val body = response.body()
                if (body?.success == true && body.data != null) {
                    Result.success(body.data)
                } else {
                    Result.failure(Exception(body?.message ?: "Gagal menyimpan pengaturan notifikasi"))
                }
            } else {
                Result.failure(Exception("HTTP ${response.code()}: ${response.message()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }
}
