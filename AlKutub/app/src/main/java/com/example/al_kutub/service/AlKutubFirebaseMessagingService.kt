package com.example.al_kutub.service

import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.os.Build
import android.util.Log
import androidx.core.app.NotificationCompat
import com.example.al_kutub.MainActivity
import com.example.al_kutub.R
import com.example.al_kutub.model.NotificationPreferences
import com.example.al_kutub.model.NotificationType
import com.example.al_kutub.utils.FcmTokenManager
import com.example.al_kutub.utils.FcmTestHelper
import com.example.al_kutub.utils.SessionManager
import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage
import dagger.hilt.android.AndroidEntryPoint
import javax.inject.Inject

@AndroidEntryPoint
class AlKutubFirebaseMessagingService : FirebaseMessagingService() {

    @Inject
    lateinit var fcmTokenManager: FcmTokenManager
    
    @Inject
    lateinit var fcmTestHelper: FcmTestHelper

    @Inject
    lateinit var sessionManager: SessionManager

    companion object {
        private const val TAG = "FCMService"
    }

    override fun onMessageReceived(remoteMessage: RemoteMessage) {
        super.onMessageReceived(remoteMessage)
        
        Log.d(TAG, "Received FCM message from: ${remoteMessage.from}")
        Log.d(TAG, "Message data: ${remoteMessage.data}")
        Log.d(TAG, "Message notification: ${remoteMessage.notification}")
        
        try {
            // Extract data dengan fallback yang lebih baik
            val title = remoteMessage.notification?.title 
                ?: remoteMessage.data["title"] 
                ?: "📚 Kitab Baru"
                
            val message = remoteMessage.notification?.body 
                ?: remoteMessage.data["message"] 
                ?: "Ada kitab baru yang tersedia! Yuk baca sekarang."
                
            val kitabId = remoteMessage.data["kitab_id"]
            val type = remoteMessage.data["type"] ?: "new_kitab"

            val preferences = sessionManager.getNotificationPreferences()
            val mappedType = mapNotificationType(type)
            val shouldShow = mappedType?.let { preferences.shouldShowNotification(it) }
                ?: (preferences.enableNotifications && !preferences.isInQuietHours())
            if (!shouldShow) {
                Log.d(TAG, "Notification skipped by user settings. type=$type")
                return
            }
            
            Log.d(TAG, "Processed notification - Title: $title, Type: $type, KitabID: $kitabId")
            
            // Show notification dengan proper error handling
            showNotification(title, message, kitabId, type, preferences)
            
        } catch (e: Exception) {
            Log.e(TAG, "Error processing FCM message", e)
            // Fallback notification
            showNotification(
                "Al-Kutub", 
                "Notifikasi baru tersedia", 
                null, 
                "general",
                sessionManager.getNotificationPreferences()
            )
        }
    }

    override fun onNewToken(token: String) {
        super.onNewToken(token)
        
        Log.d(TAG, "New FCM token generated: $token")
        
        // Log token untuk debugging
        fcmTestHelper.logFcmToken(token)
        
        // Simpan dan kirim FCM token ke server menggunakan FcmTokenManager
        fcmTokenManager.saveAndSendToken(token)
    }

    private fun showNotification(
        title: String,
        message: String,
        kitabId: String?,
        type: String = "general",
        preferences: NotificationPreferences
    ) {
        Log.d(TAG, "Creating notification: $title - $message (type: $type)")
        
        val channelId = com.example.al_kutub.utils.NotificationConstants.CHANNEL_ID
        val notificationId = System.currentTimeMillis().toInt()

        val notificationManager = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager

        // Create notification channel untuk Android O+
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val channel = NotificationChannel(
                channelId,
                com.example.al_kutub.utils.NotificationConstants.CHANNEL_NAME,
                NotificationManager.IMPORTANCE_HIGH
            ).apply {
                description = com.example.al_kutub.utils.NotificationConstants.CHANNEL_DESCRIPTION
                enableLights(true)
                enableVibration(true)
                setShowBadge(true)
            }
            notificationManager.createNotificationChannel(channel)
        }

        // Create Intent berdasarkan type
        val intent = Intent(this, MainActivity::class.java).apply {
            flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
            
            when (type) {
                "new_kitab" -> {
                    kitabId?.let { id ->
                        putExtra("kitab_id", id.toIntOrNull() ?: -1)
                        putExtra("open_kitab_detail", true)
                    }
                }
                "general" -> {
                    putExtra("open_notifications", true)
                }
            }
        }

        val pendingIntent = PendingIntent.getActivity(
            this,
            0,
            intent,
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )

        // Choose icon berdasarkan type
        val iconRes = when (type) {
            "new_kitab" -> R.drawable.ic_logo
            else -> R.drawable.ic_notification
        }

        val builder = NotificationCompat.Builder(this, channelId)
            .setSmallIcon(iconRes)
            .setContentTitle(title)
            .setContentText(message)
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setAutoCancel(true)
            .setContentIntent(pendingIntent)
            .setStyle(NotificationCompat.BigTextStyle().bigText(message))

        var defaults = 0
        if (preferences.soundEnabled) defaults = defaults or NotificationCompat.DEFAULT_SOUND
        if (preferences.vibrationEnabled) defaults = defaults or NotificationCompat.DEFAULT_VIBRATE
        if (defaults != 0) {
            builder.setDefaults(defaults)
        }

        try {
            notificationManager.notify(notificationId, builder.build())
            Log.d(TAG, "Notification displayed with ID: $notificationId")
        } catch (e: Exception) {
            Log.e(TAG, "Error showing notification", e)
        }
    }

    private fun mapNotificationType(rawType: String): NotificationType? {
        return when (rawType.lowercase()) {
            "new_kitab", "new_book" -> NotificationType.NEW_BOOK
            "update", "kitab_update" -> NotificationType.UPDATE
            "reminder", "reading_reminder" -> NotificationType.REMINDER
            else -> null
        }
    }
}
