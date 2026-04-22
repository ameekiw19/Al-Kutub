package com.example.al_kutub.utils

import android.util.Log
import com.example.al_kutub.model.FcmNotificationRequest
import com.example.al_kutub.model.FcmNotification
import com.google.gson.Gson
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.OkHttpClient
import okhttp3.Request
import okhttp3.RequestBody.Companion.toRequestBody
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class FcmTestHelper @Inject constructor(
    private val gson: Gson
) {
    
    private val TAG = "FcmTestHelper"
    private val FCM_URL = "https://fcm.googleapis.com/fcm/send"
    
    // Ganti dengan server key dari Firebase Console
    private val SERVER_KEY = "YOUR_FIREBASE_SERVER_KEY_HERE"
    
    /**
     * Test kirim notifikasi FCM secara manual
     * Ini untuk testing purposes - bukan untuk production
     */
    suspend fun sendTestNotification(
        fcmToken: String,
        title: String = "Kitab Baru Test",
        message: String = "Ini adalah notifikasi test dari Al-Kutub",
        kitabId: String = "1"
    ): Boolean = withContext(Dispatchers.IO) {
        return@withContext try {
            val notificationRequest = FcmNotificationRequest(
                to = fcmToken,
                notification = FcmNotification(
                    title = title,
                    body = message
                ),
                data = mapOf(
                    "title" to title,
                    "message" to message,
                    "kitab_id" to kitabId
                )
            )
            
            val json = gson.toJson(notificationRequest)
            val requestBody = json.toRequestBody("application/json".toMediaType())
            
            val request = Request.Builder()
                .url(FCM_URL)
                .post(requestBody)
                .addHeader("Authorization", "key=$SERVER_KEY")
                .addHeader("Content-Type", "application/json")
                .build()
            
            val client = OkHttpClient()
            val response = client.newCall(request).execute()
            
            if (response.isSuccessful) {
                Log.d(TAG, "Test notification sent successfully: ${response.body?.string()}")
                true
            } else {
                Log.e(TAG, "Failed to send test notification: ${response.code} - ${response.body?.string()}")
                false
            }
            
        } catch (e: Exception) {
            Log.e(TAG, "Error sending test notification", e)
            false
        }
    }
    
    /**
     * Log FCM token untuk debugging
     */
    fun logFcmToken(token: String) {
        Log.d(TAG, "FCM token updated (length=${token.length})")
        Log.d(TAG, "Test dengan Firebase Console:")
        Log.d(TAG, "1. Buka Firebase Console")
        Log.d(TAG, "2. Pilih Project Al-Kutub")
        Log.d(TAG, "3. Cloud Messaging > Kirim pesan pertama")
        Log.d(TAG, "4. Masukkan token ini sebagai target")
    }
}
