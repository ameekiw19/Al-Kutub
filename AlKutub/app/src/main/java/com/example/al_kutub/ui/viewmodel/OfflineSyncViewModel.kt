package com.example.al_kutub.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.al_kutub.data.repository.DownloadManagerRepository
import com.example.al_kutub.data.repository.OfflineSyncRepository
import com.example.al_kutub.data.repository.ReadingProgressRepository
import com.example.al_kutub.model.DownloadTaskUiState
import com.example.al_kutub.model.SyncOperationUiState
import com.example.al_kutub.model.SyncSummary
import com.example.al_kutub.utils.SessionManager
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.stateIn
import kotlinx.coroutines.launch
import javax.inject.Inject

data class OfflineSyncUiState(
    val pendingOperations: Int = 0,
    val isSyncing: Boolean = false,
    val isClearing: Boolean = false,
    val requiresForceConfirm: Boolean = false,
    val message: String? = null
)

@HiltViewModel
class OfflineSyncViewModel @Inject constructor(
    private val offlineSyncRepository: OfflineSyncRepository,
    private val downloadManagerRepository: DownloadManagerRepository,
    private val readingProgressRepository: ReadingProgressRepository,
    private val sessionManager: SessionManager
) : ViewModel() {

    val syncSummary: StateFlow<SyncSummary> = offlineSyncRepository.syncSummary

    val downloadTasks: StateFlow<List<DownloadTaskUiState>> = downloadManagerRepository.observeTasks()
        .stateIn(viewModelScope, kotlinx.coroutines.flow.SharingStarted.WhileSubscribed(5000), emptyList())

    val syncOperations: StateFlow<List<SyncOperationUiState>> = offlineSyncRepository.observeOperations(limit = 30)
        .stateIn(viewModelScope, kotlinx.coroutines.flow.SharingStarted.WhileSubscribed(5000), emptyList())

    private val _uiState = MutableStateFlow(OfflineSyncUiState())
    val uiState: StateFlow<OfflineSyncUiState> = _uiState.asStateFlow()

    init {
        refresh()
    }

    fun refresh() {
        viewModelScope.launch {
            offlineSyncRepository.refreshSummary()
            _uiState.value = _uiState.value.copy(
                pendingOperations = offlineSyncRepository.getPendingOperationCount(),
                isSyncing = syncSummary.value.isSyncRunning
            )
        }
    }

    fun syncNow() {
        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isSyncing = true, message = null)
            runCatching {
                val syncResult = offlineSyncRepository.processQueue()
                syncResult.getOrThrow()
                downloadManagerRepository.processPendingTasks()
                syncResult
            }.onSuccess {
                val authRequired = syncSummary.value.authRequired
                _uiState.value = _uiState.value.copy(
                    isSyncing = false,
                    message = if (authRequired) {
                        "Sesi berakhir. Silakan login ulang untuk melanjutkan sinkronisasi."
                    } else {
                        "Sinkronisasi selesai."
                    }
                )
            }.onFailure {
                _uiState.value = _uiState.value.copy(
                    isSyncing = false,
                    message = if (syncSummary.value.authRequired) {
                        "Sesi berakhir. Silakan login ulang untuk melanjutkan sinkronisasi."
                    } else {
                        "Sinkronisasi gagal: ${it.message}"
                    }
                )
            }
            refresh()
        }
    }

    fun pauseTask(taskId: Long) {
        viewModelScope.launch { downloadManagerRepository.pauseTask(taskId) }
    }

    fun resumeTask(taskId: Long) {
        viewModelScope.launch { downloadManagerRepository.resumeTask(taskId) }
    }

    fun retryTask(taskId: Long) {
        viewModelScope.launch { downloadManagerRepository.retryTask(taskId) }
    }

    fun cancelTask(taskId: Long) {
        viewModelScope.launch { downloadManagerRepository.cancelTask(taskId) }
    }

    fun clearCache(force: Boolean = false) {
        viewModelScope.launch {
            val pending = offlineSyncRepository.getPendingOperationCount()
            if (pending > 0 && !force) {
                _uiState.value = _uiState.value.copy(
                    requiresForceConfirm = true,
                    message = "Ada operasi pending. Konfirmasi force clear untuk melanjutkan."
                )
                return@launch
            }

            _uiState.value = _uiState.value.copy(isClearing = true, requiresForceConfirm = false, message = null)
            runCatching {
                val userId = sessionManager.getUserId()
                downloadManagerRepository.clearAllLocalDownloads()
                offlineSyncRepository.clearLocalCaches()
                readingProgressRepository.clearUserProgress(userId)
                if (force) {
                    offlineSyncRepository.clearOperationQueue()
                }
            }.onSuccess {
                _uiState.value = _uiState.value.copy(
                    isClearing = false,
                    message = "Cache lokal berhasil dibersihkan."
                )
            }.onFailure {
                _uiState.value = _uiState.value.copy(
                    isClearing = false,
                    message = "Gagal hapus cache: ${it.message}"
                )
            }
            refresh()
        }
    }

    fun consumeMessage() {
        _uiState.value = _uiState.value.copy(message = null)
    }

    fun dismissForceConfirm() {
        _uiState.value = _uiState.value.copy(requiresForceConfirm = false)
    }
}
