package com.example.al_kutub

import android.app.Application
import androidx.work.Constraints
import androidx.work.ExistingPeriodicWorkPolicy
import androidx.work.NetworkType
import androidx.work.PeriodicWorkRequestBuilder
import androidx.work.WorkManager
import com.example.al_kutub.data.repository.OfflineSyncRepository
import com.example.al_kutub.worker.NotificationWorker
import com.example.al_kutub.worker.OfflineSyncWorker
import com.example.al_kutub.utils.FcmTokenManager
import com.example.al_kutub.utils.FcmDebugger
import com.example.al_kutub.utils.SessionManager
import com.google.firebase.crashlytics.FirebaseCrashlytics
import com.google.firebase.messaging.FirebaseMessaging
import com.tom_roush.pdfbox.android.PDFBoxResourceLoader
import dagger.hilt.android.HiltAndroidApp
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.tasks.await
import java.util.concurrent.TimeUnit
import javax.inject.Inject

@HiltAndroidApp
class AlKutubApp : Application() {

    @Inject
    lateinit var fcmTokenManager: FcmTokenManager
    
    @Inject
    lateinit var fcmDebugger: FcmDebugger

    @Inject
    lateinit var offlineSyncRepository: OfflineSyncRepository

    @Inject
    lateinit var sessionManager: SessionManager

    override fun onCreate() {
        super.onCreate()
        PDFBoxResourceLoader.init(this)
        
        // Debug Firebase configuration first
        fcmDebugger.checkFirebaseConfig()
        
        createNotificationChannel()
        setupCrashMonitoring()
        setupNotificationWorker()
        setupOfflineSyncWorker()
        initializeFcmToken()
        triggerInitialSync()
        
        // Subscribe to topic for broadcast notifications
        FirebaseMessaging.getInstance().subscribeToTopic("all_users")
            .addOnCompleteListener { task ->
                if (task.isSuccessful) {
                    android.util.Log.d("AlKutubApp", "Subscribed to topic: all_users")
                } else {
                    android.util.Log.e("AlKutubApp", "Failed to subscribe to topic", task.exception)
                }
            }
        
        // Debug FCM initialization
        fcmDebugger.debugFcmInitialization()
    }

    private fun createNotificationChannel() {
        if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.O) {
            val channelId = com.example.al_kutub.utils.NotificationConstants.CHANNEL_ID
            val channelName = com.example.al_kutub.utils.NotificationConstants.CHANNEL_NAME
            val importance = android.app.NotificationManager.IMPORTANCE_HIGH
            val channel = android.app.NotificationChannel(channelId, channelName, importance).apply {
                description = com.example.al_kutub.utils.NotificationConstants.CHANNEL_DESCRIPTION
                enableLights(true)
                enableVibration(true)
            }
            
            val notificationManager = getSystemService(android.app.NotificationManager::class.java)
            notificationManager.createNotificationChannel(channel)
            android.util.Log.d("AlKutubApp", "Notification channel created on startup")
        }
    }

    private fun setupCrashMonitoring() {
        val crashlytics = FirebaseCrashlytics.getInstance()
        crashlytics.setCrashlyticsCollectionEnabled(!BuildConfig.DEBUG)
        crashlytics.setCustomKey("app_version", BuildConfig.VERSION_NAME)
        crashlytics.setCustomKey("build_type", if (BuildConfig.DEBUG) "debug" else "release")
        crashlytics.setCustomKey("user_id", sessionManager.getUserId())

        val previousHandler = Thread.getDefaultUncaughtExceptionHandler()
        Thread.setDefaultUncaughtExceptionHandler { thread, throwable ->
            crashlytics.recordException(throwable)
            previousHandler?.uncaughtException(thread, throwable)
        }
    }

    private fun setupNotificationWorker() {
        // WorkManager periodic interval minimum is 15 minutes.
        val workRequest = PeriodicWorkRequestBuilder<NotificationWorker>(15, TimeUnit.MINUTES)
            .build()

        WorkManager.getInstance(this).enqueueUniquePeriodicWork(
            "NewKitabNotificationWork",
            ExistingPeriodicWorkPolicy.KEEP,
            workRequest
        )
    }

    private fun setupOfflineSyncWorker() {
        val constraints = Constraints.Builder()
            .setRequiredNetworkType(NetworkType.CONNECTED)
            .build()
        val workRequest = PeriodicWorkRequestBuilder<OfflineSyncWorker>(15, TimeUnit.MINUTES)
            .setConstraints(constraints)
            .build()

        WorkManager.getInstance(this).enqueueUniquePeriodicWork(
            OfflineSyncWorker.UNIQUE_WORK_NAME,
            ExistingPeriodicWorkPolicy.KEEP,
            workRequest
        )
    }

    private fun initializeFcmToken() {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                // Get FCM token
                val token = FirebaseMessaging.getInstance().token.await()
                
                // Simpan dan kirim token ke server
                fcmTokenManager.saveAndSendToken(token)
                
            } catch (e: Exception) {
                e.printStackTrace()
            }
        }
    }

    private fun triggerInitialSync() {
        CoroutineScope(Dispatchers.IO).launch {
            if (sessionManager.isLoggedIn()) {
                runCatching { offlineSyncRepository.processQueue() }
            }
        }
    }
}
