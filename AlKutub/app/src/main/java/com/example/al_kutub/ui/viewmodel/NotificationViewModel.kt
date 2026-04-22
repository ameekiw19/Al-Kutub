package com.example.al_kutub.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.al_kutub.data.repository.NotificationRepository
import com.example.al_kutub.model.AppNotification
import com.example.al_kutub.model.Kitab
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.Job
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.isActive
import kotlinx.coroutines.launch
import java.time.Instant
import javax.inject.Inject

@HiltViewModel
class NotificationViewModel @Inject constructor(
    private val notificationRepository: NotificationRepository
) : ViewModel() {
    
    private val _uiState = MutableStateFlow(NotificationUiState())
    val uiState: StateFlow<NotificationUiState> = _uiState.asStateFlow()
    
    private val _notifications = MutableStateFlow<List<AppNotification>>(emptyList())
    val notifications: StateFlow<List<AppNotification>> = _notifications.asStateFlow()
    
    private val _newKitabs = MutableStateFlow<List<Kitab>>(emptyList())
    val newKitabs: StateFlow<List<Kitab>> = _newKitabs.asStateFlow()
    
    private val _unreadCount = MutableStateFlow(0)
    val unreadCount: StateFlow<Int> = _unreadCount.asStateFlow()
    
    private var syncJob: Job? = null
    private var isRealTimeEnabled = true
    
    init {
        loadNotifications()
        startRealTimeSync()
    }
    
    fun loadNotifications() {
        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isLoading = true, error = null)
            
            try {
                val result = notificationRepository.getNotifications()
                result.fold(
                    onSuccess = { notifications ->
                        _notifications.value = notifications
                        notificationRepository.getUnreadCount().onSuccess { count ->
                            _unreadCount.value = count
                        }
                        _uiState.value = _uiState.value.copy(isLoading = false)
                    },
                    onFailure = { error ->
                        _uiState.value = _uiState.value.copy(
                            isLoading = false,
                            error = "Gagal memuat notifikasi: ${error.message}"
                        )
                    }
                )
            } catch (e: Exception) {
                _uiState.value = _uiState.value.copy(
                    isLoading = false,
                    error = "Terjadi kesalahan: ${e.message}"
                )
            }
        }
    }
    
    fun refreshNotifications() {
        viewModelScope.launch {
            try {
                _uiState.value = _uiState.value.copy(isRefreshing = true, error = null)
                
                val result = notificationRepository.syncAll()
                result.fold(
                    onSuccess = {
                        loadNotifications()
                        _uiState.value = _uiState.value.copy(isRefreshing = false)
                    },
                    onFailure = { error ->
                        _uiState.value = _uiState.value.copy(
                            isRefreshing = false,
                            error = "Gagal refresh: ${error.message}"
                        )
                    }
                )
            } catch (e: Exception) {
                _uiState.value = _uiState.value.copy(
                    isRefreshing = false,
                    error = "Terjadi kesalahan: ${e.message}"
                )
            }
        }
    }
    
    private fun startRealTimeSync() {
        if (!isRealTimeEnabled) return
        
        syncJob = viewModelScope.launch {
            while (isActive) {
                try {
                    // Sync every 30 seconds
                    delay(30000)
                    
                    val result = notificationRepository.syncAll()
                    result.fold(
                        onSuccess = {
                            // Get updated unread count
                            val unreadResult = notificationRepository.getUnreadCount()
                            unreadResult.fold(
                                onSuccess = { count ->
                                    _unreadCount.value = count
                                },
                                onFailure = { /* Handle error if needed */ }
                            )

                            // Reload notifications snapshot after sync.
                            loadNotifications()
                        },
                        onFailure = { error ->
                            // Log error but don't update UI state for background sync
                            println("Background sync error: ${error.message}")
                        }
                    )
                } catch (e: Exception) {
                    println("Background sync exception: ${e.message}")
                }
            }
        }
    }
    
    fun stopRealTimeSync() {
        isRealTimeEnabled = false
        syncJob?.cancel()
    }
    
    fun enableRealTimeSync() {
        isRealTimeEnabled = true
        if (syncJob?.isActive != true) {
            startRealTimeSync()
        }
    }
    
    fun markAsRead(notificationId: Int) {
        viewModelScope.launch {
            val previousNotifications = _notifications.value
            val previousUnread = _unreadCount.value
            val nowIso = Instant.now().toString()

            _notifications.value = previousNotifications.map { notification ->
                if (notification.id == notificationId && notification.readAt.isNullOrBlank()) {
                    notification.copy(readAt = nowIso)
                } else {
                    notification
                }
            }
            _unreadCount.value = (_unreadCount.value - 1).coerceAtLeast(0)

            try {
                val result = notificationRepository.markAsRead(notificationId)
                result.fold(
                    onSuccess = {
                        notificationRepository.getUnreadCount().onSuccess { count ->
                            _unreadCount.value = count
                        }
                    },
                    onFailure = { error ->
                        _notifications.value = previousNotifications
                        _unreadCount.value = previousUnread
                        _uiState.value = _uiState.value.copy(
                            error = "Gagal menandai sebagai dibaca: ${error.message}"
                        )
                    }
                )
            } catch (e: Exception) {
                _notifications.value = previousNotifications
                _unreadCount.value = previousUnread
                _uiState.value = _uiState.value.copy(
                    error = "Terjadi kesalahan: ${e.message}"
                )
            }
        }
    }

    fun markAllAsRead() {
        viewModelScope.launch {
            if (_unreadCount.value <= 0) return@launch

            val previousNotifications = _notifications.value
            val previousUnread = _unreadCount.value
            val nowIso = Instant.now().toString()

            _notifications.value = previousNotifications.map { notification ->
                if (notification.readAt.isNullOrBlank()) {
                    notification.copy(readAt = nowIso)
                } else {
                    notification
                }
            }
            _unreadCount.value = 0

            try {
                val result = notificationRepository.markAllAsRead()
                result.fold(
                    onSuccess = {
                        notificationRepository.getUnreadCount().onSuccess { count ->
                            _unreadCount.value = count
                        }
                    },
                    onFailure = { error ->
                        _notifications.value = previousNotifications
                        _unreadCount.value = previousUnread
                        _uiState.value = _uiState.value.copy(
                            error = "Gagal menandai semua notifikasi: ${error.message}"
                        )
                    }
                )
            } catch (e: Exception) {
                _notifications.value = previousNotifications
                _unreadCount.value = previousUnread
                _uiState.value = _uiState.value.copy(
                    error = "Terjadi kesalahan: ${e.message}"
                )
            }
        }
    }
    
    fun clearNewKitabs() {
        notificationRepository.clearNewKitabs()
        _newKitabs.value = emptyList()
    }
    
    fun clearError() {
        _uiState.value = _uiState.value.copy(error = null)
    }
    
    override fun onCleared() {
        super.onCleared()
        syncJob?.cancel()
    }
}

data class NotificationUiState(
    val isLoading: Boolean = false,
    val isRefreshing: Boolean = false,
    val error: String? = null,
    val lastSyncTime: Long = System.currentTimeMillis()
)
