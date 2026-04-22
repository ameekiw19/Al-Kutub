package com.example.al_kutub.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.al_kutub.data.repository.DeleteState
import com.example.al_kutub.data.repository.DownloadRepository
import com.example.al_kutub.data.repository.HistoryRepository
import com.example.al_kutub.data.repository.HistoryProgressConflictResolver
import com.example.al_kutub.data.repository.HistoriesState
import com.example.al_kutub.data.repository.OfflineSyncRepository
import com.example.al_kutub.data.repository.ReadingProgressRepository
import com.example.al_kutub.model.HistoryItemData
import com.example.al_kutub.model.Kitab
import com.example.al_kutub.model.HistoryStatsResponse
import com.example.al_kutub.model.SyncSummary
import com.example.al_kutub.utils.SessionManager
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableSharedFlow
import kotlinx.coroutines.flow.SharedFlow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.asSharedFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class HistoryViewModel @Inject constructor(
    private val repository: HistoryRepository,
    private val downloadRepository: DownloadRepository,
    private val readingProgressRepository: ReadingProgressRepository,
    private val offlineSyncRepository: OfflineSyncRepository,
    private val sessionManager: SessionManager
) : ViewModel() {

    private val _toastMessage = MutableStateFlow<String?>(null)
    val toastMessage: StateFlow<String?> = _toastMessage.asStateFlow()

    private val _continueReadingEvent = MutableSharedFlow<ContinueReadingNavigationEvent>(extraBufferCapacity = 1)
    val continueReadingEvent: SharedFlow<ContinueReadingNavigationEvent> = _continueReadingEvent.asSharedFlow()

    // Histories state from repository
    val historiesState: StateFlow<HistoriesState> = repository.historiesState
    val deleteState: StateFlow<DeleteState> = repository.deleteState
    val syncSummary: StateFlow<SyncSummary> = offlineSyncRepository.syncSummary

    init {
        viewModelScope.launch { offlineSyncRepository.refreshSummary() }
        loadHistories()
    }

    /**
     * Load all histories
     */
    fun loadHistories() {
        viewModelScope.launch {
            repository.loadHistories()
            offlineSyncRepository.refreshSummary()
        }
    }

    /**
     * Add or update history when user opens a kitab
     */
    fun addOrUpdateHistory(kitabId: Int) {
        viewModelScope.launch {
            repository.addOrUpdateHistory(kitabId)
                .onSuccess { response ->
                    android.util.Log.d("HistoryViewModel", "✅ History added/updated: ${response.message}")
                    showToast(response.message)
                }
                .onFailure { error ->
                    android.util.Log.e("HistoryViewModel", "❌ Failed to add/update history", error)
                    showToast(error.message ?: "Gagal menyimpan riwayat")
                }
            offlineSyncRepository.refreshSummary()
        }
    }

    /**
     * Delete single history item
     */
    fun deleteHistory(historyId: Int) {
        viewModelScope.launch {
            android.util.Log.d("HistoryViewModel", "🗑️ Deleting history with ID: $historyId")
            repository.deleteHistory(historyId)
                .onSuccess { response ->
                    android.util.Log.d("HistoryViewModel", "✅ History deleted: ${response.message}")
                    showToast(response.message)
                    // Reload histories to update UI
                    loadHistories()
                }
                .onFailure { error ->
                    android.util.Log.e("HistoryViewModel", "❌ Failed to delete history", error)
                    showToast(error.message ?: "Gagal menghapus riwayat")
                }
            offlineSyncRepository.refreshSummary()
        }
    }

    /**
     * Clear all history
     */
    fun clearAllHistory() {
        viewModelScope.launch {
            repository.clearAllHistory()
                .onSuccess { response ->
                    android.util.Log.d("HistoryViewModel", "✅ All history cleared: ${response.message}")
                    val count = response.data?.deleted_count ?: 0
                    android.util.Log.d("HistoryViewModel", "Deleted count: $count")
                    showToast("Semua riwayat telah dihapus ($count item)")
                    // Reload histories to update UI
                    loadHistories()
                }
                .onFailure { error ->
                    android.util.Log.e("HistoryViewModel", "❌ Failed to clear all history", error)
                    showToast(error.message ?: "Gagal menghapus semua riwayat")
                }
            offlineSyncRepository.refreshSummary()
        }
    }

    /**
     * Get history statistics
     */
    fun getHistoryStatistics() {
        viewModelScope.launch {
            repository.getHistoryStatistics()
                .onSuccess { response ->
                    android.util.Log.d("HistoryViewModel", "✅ Statistics loaded: ${response.message}")
                    android.util.Log.d("HistoryViewModel", "Total kitab: ${response.data?.total_kitab ?: 0}")
                    android.util.Log.d("HistoryViewModel", "Today count: ${response.data?.today_count ?: 0}")
                    android.util.Log.d("HistoryViewModel", "This week: ${response.data?.this_week_count ?: 0}")
                    android.util.Log.d("HistoryViewModel", "This month: ${response.data?.this_month_count ?: 0}")
                }
                .onFailure { error ->
                    android.util.Log.e("HistoryViewModel", "❌ Failed to load statistics", error)
                    showToast(error.message ?: "Gagal memuat statistik")
                }
        }
    }

    /**
     * Reset delete state
     */
    fun resetDeleteState() {
        repository.resetDeleteState()
    }

    fun continueReading(item: HistoryItemData) {
        viewModelScope.launch {
            val kitabId = item.kitab_id
            val historyPage = item.current_page.coerceAtLeast(1)
            val userId = sessionManager.getUserId()
            val localPage = if (userId > 0) {
                readingProgressRepository
                    .getProgress(userId, kitabId)
                    ?.lastPageRead
                    ?.coerceAtLeast(1)
            } else {
                null
            }
            val targetPage = HistoryProgressConflictResolver.resolvePage(
                localPage = localPage,
                remotePage = historyPage
            )

            try {
                val localKitab = downloadRepository.getDownloadedKitab(kitabId)
                if (localKitab != null) {
                    val localFile = java.io.File(localKitab.filePath)
                    if (localFile.exists()) {
                        _continueReadingEvent.tryEmit(
                            ContinueReadingNavigationEvent.OpenPdf(
                                kitabId = kitabId,
                                filePath = localFile.absolutePath,
                                initialPage = targetPage
                            )
                        )
                        return@launch
                    }
                }

                val kitabData = item.kitab
                val token = sessionManager.getToken()

                if (kitabData == null || token.isNullOrBlank()) {
                    showToast("PDF belum tersedia. Membuka detail kitab.")
                    _continueReadingEvent.tryEmit(ContinueReadingNavigationEvent.OpenKitabDetail(kitabId))
                    return@launch
                }

                showToast("Mengunduh kitab untuk melanjutkan baca...")
                val kitab = Kitab(
                    idKitab = kitabId,
                    judul = kitabData.judul,
                    penulis = kitabData.penulis,
                    kategori = kitabData.kategori,
                    deskripsi = "",
                    bahasa = kitabData.bahasa,
                    cover = kitabData.cover,
                    views = kitabData.views,
                    downloads = kitabData.downloads
                )

                downloadRepository.downloadKitab(kitab, token)
                    .onSuccess { file ->
                        _continueReadingEvent.tryEmit(
                            ContinueReadingNavigationEvent.OpenPdf(
                                kitabId = kitabId,
                                filePath = file.absolutePath,
                                initialPage = targetPage
                            )
                        )
                    }
                    .onFailure { error ->
                        showToast(
                            error.message?.let { "Gagal membuka PDF: $it" }
                                ?: "Gagal membuka PDF. Membuka detail kitab."
                        )
                        _continueReadingEvent.tryEmit(ContinueReadingNavigationEvent.OpenKitabDetail(kitabId))
                    }
            } catch (_: Exception) {
                showToast("Gagal membuka PDF. Membuka detail kitab.")
                _continueReadingEvent.tryEmit(ContinueReadingNavigationEvent.OpenKitabDetail(kitabId))
            }
        }
    }

    /**
     * Show toast message
     */
    private fun showToast(message: String) {
        _toastMessage.value = message
    }

    /**
     * Clear toast message
     */
    fun clearToastMessage() {
        _toastMessage.value = null
    }
}

sealed class ContinueReadingNavigationEvent {
    data class OpenPdf(
        val kitabId: Int,
        val filePath: String,
        val initialPage: Int
    ) : ContinueReadingNavigationEvent()

    data class OpenKitabDetail(val kitabId: Int) : ContinueReadingNavigationEvent()
}
