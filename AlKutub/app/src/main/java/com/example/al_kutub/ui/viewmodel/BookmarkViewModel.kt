package com.example.al_kutub.ui.viewmodel

import android.util.Log
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.al_kutub.data.repository.KatalogRepository
import com.example.al_kutub.data.repository.OfflineSyncRepository
import com.example.al_kutub.model.*
import com.example.al_kutub.repository.BookmarkRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

private const val TAG = "BookmarkViewModel"

@HiltViewModel
class BookmarkViewModel @Inject constructor(
    private val bookmarkRepository: BookmarkRepository,
    private val katalogRepository: KatalogRepository,
    private val offlineSyncRepository: OfflineSyncRepository
) : ViewModel() {

    // State untuk list bookmarks
    private val _bookmarksState = MutableStateFlow<BookmarkUiState>(BookmarkUiState.Loading)
    val bookmarksState: StateFlow<BookmarkUiState> = _bookmarksState.asStateFlow()

    // State untuk bookmark stats
    private val _statsState = MutableStateFlow<StatsUiState>(StatsUiState.Loading)
    val statsState: StateFlow<StatsUiState> = _statsState.asStateFlow()

    // State untuk toast/snackbar messages
    private val _messageState = MutableStateFlow<String?>(null)
    val messageState: StateFlow<String?> = _messageState.asStateFlow()

    private val _recommendedKitabState =
        MutableStateFlow<RecommendedKitabUiState>(RecommendedKitabUiState.Loading)
    val recommendedKitabState: StateFlow<RecommendedKitabUiState> = _recommendedKitabState.asStateFlow()

    val syncSummary: StateFlow<SyncSummary> = offlineSyncRepository.syncSummary

    init {
        viewModelScope.launch { offlineSyncRepository.refreshSummary() }
        loadBookmarks()
        loadRecommendedKitab()
    }

    /**
     * Load semua bookmarks
     */
    fun loadBookmarks() {
        viewModelScope.launch {
            Log.d(TAG, "🔄 Loading bookmarks...")
            _bookmarksState.value = BookmarkUiState.Loading

            bookmarkRepository.getAllBookmarks().fold(
                onSuccess = { response ->
                    if (response.status == "success") {
                        val bookmarks = response.data ?: emptyList()
                        Log.d(TAG, "✅ Loaded ${bookmarks.size} bookmarks")
                        _bookmarksState.value = BookmarkUiState.Success(bookmarks)
                    } else {
                        Log.e(TAG, "❌ API returned error: ${response.message}")
                        _bookmarksState.value = BookmarkUiState.Error(response.message)
                    }
                },
                onFailure = { error ->
                    Log.e(TAG, "❌ Failed to load bookmarks", error)
                    _bookmarksState.value = BookmarkUiState.Error(error.message ?: "Unknown error")
                }
            )
            offlineSyncRepository.refreshSummary()
        }
    }

    /**
     * Toggle bookmark (add/remove)
     */
    fun toggleBookmark(idKitab: Int, onSuccess: ((Boolean) -> Unit)? = null) {
        viewModelScope.launch {
            Log.d(TAG, "🔄 Toggling bookmark for kitab: $idKitab")

            bookmarkRepository.toggleBookmark(idKitab).fold(
                onSuccess = { response ->
                    if (response.status == "success") {
                        val isBookmarked = response.isBookmarked
                        Log.d(TAG, "✅ Bookmark toggled: $isBookmarked")
                        _messageState.value = response.message
                        onSuccess?.invoke(isBookmarked)
                        loadBookmarks() // Refresh list
                    } else {
                        _messageState.value = response.message
                    }
                },
                onFailure = { error ->
                    Log.e(TAG, "❌ Failed to toggle bookmark", error)
                    _messageState.value = "Gagal mengubah bookmark"
                }
            )
            offlineSyncRepository.refreshSummary()
        }
    }

    /**
     * Delete single bookmark
     */
    fun deleteBookmark(idKitab: Int) {
        viewModelScope.launch {
            Log.d(TAG, "🗑️ Deleting bookmark: $idKitab")

            bookmarkRepository.deleteBookmark(idKitab).fold(
                onSuccess = { response ->
                    if (response.status == "success") {
                        Log.d(TAG, "✅ Bookmark deleted")
                        _messageState.value = response.message
                        loadBookmarks() // Refresh list
                    } else {
                        _messageState.value = response.message
                    }
                },
                onFailure = { error ->
                    Log.e(TAG, "❌ Failed to delete bookmark", error)
                    _messageState.value = "Gagal menghapus bookmark"
                }
            )
            offlineSyncRepository.refreshSummary()
        }
    }

    /**
     * Clear all bookmarks
     */
    fun clearAllBookmarks() {
        viewModelScope.launch {
            Log.d(TAG, "🗑️ Clearing all bookmarks...")

            bookmarkRepository.clearAllBookmarks().fold(
                onSuccess = { response ->
                    if (response.status == "success") {
                        Log.d(TAG, "✅ All bookmarks cleared")
                        _messageState.value = response.message
                        loadBookmarks() // Refresh list
                    } else {
                        _messageState.value = response.message
                    }
                },
                onFailure = { error ->
                    Log.e(TAG, "❌ Failed to clear bookmarks", error)
                    _messageState.value = "Gagal menghapus semua bookmark"
                }
            )
            offlineSyncRepository.refreshSummary()
        }
    }

    /**
     * Load bookmark statistics
     */
    fun loadStats() {
        viewModelScope.launch {
            Log.d(TAG, "🔄 Loading bookmark stats...")
            _statsState.value = StatsUiState.Loading

            bookmarkRepository.getBookmarkStats().fold(
                onSuccess = { response ->
                    if (response.status == "success") {
                        Log.d(TAG, "✅ Stats loaded successfully")
                        _statsState.value = StatsUiState.Success(response.data)
                    } else {
                        _statsState.value = StatsUiState.Error("Failed to load stats")
                    }
                },
                onFailure = { error ->
                    Log.e(TAG, "❌ Failed to load stats", error)
                    _statsState.value = StatsUiState.Error(error.message ?: "Unknown error")
                }
            )
        }
    }

    /**
     * Clear message after showing
     */
    fun clearMessage() {
        _messageState.value = null
    }

    fun loadRecommendedKitab() {
        viewModelScope.launch {
            _recommendedKitabState.value = RecommendedKitabUiState.Loading
            katalogRepository.getKatalog().fold(
                onSuccess = { response ->
                    if (response.success) {
                        val recommended = response.data
                            ?.kitab
                            .orEmpty()
                            .sortedByDescending { it.views }
                            .take(3)
                        _recommendedKitabState.value = RecommendedKitabUiState.Success(recommended)
                    } else {
                        _recommendedKitabState.value = RecommendedKitabUiState.Error(
                            response.message?.ifBlank { "Gagal memuat rekomendasi kitab" }
                                ?: "Gagal memuat rekomendasi kitab"
                        )
                    }
                },
                onFailure = { error ->
                    _recommendedKitabState.value = RecommendedKitabUiState.Error(
                        error.message ?: "Gagal memuat rekomendasi kitab"
                    )
                }
            )
        }
    }
}

// UI States
sealed class BookmarkUiState {
    object Loading : BookmarkUiState()
    data class Success(val bookmarks: List<BookmarkItem>) : BookmarkUiState()
    data class Error(val message: String) : BookmarkUiState()
}

sealed class StatsUiState {
    object Loading : StatsUiState()
    data class Success(val stats: BookmarkStatsData) : StatsUiState()
    data class Error(val message: String) : StatsUiState()
}

sealed class RecommendedKitabUiState {
    object Loading : RecommendedKitabUiState()
    data class Success(val kitab: List<Kitab>) : RecommendedKitabUiState()
    data class Error(val message: String) : RecommendedKitabUiState()
}
