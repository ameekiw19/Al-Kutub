package com.example.al_kutub.utils

import android.content.Context
import android.content.SharedPreferences
import android.util.Log
import com.example.al_kutub.api.ApiConfig
import com.example.al_kutub.api.ApiService
import com.example.al_kutub.model.FcmTokenRequest
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.tasks.await
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class FcmTokenManager @Inject constructor(
    @ApplicationContext private val context: Context,
    private val sharedPreferences: SharedPreferences,
    private val sessionManager: SessionManager
) {
    
    companion object {
        private const val FCM_TOKEN_KEY = "FCM_TOKEN"
        private const val TOKEN_SENT_KEY = "FCM_TOKEN_SENT"
        private const val TAG = "FcmTokenManager"
    }
    
    /**
     * Simpan FCM token dan kirim ke server
     */
    fun saveAndSendToken(token: String) {
        // Simpan token ke SharedPreferences
        sharedPreferences.edit()
            .putString(FCM_TOKEN_KEY, token)
            .apply()
        
        // Kirim token ke server
        CoroutineScope(Dispatchers.IO).launch {
            sendTokenToServer(token)
        }
    }
    
    /**
     * Kirim token ke server Laravel dengan retry mechanism
     */
    private suspend fun sendTokenToServer(token: String) {
        val maxRetries = 3
        var retryCount = 0
        
        while (retryCount < maxRetries) {
            try {
                // Get user token from SessionManager
                val userToken = sessionManager.getToken()
                
                if (userToken == null) {
                    Log.w(TAG, "User not logged in, skipping token sync")
                    return
                }

                val apiService = ApiConfig.getApiService(sessionManager)
                
                val request = FcmTokenRequest(
                    deviceToken = token,
                    deviceType = "android",
                    appVersion = getAppVersion()
                )
                
                Log.d(TAG, "🔍 FCM Request Debug:")
                Log.d(TAG, "  - Device token length: ${token.length}")
                Log.d(TAG, "  - Device Type: ${request.deviceType}")
                Log.d(TAG, "  - App Version: ${request.appVersion}")
                Log.d(TAG, "  - Request JSON: ${com.google.gson.Gson().toJson(request)}")
                
                val response = apiService.saveFcmToken(
                    authorization = "Bearer $userToken",
                    request = request
                )

                if (response.isSuccessful) {
                    Log.d(TAG, "✅ FCM token sent to server successfully")
                    val responseBody = response.body()
                    if (responseBody?.success == true) {
                        Log.d(TAG, "Server response: ${responseBody.message}")
                        sharedPreferences.edit()
                            .putBoolean(TOKEN_SENT_KEY, true)
                            .apply()
                    }
                    return // Success, exit retry loop
                } else {
                    Log.e(TAG, "❌ Failed to send FCM token to server: ${response.code()}")
                    Log.e(TAG, "Error body: ${response.errorBody()?.string()}")
                }

            } catch (e: Exception) {
                Log.e(TAG, "❌ Exception sending FCM token to server (attempt ${retryCount + 1}/$maxRetries)", e)
                
                // Check if it's a timeout or network error
                if (e is java.net.SocketTimeoutException || e is java.net.SocketException) {
                    retryCount++
                    if (retryCount < maxRetries) {
                        // Exponential backoff: 1s, 2s, 4s
                        val delayMs = 1000L * (1 shl (retryCount - 1))
                        Log.d(TAG, "Retrying FCM token sync in ${delayMs}ms...")
                        kotlinx.coroutines.delay(delayMs)
                        continue
                    }
                } else {
                    // For other exceptions, don't retry
                    break
                }
            }
        }
        
        if (retryCount >= maxRetries) {
            Log.e(TAG, "❌ Failed to send FCM token after $maxRetries attempts")
        }
    }
    
    /**
     * Get FCM token yang tersimpan
     */
    fun getStoredToken(): String? {
        return sharedPreferences.getString(FCM_TOKEN_KEY, null)
    }
    
    /**
     * Cek apakah token sudah pernah dikirim ke server
     */
    fun isTokenSent(): Boolean {
        return sharedPreferences.getBoolean(TOKEN_SENT_KEY, false)
    }
    
    /**
     * Reset token sent status (untuk saat user logout/login)
     */
    fun resetTokenSentStatus() {
        sharedPreferences.edit()
            .remove(TOKEN_SENT_KEY)
            .apply()
    }
    
    /**
     * Ambil token terbaru dari Firebase dan kirim ke server.
     * Berguna setelah login/register untuk memastikan server punya token terbaru.
     */
    fun fetchAndSyncToken() {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                Log.d(TAG, "Fetching current FCM token for synchronization...")
                val token = com.google.firebase.messaging.FirebaseMessaging.getInstance().token.await()
                saveAndSendToken(token)
            } catch (e: Exception) {
                Log.e(TAG, "Failed to fetch FCM token for sync", e)
            }
        }
    }

    /**
     * Get app version
     */
    private fun getAppVersion(): String {
        return try {
            val packageManager = context.packageManager
            val packageInfo = packageManager.getPackageInfo(context.packageName, 0)
            packageInfo.versionName ?: "1.0"
        } catch (e: Exception) {
            "1.0"
        }
    }
}
