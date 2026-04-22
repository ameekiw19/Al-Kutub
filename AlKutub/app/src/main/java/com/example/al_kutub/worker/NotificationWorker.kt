package com.example.al_kutub.worker

import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.os.Build
import androidx.core.app.NotificationCompat
import androidx.work.CoroutineWorker
import androidx.work.WorkerParameters
import com.example.al_kutub.MainActivity
import com.example.al_kutub.R
import com.example.al_kutub.api.ApiService
import com.example.al_kutub.model.NotificationType
import com.example.al_kutub.utils.SessionManager
import dagger.hilt.EntryPoint
import dagger.hilt.InstallIn
import dagger.hilt.android.EntryPointAccessors
import dagger.hilt.components.SingletonComponent
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext

class NotificationWorker(
    appContext: Context,
    workerParams: WorkerParameters
) : CoroutineWorker(appContext, workerParams) {

    @EntryPoint
    @InstallIn(SingletonComponent::class)
    interface NotificationWorkerEntryPoint {
        fun apiService(): ApiService
        fun sessionManager(): SessionManager
    }

    override suspend fun doWork(): Result = withContext(Dispatchers.IO) {
        try {
            android.util.Log.d("NotificationWorker", "Worker started")
            
            // Use EntryPoint to get ApiService and SessionManager
            val entryPoint = EntryPointAccessors.fromApplication(
                applicationContext,
                NotificationWorkerEntryPoint::class.java
            )
            val apiService = entryPoint.apiService()
            val sessionManager = entryPoint.sessionManager()

            val token = sessionManager.getToken()
            val authHeader = if (token != null) "Bearer $token" else null
            
            android.util.Log.d("NotificationWorker", "Fetching latest notification. Auth present: ${token != null}")
            val response = apiService.getLatestNotification(authHeader)
            
            if (response.isSuccessful) {
                val body = response.body()
                android.util.Log.d("NotificationWorker", "API Response: success=${body?.success}")
                
                if (body?.success == true) {
                    val notif = body.data
                    if (notif != null) {
                        val sharedPref = applicationContext.getSharedPreferences("AL_KUTUB_PREFS", Context.MODE_PRIVATE)
                        val lastNotifId = sharedPref.getInt("LAST_NOTIF_ID", 0)

                        android.util.Log.d("NotificationWorker", "Newest Notif ID: ${notif.id}, Last Processed ID: $lastNotifId")

                        if (notif.id > lastNotifId) {
                            val preferences = sessionManager.getNotificationPreferences()
                            val mappedType = mapNotificationType(notif.type)
                            val shouldShow = mappedType?.let { preferences.shouldShowNotification(it) }
                                ?: (preferences.enableNotifications && !preferences.isInQuietHours())
                            if (!shouldShow) {
                                android.util.Log.d("NotificationWorker", "Notification skipped by user settings")
                                return@withContext Result.success()
                            }

                            // Simpan ID baru
                            sharedPref.edit().putInt("LAST_NOTIF_ID", notif.id).apply()
                            
                            // Tampilkan Notifikasi
                            android.util.Log.d("NotificationWorker", "🚀 Triggering background notification: ${notif.title}")
                            showNotification(
                                title = notif.title,
                                message = notif.message,
                                actionUrl = notif.actionUrl,
                                soundEnabled = preferences.soundEnabled,
                                vibrationEnabled = preferences.vibrationEnabled
                            )
                        } else {
                            android.util.Log.d("NotificationWorker", "No new notifications since last check")
                        }
                    } else {
                        android.util.Log.d("NotificationWorker", "No notification data in response")
                    }
                } else {
                    android.util.Log.e("NotificationWorker", "API returned success=false: ${body?.message}")
                }
            } else {
                android.util.Log.e("NotificationWorker", "HTTP Error: ${response.code()} ${response.message()}")
                android.util.Log.e("NotificationWorker", "Error Body: ${response.errorBody()?.string()}")
            }
            Result.success()
        } catch (e: Exception) {
            android.util.Log.e("NotificationWorker", "Error in NotificationWorker", e)
            Result.retry()
        }
    }

    private fun showNotification(
        title: String,
        message: String,
        actionUrl: String?,
        soundEnabled: Boolean,
        vibrationEnabled: Boolean
    ) {
        val channelId = com.example.al_kutub.utils.NotificationConstants.CHANNEL_ID
        val notificationId = System.currentTimeMillis().toInt()

        val notificationManager = applicationContext.getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val channel = NotificationChannel(
                channelId,
                com.example.al_kutub.utils.NotificationConstants.CHANNEL_NAME,
                NotificationManager.IMPORTANCE_HIGH
            ).apply {
                description = com.example.al_kutub.utils.NotificationConstants.CHANNEL_DESCRIPTION
                enableLights(true)
                enableVibration(true)
            }
            notificationManager.createNotificationChannel(channel)
        }

        // Create Intent untuk membuka app
        val intent = Intent(applicationContext, MainActivity::class.java).apply {
            flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
            actionUrl?.let { 
                // Parse action_url untuk dapatkan kitab ID
                val kitabId = it.split("/").lastOrNull()?.toIntOrNull()
                kitabId?.let { id ->
                    putExtra("kitab_id", id)
                    putExtra("open_kitab_detail", true)
                }
            }
        }

        val pendingIntent = PendingIntent.getActivity(
            applicationContext,
            0,
            intent,
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )

        val builder = NotificationCompat.Builder(applicationContext, channelId)
            .setSmallIcon(R.drawable.ic_launcher_foreground)
            .setContentTitle(title)
            .setContentText(message)
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setAutoCancel(true)
            .setContentIntent(pendingIntent)
            .setDefaults(
                (if (soundEnabled) NotificationCompat.DEFAULT_SOUND else 0) or
                    (if (vibrationEnabled) NotificationCompat.DEFAULT_VIBRATE else 0)
            )

        notificationManager.notify(notificationId, builder.build())
    }

    private fun mapNotificationType(rawType: String?): NotificationType? {
        return when (rawType?.lowercase()) {
            "new_kitab", "new_book" -> NotificationType.NEW_BOOK
            "update", "kitab_update" -> NotificationType.UPDATE
            "reminder", "reading_reminder" -> NotificationType.REMINDER
            else -> null
        }
    }
}
