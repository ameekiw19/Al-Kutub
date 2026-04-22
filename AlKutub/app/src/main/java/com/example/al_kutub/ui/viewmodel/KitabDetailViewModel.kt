package com.example.al_kutub.ui.viewmodel

import android.content.Context
import androidx.lifecycle.ViewModel
import androidx.lifecycle.SavedStateHandle // 1. PASTIKAN SavedStateHandle DI-IMPORT
import androidx.lifecycle.viewModelScope
import com.example.al_kutub.data.repository.KitabDetailRepository
import com.example.al_kutub.data.repository.HistoryRepository
import com.example.al_kutub.model.ApiResponse
import com.example.al_kutub.model.Comment
import com.example.al_kutub.model.Kitab
import com.example.al_kutub.utils.SessionManager
import dagger.hilt.android.lifecycle.HiltViewModel
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class KitabDetailViewModel @Inject constructor(
    private val repository: KitabDetailRepository,
    private val historyRepository: HistoryRepository,
    private val downloadRepository: com.example.al_kutub.data.repository.DownloadRepository,
    private val sessionManager: SessionManager,
    @ApplicationContext private val context: Context,

    private val savedStateHandle: SavedStateHandle
) : ViewModel() {

    private val kitabId: Int = savedStateHandle.get<Int>("kitabId") ?: 0
    private val _uiState = MutableStateFlow<KitabDetailUiState>(KitabDetailUiState.Loading)
    val uiState: StateFlow<KitabDetailUiState> = _uiState.asStateFlow()

    private val _isBookmarked = MutableStateFlow(false)
    val isBookmarked: StateFlow<Boolean> = _isBookmarked.asStateFlow()

    private val _isDownloaded = MutableStateFlow(false)
    val isDownloaded: StateFlow<Boolean> = _isDownloaded.asStateFlow()

    private val _toastMessage = MutableStateFlow<String?>(null)
    val toastMessage: StateFlow<String?> = _toastMessage.asStateFlow()

    private val _comments = MutableStateFlow<List<Comment>>(emptyList())
    val comments: StateFlow<List<Comment>> = _comments.asStateFlow()

    private val _isSubmittingComment = MutableStateFlow(false)
    val isSubmittingComment: StateFlow<Boolean> = _isSubmittingComment.asStateFlow()

    private val _lastPageRead = MutableStateFlow(1)
    val lastPageRead: StateFlow<Int> = _lastPageRead.asStateFlow()

    // Rating State
    private val _myRating = MutableStateFlow(0)
    val myRating: StateFlow<Int> = _myRating.asStateFlow()

    private val _averageRating = MutableStateFlow(0.0)
    val averageRating: StateFlow<Double> = _averageRating.asStateFlow()

    private val _ratingsCount = MutableStateFlow(0)
    val ratingsCount: StateFlow<Int> = _ratingsCount.asStateFlow()

    // Reading Tracking
    private var readingStartTime: Long = 0
    private var lastSavedPage: Int = -1

    // Internal PDF Navigation State
    private val _navigateToPdf = MutableStateFlow<String?>(null)
    val navigateToPdf: StateFlow<String?> = _navigateToPdf.asStateFlow()

    // Related Kitabs
    private val _relatedKitabs = MutableStateFlow<List<Kitab>>(emptyList())
    val relatedKitabs: StateFlow<List<Kitab>> = _relatedKitabs.asStateFlow()

    // Current User ID for comment ownership
    val currentUserId: Int
        get() = sessionManager.getUserId().takeIf { it > 0 } ?: SessionManager.getInstance(context).getUserId()

    init {
        // Otomatis muat detail kitab saat ViewModel dibuat, jika kitabId valid
        if (kitabId != 0) {
            loadKitabDetail(kitabId)
        } else {
            // Jika ID tidak valid dari awal, langsung tampilkan error
            _uiState.value = KitabDetailUiState.Error("ID Kitab tidak ditemukan atau tidak valid.")
            android.util.Log.e("KitabDetailViewModel", "❌ Invalid Kitab ID (0) from SavedStateHandle")
        }
    }

    fun loadKitabDetail(idKitab: Int) {
        viewModelScope.launch {
            _uiState.value = KitabDetailUiState.Loading
            
            // Debug logging
            android.util.Log.d("KitabDetailViewModel", "========================================")
            android.util.Log.d("KitabDetailViewModel", "🔄 LOADING KITAB DETAIL")
            android.util.Log.d("KitabDetailViewModel", "📖 Kitab ID: $idKitab")
            android.util.Log.d("KitabDetailViewModel", "========================================")

            repository.getKitabDetail(idKitab)
                .onSuccess { response ->
                    android.util.Log.d("KitabDetailViewModel", "✅ API SUCCESS")
                    android.util.Log.d("KitabDetailViewModel", "Response Success: ${response.success}")
                    android.util.Log.d("KitabDetailViewModel", "Response Message: ${response.message}")
                    android.util.Log.d("KitabDetailViewModel", "Response Data: ${response.data}")
                    android.util.Log.d("KitabDetailViewModel", "========================================")
                    
                    if (response.success) {
                        val kitab = response.data
                        if (kitab != null) {
                            _uiState.value = KitabDetailUiState.Success(kitab)
                            android.util.Log.d("KitabDetailViewModel", "✅ KITAB LOADED SUCCESSFULLY")
                            android.util.Log.d("KitabDetailViewModel", "📖 Kitab Title: ${kitab.judul}")
                            android.util.Log.d("KitabDetailViewModel", "� Kitab ID: ${kitab.idKitab}")
                            android.util.Log.d("KitabDetailViewModel", "📖 Kitab PDF: ${kitab.filePdf}")
                            android.util.Log.d("KitabDetailViewModel", "========================================")
                            
                            // Add to history when kitab is loaded
                            addToHistory(kitab.idKitab)
                            
                            // Check download status
                            checkDownloadStatus(kitab.idKitab)
                            
                            // Check bookmark status
                            checkBookmarkStatus(kitab.idKitab)

                            // Load comments for this kitab
                            loadComments(kitab.idKitab)

                            // Initialize rating stats from kitab data
                            _averageRating.value = kitab.averageRating
                            _ratingsCount.value = kitab.ratingsCount
                            
                            // Load my existing rating
                            loadMyRating(kitab.idKitab)
                            
                            // Load related kitabs
                            loadRelatedKitabs(kitab.idKitab)
                        } else {  
                            android.util.Log.e("KitabDetailViewModel", "❌ API Response Success = false")
                            _uiState.value = KitabDetailUiState.Error(response.message)
                        }
                    }
                }
                .onFailure { error ->
                    android.util.Log.e("KitabDetailViewModel", "❌❌❌ API FAILURE ❌❌❌")
                    android.util.Log.e("KitabDetailViewModel", "Error: ${error.message}")
                    android.util.Log.e("KitabDetailViewModel", "Error Type: ${error::class.java.simpleName}")
                    android.util.Log.e("KitabDetailViewModel", "========================================")
                    
                    _uiState.value = KitabDetailUiState.Error(
                        error.message ?: "Gagal memuat detail kitab"
                    )
                }
        }
    }

    private fun checkBookmarkStatus(idKitab: Int) {
        viewModelScope.launch {
            val token = sessionManager.getToken() ?: SessionManager.getInstance(context).getToken()
            if (token != null) {
                repository.checkBookmark(idKitab, token)
                    .onSuccess { response ->
                         android.util.Log.d("KitabDetailViewModel", "✅ Bookmark status: ${response.isBookmarked}")
                        _isBookmarked.value = response.isBookmarked
                    }
                    .onFailure {
                        android.util.Log.e("KitabDetailViewModel", "Failed to check bookmark status", it)
                    }
            }
        }
    }

    fun toggleBookmark(idKitab: Int) {
        viewModelScope.launch {
            // Try both injected and static session manager
            val token = sessionManager.getToken() ?: SessionManager.getInstance(context).getToken()
            val newValue = !_isBookmarked.value
            _isBookmarked.value = newValue

            android.util.Log.d("KitabDetailViewModel", "TOGGLING BOOKMARK")
            android.util.Log.d("KitabDetailViewModel", "Kitab ID: $idKitab")
            android.util.Log.d("KitabDetailViewModel", "New Value: $newValue")
            android.util.Log.d("KitabDetailViewModel", "Token Available: ${token != null}")
            android.util.Log.d("KitabDetailViewModel", "Token Length: ${token?.length ?: 0}")
            
            // Debug session state
            SessionManager.getInstance(context).debugState()

            if (token != null) {
                repository.toggleBookmark(idKitab, token)
                    .onSuccess { response ->
                        android.util.Log.d("KitabDetailViewModel", "Bookmark API Success")
                        android.util.Log.d("KitabDetailViewModel", "Response: ${response.status} - ${response.message}")
                        
                        showToast(
                            if (response.status == "success") {
                                if (response.action == "added") {
                                    _isBookmarked.value = true
                                    "Ditambahkan ke bookmark"
                                } else {
                                    _isBookmarked.value = false
                                    "Dihapus dari bookmark"
                                }
                            } else {
                                // Default behavior if action not specified
                                if (newValue) "Ditambahkan ke bookmark" else "Dihapus dari bookmark"
                            }
                        )
                    }
                    .onFailure { error ->
                        android.util.Log.e("KitabDetailViewModel", "Bookmark API Failed")
                        android.util.Log.e("KitabDetailViewModel", "Error: ${error.message}")
                        
                        // Revert on failure
                        _isBookmarked.value = !newValue
                        showToast("Gagal mengubah bookmark: ${error.message}")
                    }
            } else {
                android.util.Log.d("KitabDetailViewModel", "Mock bookmark behavior (no token)")
                showToast(
                    if (newValue) "Ditambahkan ke bookmark" else "Dihapus dari bookmark"
                )
            }
        }
    }

    fun clearToastMessage() {
        _toastMessage.value = null
    }

    fun clearPdfNavigation() {
        _navigateToPdf.value = null
    }

    private fun showToast(message: String) {
        _toastMessage.value = message
    }
    
    /**
     * Add kitab to reading history or update progress
     */
    fun saveReadingProgress(page: Int, totalPages: Int? = null, readingTime: Int? = null) {
        val currentKitabId = kitabId
        if (currentKitabId == 0) return
        
        // Avoid redundant saves if same page (unless time is being saved)
        if (page == lastSavedPage && readingTime == null) return

        viewModelScope.launch {
            val token = sessionManager.getToken() ?: return@launch
            
            android.util.Log.d("KitabDetailViewModel", "Saving progress for kitab $currentKitabId: page $page")
            
            // If readingTime is provided, we use it as reading_time_added
            historyRepository.addOrUpdateHistory(
                kitabId = currentKitabId,
                currentPage = page,
                totalPages = totalPages,
                readingTimeAdded = readingTime
            ).onSuccess { response ->
                lastSavedPage = page
                android.util.Log.d("KitabDetailViewModel", "✅ Progress saved successfully")
                response.data?.current_page?.let { page ->
                    _lastPageRead.value = page
                }
            }.onFailure { e ->
                android.util.Log.e("KitabDetailViewModel", "❌ Failed to save progress", e)
            }
        }
    }

    fun startReadingSession() {
        readingStartTime = System.currentTimeMillis()
        android.util.Log.d("KitabDetailViewModel", "📖 Reading session started")
    }

    fun stopReadingSession(currentPage: Int, totalPages: Int) {
        if (readingStartTime == 0L) return
        
        val elapsedMillis = System.currentTimeMillis() - readingStartTime
        val elapsedMinutes = (elapsedMillis / 60000).toInt()
        
        android.util.Log.d("KitabDetailViewModel", "⏱️ Session ended. Elapsed: $elapsedMinutes min")
        
        // Save final progress with accumulated time
        saveReadingProgress(
            page = currentPage,
            totalPages = totalPages,
            readingTime = if (elapsedMinutes > 0) elapsedMinutes else null
        )
        
        readingStartTime = 0
    }

    /**
     * Add kitab to reading history (initial load)
     */
    private fun addToHistory(kitabId: Int) {
        saveReadingProgress(page = 1) // Save initial history starting from page 1
    }

    private fun loadRelatedKitabs(idKitab: Int) {
        viewModelScope.launch {
            repository.getRelatedKitab(idKitab)
                .onSuccess { response ->
                    if (response.success && response.data != null) {
                        _relatedKitabs.value = response.data
                    }
                }
                .onFailure {
                    android.util.Log.e("KitabDetailViewModel", "Failed to load related kitabs", it)
                }
        }
    }

    fun loadComments(idKitab: Int) {
        viewModelScope.launch {
            android.util.Log.d("KitabDetailViewModel", "========================================")
            android.util.Log.d("KitabDetailViewModel", "LOADING COMMENTS")
            android.util.Log.d("KitabDetailViewModel", "Kitab ID: $idKitab")
            android.util.Log.d("KitabDetailViewModel", "Current comments count: ${_comments.value.size}")
            android.util.Log.d("KitabDetailViewModel", "========================================")
            
            repository.getComments(idKitab)
                .onSuccess { response ->
                    android.util.Log.d("KitabDetailViewModel", "COMMENTS API SUCCESS")
                    android.util.Log.d("KitabDetailViewModel", "Response Success: ${response.success}")
                    android.util.Log.d("KitabDetailViewModel", "Response Message: ${response.message}")
                    android.util.Log.d("KitabDetailViewModel", "Comments Data: ${response.data}")
                    android.util.Log.d("KitabDetailViewModel", "Comments Count: ${response.data?.size}")
                    
                    if (response.success) {
                        val comments = response.data ?: emptyList()
                        _comments.value = comments
                        android.util.Log.d("KitabDetailViewModel", "COMMENTS UPDATED IN UI STATE")
                        android.util.Log.d("KitabDetailViewModel", "New comments count: ${_comments.value.size}")
                        
                        // Log each comment for UI debugging
                        comments.forEachIndexed { index, comment ->
                            android.util.Log.d("KitabDetailViewModel", "UI Comment[$index]: ${comment.username} - ${comment.comment}")
                        }
                    } else {
                        android.util.Log.e("KitabDetailViewModel", "Comments API returned success=false")
                        android.util.Log.e("KitabDetailViewModel", "Error message: ${response.message}")
                        _comments.value = emptyList()
                    }
                    android.util.Log.d("KitabDetailViewModel", "========================================")
                }
                .onFailure { error ->
                    android.util.Log.e("KitabDetailViewModel", "COMMENTS API FAILED")
                    android.util.Log.e("KitabDetailViewModel", "Error: ${error.message}")
                    android.util.Log.e("KitabDetailViewModel", "Error Type: ${error::class.java.simpleName}")
                    _comments.value = emptyList()
                    android.util.Log.d("KitabDetailViewModel", "========================================")
                }
        }
    }

    fun submitComment(idKitab: Int, comment: String, onSuccess: () -> Unit = {}) {
        viewModelScope.launch {
            val normalizedComment = comment.trim()
            if (normalizedComment.isBlank()) {
                showToast("Komentar tidak boleh kosong")
                return@launch
            }

            // Try both injected and static session manager
            val token = sessionManager.getToken() ?: SessionManager.getInstance(context).getToken()
            android.util.Log.d("KitabDetailViewModel", "SUBMITTING COMMENT")
            android.util.Log.d("KitabDetailViewModel", "Kitab ID: $idKitab")
            android.util.Log.d("KitabDetailViewModel", "Comment: $normalizedComment")
            android.util.Log.d("KitabDetailViewModel", "Token Available: ${token != null}")
            android.util.Log.d("KitabDetailViewModel", "Token Length: ${token?.length ?: 0}")
            
            // Debug session state
            SessionManager.getInstance(context).debugState()
            
            if (token != null && token.isNotEmpty()) {
                android.util.Log.d("KitabDetailViewModel", "Submitting comment with token...")

                val userId = currentUserId
                android.util.Log.d("KitabDetailViewModel", "User ID: $userId")
                if (userId <= 0) {
                    android.util.Log.w("KitabDetailViewModel", "User ID tidak ditemukan di session, lanjut submit dengan token auth")
                }

                _isSubmittingComment.value = true
                try {
                    repository.submitComment(idKitab, normalizedComment, userId.coerceAtLeast(0), token)
                    .onSuccess { response ->
                        android.util.Log.d("KitabDetailViewModel", "Comment submitted successfully")
                        android.util.Log.d("KitabDetailViewModel", "Response: ${response.success} - ${response.message}")
                        
                        if (response.success) {
                            onSuccess()
                            showToast("Komentar berhasil dikirim")
                            // Reload comments to get the latest
                            loadComments(idKitab)
                        } else {
                            showToast("Gagal mengirim komentar: ${response.message}")
                        }
                    }
                    .onFailure { error ->
                        android.util.Log.e("KitabDetailViewModel", "Failed to submit comment", error)
                        showToast("Gagal mengirim komentar: ${error.message}")
                    }
                } finally {
                    _isSubmittingComment.value = false
                }
            } else {
                android.util.Log.e("KitabDetailViewModel", "No token available for comment submission")
                showToast("Silakan login untuk mengirim komentar")
            }
        }
    }

    private fun checkDownloadStatus(idKitab: Int) {
        viewModelScope.launch {
            downloadRepository.isKitabDownloaded(idKitab).collect { downloaded ->
                _isDownloaded.value = downloaded
            }
        }
    }

    fun downloadAndOpenPdf(idKitab: Int, title: String) {
        viewModelScope.launch {
            // Increment view count when user opens PDF
            incrementViewCount(idKitab)
            
            // Check if file is already downloaded
            val localKitab = downloadRepository.getDownloadedKitab(idKitab)
            if (localKitab != null) {
                // Open local file
                val file = java.io.File(localKitab.filePath)
                if (file.exists()) {
                    android.util.Log.d("KitabDetailViewModel", "📂 Opening local file: ${file.absolutePath}")
                    openPdfFile(file)
                    saveReadingProgress(page = lastPageRead.value) // Update progress with current page
                    return@launch
                } else {
                    // File record exists but file is missing? Invalid state, proceed to download
                    android.util.Log.w("KitabDetailViewModel", "⚠️ Local record exists but file missing, redownloading...")
                }
            }

            // Check token for download
            val token = sessionManager.getToken() ?: SessionManager.getInstance(context).getToken()
            
            if (token == null) {
                showToast("Login diperlukan untuk membuka kitab")
                return@launch
            }

            // Save reading progress (update timestamp)
            saveReadingProgress(page = lastPageRead.value)

            // Show loading state/toast
            showToast("Sedang mengunduh kitab...")
            android.util.Log.d("KitabDetailViewModel", "🔽 Start downloading kitab: $idKitab")
            
            // Prepare kitab object (minimal since we have ID and assume loaded)
            val kitab = (_uiState.value as? KitabDetailUiState.Success)?.kitab
            if (kitab == null) {
                 showToast("Gagal mengunduh: Data kitab belum dimuat")
                 return@launch
            }

            downloadRepository.downloadKitab(kitab, token)
                .onSuccess { file ->
                     android.util.Log.d("KitabDetailViewModel", "✅ Download success: ${file.absolutePath}")
                     showToast("Download berhasil!")
                     _navigateToPdf.value = "${file.absolutePath}|${_lastPageRead.value}"
                }
                .onFailure { error ->
                    android.util.Log.e("KitabDetailViewModel", "❌ Failed to download PDF", error)
                    showToast("Gagal mengunduh kitab: ${error.message}")
                }
        }
    }

    private fun openPdfFile(file: java.io.File) {
        _navigateToPdf.value = "${file.absolutePath}|${_lastPageRead.value}"
    }

    fun deleteComment(commentId: Int, kitabId: Int) {
        viewModelScope.launch {
            val token = sessionManager.getToken()
            android.util.Log.d("KitabDetailViewModel", "🗑️ DELETING COMMENT")
            android.util.Log.d("KitabDetailViewModel", "Comment ID: $commentId")
            android.util.Log.d("KitabDetailViewModel", "Kitab ID: $kitabId")
            android.util.Log.d("KitabDetailViewModel", "Token Available: ${token != null}")
            
            if (token != null) {
                repository.deleteComment(commentId, token)
                    .onSuccess { response ->
                        android.util.Log.d("KitabDetailViewModel", "✅ Comment deleted successfully")
                        android.util.Log.d("KitabDetailViewModel", "Response: ${response.success} - ${response.message}")
                        
                        if (response.success) {
                            showToast("Komentar berhasil dihapus")
                            // Reload comments to get the latest
                            loadComments(kitabId)
                        } else {
                            showToast("Gagal menghapus komentar: ${response.message}")
                        }
                    }
                    .onFailure { error ->
                        android.util.Log.e("KitabDetailViewModel", "❌ Failed to delete comment: ${error.message}")
                        showToast("Gagal menghapus komentar: ${error.message}")
                    }
            } else {
                android.util.Log.d("KitabDetailViewModel", "🔄 Mock comment deletion (no token)")
                showToast("Login diperlukan untuk menghapus komentar")
            }
        }
    }
    
    /**
     * Increment view count for kitab
     * Called when user opens PDF
     */
    private fun incrementViewCount(idKitab: Int) {
        viewModelScope.launch {
            val token = sessionManager.getToken() ?: SessionManager.getInstance(context).getToken()
            
            if (token != null) {
                repository.incrementView(idKitab, token)
                    .onSuccess { response ->
                        android.util.Log.d("KitabDetailViewModel", "✅ View incremented successfully")
                        // Don't need to update UI for views usually, unless we want to
                    }
                    .onFailure { error ->
                        android.util.Log.e("KitabDetailViewModel", "❌ Failed to increment view: ${error.message}")
                    }
            }
        }
    }

    private fun loadMyRating(idKitab: Int) {
        viewModelScope.launch {
            val token = sessionManager.getToken() ?: SessionManager.getInstance(context).getToken()
            if (token != null) {
                repository.getMyRating(idKitab, token)
                    .onSuccess { response ->
                        if (response.success && response.data != null) {
                            _myRating.value = response.data.myRating
                            android.util.Log.d("KitabDetailViewModel", "✅ My Rating Loaded: ${response.data.myRating}")
                        }
                    }
                    .onFailure {
                        android.util.Log.e("KitabDetailViewModel", "Failed to load my rating", it)
                    }
            }
        }
    }

    fun rateKitab(rating: Int) {
        val currentKitabId = kitabId
        if (currentKitabId == 0) return

        viewModelScope.launch {
            val token = sessionManager.getToken() ?: SessionManager.getInstance(context).getToken()
            
            if (token != null) {
                // Optimistic update
                val previousRating = _myRating.value
                _myRating.value = rating
                
                showToast("Mengirim penilaian...")
                
                repository.rateKitab(currentKitabId, rating, token)
                    .onSuccess { response ->
                        if (response.success && response.data != null) {
                            _myRating.value = response.data.rating
                            _averageRating.value = response.data.averageRating
                            _ratingsCount.value = response.data.ratingsCount
                            showToast("Terima kasih atas penilaian Anda!")
                        } else {
                            _myRating.value = previousRating // Revert
                            showToast("Gagal menilai: ${response.message}")
                        }
                    }
                    .onFailure { error ->
                        _myRating.value = previousRating // Revert
                        showToast("Gagal menilai: ${error.message}")
                    }
            } else {
                showToast("Silakan login untuk memberi penilaian")
            }
        }
    }
}

sealed class KitabDetailUiState {
    object Loading : KitabDetailUiState()
    data class Success(val kitab: Kitab) : KitabDetailUiState()
    data class Error(val message: String) : KitabDetailUiState()
}
