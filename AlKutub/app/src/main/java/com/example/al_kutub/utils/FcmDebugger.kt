package com.example.al_kutub.utils

import android.util.Log
import com.google.firebase.messaging.FirebaseMessaging
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.tasks.await
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class FcmDebugger @Inject constructor() {
    
    private val TAG = "FcmDebugger"
    
    /**
     * Debug FCM initialization
     */
    fun debugFcmInitialization() {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                Log.d(TAG, "🔥 Starting FCM initialization...")
                
                // Get FCM token
                val token = FirebaseMessaging.getInstance().token.await()
                Log.d(TAG, "✅ FCM Token obtained: $token")
                Log.d(TAG, "📱 Token length: ${token.length}")
                
                // Test token format
                if (token.startsWith("fcm_") || token.length > 100) {
                    Log.d(TAG, "✅ Token format looks valid")
                } else {
                    Log.w(TAG, "⚠️ Token format might be invalid")
                }
                
                // Log instructions for testing
                logTestInstructions(token)
                
            } catch (e: Exception) {
                Log.e(TAG, "❌ FCM initialization failed", e)
                Log.e(TAG, "Error type: ${e::class.java.simpleName}")
                Log.e(TAG, "Error message: ${e.message}")
            }
        }
    }
    
    /**
     * Log test instructions
     */
    private fun logTestInstructions(token: String) {
        Log.d(TAG, "🧪 Testing Instructions:")
        Log.d(TAG, "1. Copy this token: $token")
        Log.d(TAG, "2. Go to Firebase Console")
        Log.d(TAG, "3. Cloud Messaging > Send your first message")
        Log.d(TAG, "4. Paste token in 'FCM registration token'")
        Log.d(TAG, "5. Send test notification")
    }
    
    /**
     * Check Firebase configuration
     */
    fun checkFirebaseConfig() {
        Log.d(TAG, "🔍 Checking Firebase configuration...")
        
        try {
            // Check if Firebase is initialized
            val app = com.google.firebase.FirebaseApp.getInstance()
            Log.d(TAG, "✅ Firebase app name: ${app.name}")
            Log.d(TAG, "✅ Firebase options: ${app.options}")
            
            // Check project ID
            val projectId = app.options.projectId
            if (projectId != null) {
                Log.d(TAG, "✅ Project ID: $projectId")
            } else {
                Log.w(TAG, "⚠️ Project ID is null")
            }
            
            // Check API key
            val apiKey = app.options.apiKey
            if (apiKey.isNotBlank()) {
                Log.d(TAG, "✅ API Key: ${apiKey.take(20)}...")
            } else {
                Log.e(TAG, "❌ API Key is missing or empty")
            }
            
        } catch (e: Exception) {
            Log.e(TAG, "❌ Firebase configuration check failed", e)
        }
    }
}
