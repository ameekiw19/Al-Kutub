package com.example.al_kutub.data.repository

import com.example.al_kutub.api.ApiConfig
import com.example.al_kutub.data.remote.NotificationApiService
import com.example.al_kutub.model.AppNotification
import com.example.al_kutub.model.Kitab
import com.example.al_kutub.utils.SessionManager
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import java.time.Instant
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class NotificationRepository @Inject constructor(
    private val sessionManager: SessionManager
) {
    
    private val apiService: NotificationApiService by lazy {
        ApiConfig.getNotificationApiService(sessionManager)
    }
    
    private val _notifications = MutableStateFlow<List<AppNotification>>(emptyList())
    val notifications: Flow<List<AppNotification>> = _notifications.asStateFlow()
    
    private val _newKitabs = MutableStateFlow<List<Kitab>>(emptyList())
    val newKitabs: Flow<List<Kitab>> = _newKitabs.asStateFlow()
    
    private val _unreadCount = MutableStateFlow(0)
    val unreadCount: Flow<Int> = _unreadCount.asStateFlow()
    
    private var lastSyncTime: String? = null

    private fun parseCountValue(value: Any?): Int? {
        return when (value) {
            is Number -> value.toInt()
            is String -> value.toIntOrNull()
            else -> null
        }
    }

    private fun authHeaderOrNull(): String? {
        val token = sessionManager.getToken()
        return if (token.isNullOrBlank()) null else "Bearer $token"
    }
    
    suspend fun getNotifications(limit: Int = 20, page: Int = 1): Result<List<AppNotification>> {
        return try {
            val auth = authHeaderOrNull() ?: return Result.failure(Exception("AUTH_401: Silakan login terlebih dahulu"))
            val response = apiService.getNotifications(auth, limit, page)
            if (response.isSuccessful) {
                val apiResponse = response.body()
                if (apiResponse?.success == true) {
                    apiResponse.data?.let { notifications ->
                        _notifications.value = notifications
                        Result.success(notifications)
                    } ?: Result.success(emptyList())
                } else {
                    Result.failure(Exception(apiResponse?.message ?: "Unknown error"))
                }
            } else {
                Result.failure(Exception("HTTP ${response.code()}: ${response.message()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }
    
    suspend fun getLatestNotifications(limit: Int = 10): Result<List<AppNotification>> {
        return try {
            val response = apiService.getLatestNotifications(authHeaderOrNull(), limit, lastSyncTime)
            if (response.isSuccessful) {
                val apiResponse = response.body()
                if (apiResponse?.success == true) {
                    val newNotifications = apiResponse.data ?: emptyList()
                    if (newNotifications.isNotEmpty()) {
                        // Merge with existing notifications
                        val currentNotifications = _notifications.value.toMutableList()
                        newNotifications.forEach { newNotif ->
                            if (!currentNotifications.any { it.id == newNotif.id }) {
                                currentNotifications.add(0, newNotif)
                            }
                        }
                        _notifications.value = currentNotifications.take(50) // Keep only latest 50
                    }
                    Result.success(newNotifications)
                } else {
                    Result.failure(Exception(apiResponse?.message ?: "Unknown error"))
                }
            } else {
                Result.failure(Exception("HTTP ${response.code()}: ${response.message()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }
    
    suspend fun getNewKitabs(limit: Int = 10): Result<List<Kitab>> {
        return try {
            val response = apiService.getNewKitabs(authHeaderOrNull(), limit, lastSyncTime)
            if (response.isSuccessful) {
                val apiResponse = response.body()
                if (apiResponse?.success == true) {
                    val newKitabs = apiResponse.data ?: emptyList()
                    if (newKitabs.isNotEmpty()) {
                        _newKitabs.value = newKitabs
                    }
                    Result.success(newKitabs)
                } else {
                    Result.failure(Exception(apiResponse?.message ?: "Unknown error"))
                }
            } else {
                Result.failure(Exception("HTTP ${response.code()}: ${response.message()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }
    
    suspend fun getUnreadCount(): Result<Int> {
        return try {
            val auth = authHeaderOrNull() ?: return Result.failure(Exception("AUTH_401: Silakan login terlebih dahulu"))
            val response = apiService.getUnreadCount(auth)
            if (response.isSuccessful) {
                val apiResponse = response.body()
                if (apiResponse?.success == true) {
                    val count = parseCountValue(apiResponse.data?.get("unread_count")) ?: 0
                    _unreadCount.value = count
                    Result.success(count)
                } else {
                    Result.failure(Exception(apiResponse?.message ?: "Unknown error"))
                }
            } else {
                Result.failure(Exception("HTTP ${response.code()}: ${response.message()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }
    
    suspend fun markAsRead(notificationId: Int): Result<Boolean> {
        return try {
            val auth = authHeaderOrNull() ?: return Result.failure(Exception("AUTH_401: Silakan login terlebih dahulu"))
            val response = apiService.markAsRead(notificationId, auth)
            if (response.isSuccessful) {
                val apiResponse = response.body()
                if (apiResponse?.success == true) {
                    val nowIso = Instant.now().toString()
                    _notifications.value = _notifications.value.map { notification ->
                        if (notification.id == notificationId && notification.readAt.isNullOrBlank()) {
                            notification.copy(readAt = nowIso)
                        } else {
                            notification
                        }
                    }

                    val serverUnreadCount = parseCountValue(apiResponse.data?.get("unread_count"))
                    if (serverUnreadCount != null) {
                        _unreadCount.value = serverUnreadCount
                    } else {
                        getUnreadCount()
                    }
                    Result.success(true)
                } else {
                    Result.failure(Exception(apiResponse?.message ?: "Unknown error"))
                }
            } else {
                Result.failure(Exception("HTTP ${response.code()}: ${response.message()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun markAllAsRead(): Result<Int> {
        return try {
            val auth = authHeaderOrNull() ?: return Result.failure(Exception("AUTH_401: Silakan login terlebih dahulu"))
            val response = apiService.markAllAsRead(auth)
            if (response.isSuccessful) {
                val apiResponse = response.body()
                if (apiResponse?.success == true) {
                    val nowIso = Instant.now().toString()
                    val markedCount = parseCountValue(apiResponse.data?.get("marked_count")) ?: 0
                    val unreadCount = parseCountValue(apiResponse.data?.get("unread_count"))

                    _notifications.value = _notifications.value.map { notification ->
                        if (notification.readAt.isNullOrBlank()) {
                            notification.copy(readAt = nowIso)
                        } else {
                            notification
                        }
                    }
                    if (unreadCount != null) {
                        _unreadCount.value = unreadCount
                    } else {
                        getUnreadCount()
                    }
                    Result.success(markedCount)
                } else {
                    Result.failure(Exception(apiResponse?.message ?: "Unknown error"))
                }
            } else {
                Result.failure(Exception("HTTP ${response.code()}: ${response.message()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }
    
    suspend fun syncAll(): Result<Unit> {
        return try {
            // Get latest notifications
            getLatestNotifications()
            
            // Get new kitabs
            getNewKitabs()
            
            // Update unread count
            getUnreadCount()
            
            // Update last sync time
            lastSyncTime = java.time.Instant.now().toString()
            
            Result.success(Unit)
        } catch (e: Exception) {
            Result.failure(e)
        }
    }
    
    fun clearNewKitabs() {
        _newKitabs.value = emptyList()
    }
}
