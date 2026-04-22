package com.example.al_kutub.services

import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.os.Build
import androidx.core.app.NotificationCompat
import androidx.core.app.NotificationManagerCompat
import com.example.al_kutub.MainActivity
import com.example.al_kutub.R
import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch

class FcmService : FirebaseMessagingService() {

    override fun onMessageReceived(remoteMessage: RemoteMessage) {
        // Handle FCM message received
        remoteMessage.notification?.let {
            showNotification(
                title = it.title ?: "AlKutub",
                body = it.body ?: "Ada update terbaru",
                data = remoteMessage.data
            )
        }

        // Handle data messages
        remoteMessage.data.let { data ->
            if (data.isNotEmpty()) {
                handleDataMessage(data)
            }
        }
    }

    override fun onNewToken(token: String) {
        // Handle new FCM token
        super.onNewToken(token)
        
        // Save token to server
        CoroutineScope(Dispatchers.IO).launch {
            try {
                // Save token to your server
                // apiService.saveFcmToken(token)
            } catch (e: Exception) {
                // Handle error
            }
        }
    }

    private fun showNotification(title: String, body: String, data: Map<String, String>) {
        createNotificationChannel()

        // Create intent for notification click
        val intent = Intent(this, MainActivity::class.java).apply {
            flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
            putExtra("notification_type", data["type"])
            putExtra("kitab_id", data["kitab_id"])
            putExtra("action_url", data["action_url"])
        }

        val pendingIntent = PendingIntent.getActivity(
            this, 0, intent,
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )

        val notificationBuilder = NotificationCompat.Builder(this, CHANNEL_ID)
            .setSmallIcon(R.drawable.ic_notification) // Make sure this drawable exists
            .setContentTitle(title)
            .setContentText(body)
            .setAutoCancel(true)
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setContentIntent(pendingIntent)
            .setStyle(NotificationCompat.BigTextStyle().bigText(body))

        // Handle different notification types
        when (data["type"]) {
            "new_kitab" -> {
                notificationBuilder
                    .setStyle(NotificationCompat.BigTextStyle().bigText(body))
                    .addAction(
                        R.drawable.ic_logo, // Use existing icon
                        "Buka",
                        pendingIntent
                    )
            }
            "update" -> {
                notificationBuilder.setPriority(NotificationCompat.PRIORITY_DEFAULT)
            }
        }

        with(NotificationManagerCompat.from(this)) {
            notify(NOTIFICATION_ID, notificationBuilder.build())
        }
    }

    private fun handleDataMessage(data: Map<String, String>) {
        // Handle silent data messages
        when (data["type"]) {
            "new_kitab" -> {
                // Refresh notifications in app
                sendBroadcast(Intent("com.example.al_kutub.NEW_KITAB").apply {
                    putExtra("kitab_id", data["kitab_id"])
                    putExtra("judul", data["judul"])
                })
            }
            "sync_required" -> {
                // Trigger sync
                sendBroadcast(Intent("com.example.al_kutub.SYNC_REQUIRED"))
            }
        }
    }

    private fun createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val channel = NotificationChannel(
                CHANNEL_ID,
                "AlKutub Notifications",
                NotificationManager.IMPORTANCE_HIGH
            ).apply {
                description = "Notifikasi dari aplikasi AlKutub"
                enableLights(true)
                enableVibration(true)
            }

            val notificationManager = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
            notificationManager.createNotificationChannel(channel)
        }
    }

    companion object {
        private const val CHANNEL_ID = "alkutub_notifications"
        private const val NOTIFICATION_ID = 1001
    }
}
