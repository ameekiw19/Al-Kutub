package com.example.al_kutub.websocket

import android.util.Log
import kotlinx.coroutines.*
import kotlinx.coroutines.flow.*
import okhttp3.*
import org.json.JSONObject
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class WebSocketManager @Inject constructor() {
    
    private val TAG = "WebSocketManager"
    private var webSocket: WebSocket? = null
    private val client = OkHttpClient()
    private var reconnectJob: Job? = null
    private var isConnecting = false
    
    private val _connectionState = MutableStateFlow(ConnectionState.DISCONNECTED)
    val connectionState: StateFlow<ConnectionState> = _connectionState.asStateFlow()
    
    private val _notifications = MutableSharedFlow<WebSocketNotification>()
    val notifications: SharedFlow<WebSocketNotification> = _notifications.asSharedFlow()
    
    private val _kitabUpdates = MutableSharedFlow<KitabUpdate>()
    val kitabUpdates: SharedFlow<KitabUpdate> = _kitabUpdates.asSharedFlow()
    
    enum class ConnectionState {
        CONNECTING, CONNECTED, DISCONNECTED, ERROR
    }
    
    data class WebSocketNotification(
        val type: String,
        val data: Map<String, Any>,
        val timestamp: Long = System.currentTimeMillis()
    )
    
    data class KitabUpdate(
        val kitabId: Int,
        val action: String, // "created", "updated", "deleted"
        val data: Map<String, Any>? = null
    )
    
    fun connect(url: String) {
        if (isConnecting || _connectionState.value == ConnectionState.CONNECTED) {
            Log.d(TAG, "WebSocket already connecting or connected")
            return
        }
        
        Log.d(TAG, "Connecting to WebSocket: $url")
        isConnecting = true
        _connectionState.value = ConnectionState.CONNECTING
        
        val request = Request.Builder()
            .url(url)
            .addHeader("Origin", "app://al-kutub")
            .build()
        
        webSocket = client.newWebSocket(request, createWebSocketListener())
    }
    
    fun disconnect() {
        Log.d(TAG, "Disconnecting WebSocket")
        reconnectJob?.cancel()
        webSocket?.close(1000, "User disconnected")
        webSocket = null
        isConnecting = false
        _connectionState.value = ConnectionState.DISCONNECTED
    }
    
    private fun createWebSocketListener(): WebSocketListener {
        return object : WebSocketListener() {
            override fun onOpen(webSocket: WebSocket, response: Response) {
                Log.d(TAG, "WebSocket connected")
                isConnecting = false
                _connectionState.value = ConnectionState.CONNECTED
                
                // Send authentication if needed
                sendAuthMessage()
            }
            
            override fun onMessage(webSocket: WebSocket, text: String) {
                Log.d(TAG, "WebSocket message received: $text")
                handleMessage(text)
            }
            
            override fun onClosing(webSocket: WebSocket, code: Int, reason: String) {
                Log.d(TAG, "WebSocket closing: $code - $reason")
                _connectionState.value = ConnectionState.DISCONNECTED
            }
            
            override fun onClosed(webSocket: WebSocket, code: Int, reason: String) {
                Log.d(TAG, "WebSocket closed: $code - $reason")
                isConnecting = false
                _connectionState.value = ConnectionState.DISCONNECTED
                
                // Auto-reconnect if not manually disconnected
                if (code != 1000) {
                    scheduleReconnect()
                }
            }
            
            override fun onFailure(webSocket: WebSocket, t: Throwable, response: Response?) {
                Log.e(TAG, "WebSocket error", t)
                isConnecting = false
                _connectionState.value = ConnectionState.ERROR
                
                // Schedule reconnect
                scheduleReconnect()
            }
        }
    }
    
    private fun sendAuthMessage() {
        // Send authentication token if available
        // This depends on your auth system
        try {
            val authMessage = JSONObject().apply {
                put("type", "auth")
                put("token", "YOUR_AUTH_TOKEN_HERE") // Replace with actual token
            }.toString()
            
            webSocket?.send(authMessage)
            Log.d(TAG, "Auth message sent")
        } catch (e: Exception) {
            Log.e(TAG, "Error sending auth message", e)
        }
    }
    
    private fun handleMessage(message: String) {
        try {
            val json = JSONObject(message)
            val type = json.getString("type")
            val data = json.getJSONObject("data").toMap()
            
            when (type) {
                "notification" -> {
                    val notification = WebSocketNotification(type, data)
                    _notifications.tryEmit(notification)
                }
                "kitab_update" -> {
                    val kitabId = data["kitab_id"] as? Int ?: data["kitab_id"]?.toString()?.toIntOrNull()
                    val action = data["action"] as? String ?: "unknown"
                    
                    kitabId?.let { id ->
                        val update = KitabUpdate(id, action, data)
                        _kitabUpdates.tryEmit(update)
                    }
                }
                else -> {
                    Log.d(TAG, "Unknown message type: $type")
                }
            }
        } catch (e: Exception) {
            Log.e(TAG, "Error parsing WebSocket message", e)
        }
    }
    
    private fun scheduleReconnect() {
        reconnectJob?.cancel()
        reconnectJob = CoroutineScope(Dispatchers.IO).launch {
            delay(5000) // Wait 5 seconds before reconnecting
            
            if (_connectionState.value != ConnectionState.CONNECTED) {
                Log.d(TAG, "Attempting to reconnect WebSocket")
                // You'll need to store the URL for reconnection
                // connect(lastUrl)
            }
        }
    }
    
    fun sendMessage(type: String, data: Map<String, Any>) {
        try {
            val message = JSONObject().apply {
                put("type", type)
                put("data", JSONObject(data))
            }.toString()
            
            val sent = webSocket?.send(message) ?: false
            Log.d(TAG, "Message sent: $sent - $message")
        } catch (e: Exception) {
            Log.e(TAG, "Error sending WebSocket message", e)
        }
    }
    
    // Extension function to convert JSONObject to Map
    private fun JSONObject.toMap(): Map<String, Any> {
        return keys().asSequence().associateWith { key ->
            when (val value = get(key)) {
                is JSONObject -> value.toMap()
                else -> value
            }
        }
    }
}
