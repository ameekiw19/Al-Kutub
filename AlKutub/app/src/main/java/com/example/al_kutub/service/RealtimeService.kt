package com.example.al_kutub.service

import android.content.Context
import android.content.SharedPreferences
import android.util.Log
import androidx.work.*
import com.example.al_kutub.websocket.WebSocketManager
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.*
import kotlinx.coroutines.flow.*
import kotlinx.coroutines.launch
import java.util.concurrent.TimeUnit
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class RealtimeService @Inject constructor(
    private val webSocketManager: WebSocketManager,
    private val context: Context,
    private val sharedPreferences: SharedPreferences
) {
    
    private val TAG = "RealtimeService"
    var isServiceRunning = false
        private set
    
    companion object {
        private const val PREF_WEBSOCKET_ENABLED = "websocket_enabled"
        private const val PREF_WEBSOCKET_URL = "websocket_url"
        private const val WORK_NAME = "RealtimeServiceWork"
        
        // Default WebSocket URL - replace with your actual WebSocket server
        private const val DEFAULT_WEBSOCKET_URL = "ws://10.0.2.2:6001/app/notifications"
        
        private const val HEARTBEAT_INTERVAL = 30000L // 30 seconds
        private const val RECONNECT_DELAY = 5000L // 5 seconds
    }
    
    fun startRealtimeService() {
        if (isServiceRunning) {
            Log.d(TAG, "Realtime service already running")
            return
        }
        
        if (!isWebSocketEnabled()) {
            Log.d(TAG, "WebSocket is disabled in settings")
            return
        }
        
        Log.d(TAG, "Starting realtime service")
        isServiceRunning = true
        
        // Start WebSocket connection
        connectWebSocket()
        
        // Schedule periodic work to keep service alive
        schedulePeriodicWork()
    }
    
    fun stopRealtimeService() {
        Log.d(TAG, "Stopping realtime service")
        isServiceRunning = false
        webSocketManager.disconnect()
        
        // Cancel periodic work
        WorkManager.getInstance(context).cancelUniqueWork(WORK_NAME)
    }
    
    private fun connectWebSocket() {
        val url = getWebSocketUrl()
        Log.d(TAG, "Connecting to WebSocket: $url")
        
        webSocketManager.connect(url)
        
        // Listen to WebSocket events
        CoroutineScope(Dispatchers.IO).launch {
            webSocketManager.connectionState.collect { state ->
                handleConnectionStateChange(state)
            }
        }
        
        CoroutineScope(Dispatchers.IO).launch {
            webSocketManager.notifications.collect { notification ->
                handleWebSocketNotification(notification)
            }
        }
        
        CoroutineScope(Dispatchers.IO).launch {
            webSocketManager.kitabUpdates.collect { update ->
                handleKitabUpdate(update)
            }
        }
    }
    
    private fun handleConnectionStateChange(state: WebSocketManager.ConnectionState) {
        Log.d(TAG, "WebSocket state changed: $state")
        
        when (state) {
            WebSocketManager.ConnectionState.CONNECTED -> {
                // Connection successful
                sendHeartbeat()
            }
            WebSocketManager.ConnectionState.DISCONNECTED -> {
                // Connection lost
                // Handle offline mode
                Log.d(TAG, "WebSocket disconnected")
            }
            WebSocketManager.ConnectionState.ERROR -> {
                // Connection error
                // Handle error state
            }
            WebSocketManager.ConnectionState.CONNECTING -> {
                // Connecting
                // Show loading state
            }
        }
    }
    
    private fun handleWebSocketNotification(notification: WebSocketManager.WebSocketNotification) {
        Log.d(TAG, "Received WebSocket notification: ${notification.type}")
        
        when (notification.type) {
            "notification" -> {
                // Handle real-time notification
                // Update UI, show notification badge, etc.
                updateNotificationBadge()
            }
            "new_kitab" -> {
                // Handle new kitab notification
                refreshKitabList()
            }
        }
    }
    
    private fun handleKitabUpdate(update: WebSocketManager.KitabUpdate) {
        Log.d(TAG, "Received kitab update: ${update.action} for kitab ${update.kitabId}")
        
        when (update.action) {
            "created" -> {
                // New kitab added
                refreshKitabList()
            }
            "updated" -> {
                // Kitab updated
                updateKitabInList(update.kitabId, update.data)
            }
            "deleted" -> {
                // Kitab deleted
                removeKitabFromList(update.kitabId)
            }
        }
    }
    
    private fun sendHeartbeat() {
        CoroutineScope(Dispatchers.IO).launch {
            while (isServiceRunning) {
                delay(HEARTBEAT_INTERVAL) // Send heartbeat every 30 seconds
                
                if (webSocketManager.connectionState.value == WebSocketManager.ConnectionState.CONNECTED) {
                    webSocketManager.sendMessage("ping", mapOf("timestamp" to System.currentTimeMillis()))
                }
            }
        }
    }
    
    private fun schedulePeriodicWork() {
        val workRequest = PeriodicWorkRequestBuilder<RealtimeWorker>(15, TimeUnit.MINUTES)
            .setConstraints(
                Constraints.Builder()
                    .setRequiredNetworkType(NetworkType.CONNECTED)
                    .build()
            )
            .setBackoffCriteria(
                BackoffPolicy.LINEAR,
                10_000L, // 10 seconds
                TimeUnit.MILLISECONDS
            )
            .build()
        
        WorkManager.getInstance(context)
            .enqueueUniquePeriodicWork(
                WORK_NAME,
                ExistingPeriodicWorkPolicy.KEEP,
                workRequest
            )
    }
    
    private fun updateNotificationBadge() {
        // Update notification badge in UI
        // This would typically emit to a shared flow or use EventBus
        Log.d(TAG, "Updating notification badge")
    }
    
    private fun refreshKitabList() {
        // Trigger kitab list refresh
        Log.d(TAG, "Refreshing kitab list")
    }
    
    private fun updateKitabInList(kitabId: Int, data: Map<String, Any>?) {
        // Update specific kitab in list
        Log.d(TAG, "Updating kitab $kitabId in list")
    }
    
    private fun removeKitabFromList(kitabId: Int) {
        // Remove kitab from list
        Log.d(TAG, "Removing kitab $kitabId from list")
    }
    
    // Settings management
    private fun isWebSocketEnabled(): Boolean {
        return sharedPreferences.getBoolean(PREF_WEBSOCKET_ENABLED, true)
    }
    
    private fun getWebSocketUrl(): String {
        return sharedPreferences.getString(PREF_WEBSOCKET_URL, DEFAULT_WEBSOCKET_URL) 
            ?: DEFAULT_WEBSOCKET_URL
    }
    
    fun setWebSocketEnabled(enabled: Boolean) {
        sharedPreferences.edit()
            .putBoolean(PREF_WEBSOCKET_ENABLED, enabled)
            .apply()
        
        if (enabled && !isServiceRunning) {
            startRealtimeService()
        } else if (!enabled && isServiceRunning) {
            stopRealtimeService()
        }
    }
    
    fun setWebSocketUrl(url: String) {
        sharedPreferences.edit()
            .putString(PREF_WEBSOCKET_URL, url)
            .apply()
        
        // Reconnect with new URL if service is running
        if (isServiceRunning) {
            webSocketManager.disconnect()
            connectWebSocket()
        }
    }
}
