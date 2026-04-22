package com.example.al_kutub.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.al_kutub.data.repository.NotificationSettingsRepository
import com.example.al_kutub.model.NotificationPreferences
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.Job
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class NotificationSettingsViewModel @Inject constructor(
    private val repository: NotificationSettingsRepository
) : ViewModel() {

    private val _uiState = MutableStateFlow(NotificationSettingsUiState())
    val uiState: StateFlow<NotificationSettingsUiState> = _uiState.asStateFlow()

    private val _preferences = MutableStateFlow(NotificationPreferences())
    val preferences: StateFlow<NotificationPreferences> = _preferences.asStateFlow()

    private var syncJob: Job? = null

    init {
        loadPreferences()
    }

    fun loadPreferences() {
        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(
                isLoading = true,
                syncError = null
            )

            val cached = repository.getCachedPreferences()
            val result = repository.getFromServer()

            result.fold(
                onSuccess = { serverSettings ->
                    _preferences.value = serverSettings
                    repository.saveToCache(serverSettings)
                    _uiState.value = _uiState.value.copy(
                        isLoading = false,
                        isSaving = false,
                        isOfflineMode = false,
                        syncError = null,
                        lastSyncAt = System.currentTimeMillis()
                    )
                },
                onFailure = { error ->
                    _preferences.value = cached
                    _uiState.value = _uiState.value.copy(
                        isLoading = false,
                        isSaving = false,
                        isOfflineMode = true,
                        syncError = "Mode offline: ${error.message}",
                        lastSyncAt = null
                    )
                }
            )
        }
    }

    fun updatePreferences(update: (NotificationPreferences) -> NotificationPreferences) {
        val newPrefs = update(_preferences.value)
        _preferences.value = newPrefs

        // Optimistic local update for responsive UI + offline fallback.
        repository.saveToCache(newPrefs)
        _uiState.value = _uiState.value.copy(syncError = null)
        scheduleSync(newPrefs)
    }

    private fun scheduleSync(preferences: NotificationPreferences) {
        syncJob?.cancel()
        syncJob = viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isSaving = true)
            delay(400)
            syncNow(preferences)
        }
    }

    fun retrySync() {
        scheduleSync(_preferences.value)
    }

    private suspend fun syncNow(preferences: NotificationPreferences) {
        val result = repository.updateOnServer(preferences)
        result.fold(
            onSuccess = { serverSettings ->
                _preferences.value = serverSettings
                repository.saveToCache(serverSettings)
                _uiState.value = _uiState.value.copy(
                    isSaving = false,
                    isOfflineMode = false,
                    syncError = null,
                    lastSyncAt = System.currentTimeMillis()
                )
            },
            onFailure = { error ->
                _uiState.value = _uiState.value.copy(
                    isSaving = false,
                    isOfflineMode = true,
                    syncError = "Sinkronisasi gagal: ${error.message}"
                )
            }
        )
    }

    fun resetToDefaults() {
        updatePreferences { NotificationPreferences() }
    }

    fun clearSyncError() {
        _uiState.value = _uiState.value.copy(syncError = null)
    }

    override fun onCleared() {
        syncJob?.cancel()
        super.onCleared()
    }
}

data class NotificationSettingsUiState(
    val isLoading: Boolean = false,
    val isSaving: Boolean = false,
    val isOfflineMode: Boolean = false,
    val lastSyncAt: Long? = null,
    val syncError: String? = null
)
