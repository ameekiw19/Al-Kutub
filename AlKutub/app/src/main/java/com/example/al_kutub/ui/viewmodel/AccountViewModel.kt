package com.example.al_kutub.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.al_kutub.model.*
import com.example.al_kutub.repository.AccountRepository
import com.example.al_kutub.utils.SessionManager
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

sealed class AccountUiState {
    object Loading : AccountUiState()
    data class Success(val data: AccountData) : AccountUiState()
    data class Error(val message: String) : AccountUiState()
}

sealed class HistoryUiState {
    object Loading : HistoryUiState()
    data class Success(val histories: List<AccountHistoryItem>) : HistoryUiState()
    data class Error(val message: String) : HistoryUiState()
}

sealed class BookmarksUiState {
    object Loading : BookmarksUiState()
    data class Success(val bookmarks: List<AccountBookmarkItem>) : BookmarksUiState()
    data class Error(val message: String) : BookmarksUiState()
}

sealed class CommentsUiState {
    object Loading : CommentsUiState()
    data class Success(val comments: List<AccountCommentItem>) : CommentsUiState()
    data class Error(val message: String) : CommentsUiState()
}

@HiltViewModel
class AccountViewModel @Inject constructor(
    private val repository: AccountRepository,
    private val sessionManager: SessionManager
) : ViewModel() {

    private val _accountState = MutableStateFlow<AccountUiState>(AccountUiState.Loading)
    val accountState: StateFlow<AccountUiState> = _accountState.asStateFlow()

    private val _historyState = MutableStateFlow<HistoryUiState>(HistoryUiState.Loading)
    val historyState: StateFlow<HistoryUiState> = _historyState.asStateFlow()

    private val _bookmarksState = MutableStateFlow<BookmarksUiState>(BookmarksUiState.Loading)
    val bookmarksState: StateFlow<BookmarksUiState> = _bookmarksState.asStateFlow()

    private val _commentsState = MutableStateFlow<CommentsUiState>(CommentsUiState.Loading)
    val commentsState: StateFlow<CommentsUiState> = _commentsState.asStateFlow()

    private val _logoutState = MutableStateFlow<Boolean?>(null)
    val logoutState: StateFlow<Boolean?> = _logoutState.asStateFlow()

    private val _updateProfileState = MutableStateFlow<Boolean?>(null)
    val updateProfileState: StateFlow<Boolean?> = _updateProfileState.asStateFlow()

    /**
     * Load account data (profile + statistik + aktivitas terbaru)
     */
    fun loadAccount() {
        viewModelScope.launch {
            _accountState.value = AccountUiState.Loading
            try {
                if (!sessionManager.isLoggedIn()) {
                    _accountState.value = AccountUiState.Error("Silakan login terlebih dahulu")
                    return@launch
                }

                val response = repository.getAccount()
                if (response.isSuccessful && response.body()?.success == true) {
                    _accountState.value = AccountUiState.Success(response.body()!!.data)
                } else {
                    _accountState.value = AccountUiState.Error(
                        response.body()?.message ?: "Gagal memuat data akun"
                    )
                }
            } catch (e: Exception) {
                _accountState.value = AccountUiState.Error(
                    e.message ?: "Terjadi kesalahan"
                )
            }
        }
    }

    /**
     * Load history
     */
    fun loadHistory() {
        viewModelScope.launch {
            _historyState.value = HistoryUiState.Loading
            try {
                if (!sessionManager.isLoggedIn()) {
                    _historyState.value = HistoryUiState.Error("Silakan login terlebih dahulu")
                    return@launch
                }

                val response = repository.getAccountHistory()
                if (response.isSuccessful && response.body()?.success == true) {
                    val data = response.body()!!.data
                    _historyState.value = HistoryUiState.Success(data)
                } else {
                    _historyState.value = HistoryUiState.Error(
                        response.body()?.message ?: "Gagal memuat riwayat"
                    )
                }
            } catch (e: Exception) {
                _historyState.value = HistoryUiState.Error(
                    e.message ?: "Terjadi kesalahan"
                )
            }
        }
    }

    /**
     * Load bookmarks
     */
    fun loadBookmarks() {
        viewModelScope.launch {
            _bookmarksState.value = BookmarksUiState.Loading
            try {
                if (!sessionManager.isLoggedIn()) {
                    _bookmarksState.value = BookmarksUiState.Error("Silakan login terlebih dahulu")
                    return@launch
                }

                val response = repository.getAccountBookmarks()
                if (response.isSuccessful && response.body()?.success == true) {
                    val data = response.body()!!.data
                    _bookmarksState.value = BookmarksUiState.Success(data)
                } else {
                    _bookmarksState.value = BookmarksUiState.Error(
                        response.body()?.message ?: "Gagal memuat bookmark"
                    )
                }
            } catch (e: Exception) {
                _bookmarksState.value = BookmarksUiState.Error(
                    e.message ?: "Terjadi kesalahan"
                )
            }
        }
    }

    /**
     * Load comments
     */
    fun loadComments() {
        viewModelScope.launch {
            _commentsState.value = CommentsUiState.Loading
            try {
                if (!sessionManager.isLoggedIn()) {
                    _commentsState.value = CommentsUiState.Error("Silakan login terlebih dahulu")
                    return@launch
                }

                val response = repository.getAccountComments()
                if (response.isSuccessful && response.body()?.success == true) {
                    val data = response.body()!!.data
                    _commentsState.value = CommentsUiState.Success(data)
                } else {
                    _commentsState.value = CommentsUiState.Error(
                        response.body()?.message ?: "Gagal memuat komentar"
                    )
                }
            } catch (e: Exception) {
                _commentsState.value = CommentsUiState.Error(
                    e.message ?: "Terjadi kesalahan"
                )
            }
        }
    }

    /**
     * Logout user
     */
    fun logout() {
        viewModelScope.launch {
            try {
                if (!sessionManager.isLoggedIn()) {
                    _logoutState.value = false
                    return@launch
                }

                val response = repository.logout()
                if (response.isSuccessful && response.body()?.success == true) {
                    // Hapus session
                    sessionManager.logout()
                    _logoutState.value = true
                } else {
                    _logoutState.value = false
                }
            } catch (e: Exception) {
                _logoutState.value = false
            }
        }
    }

    /**
     * Reset logout state
     */
    fun resetLogoutState() {
        _logoutState.value = null
    }

    /**
     * Update profile
     */
    fun updateProfile(username: String, email: String, deskripsi: String?, password: String? = null, passwordConfirmation: String? = null) {
        viewModelScope.launch {
            try {
                if (!sessionManager.isLoggedIn()) {
                    _updateProfileState.value = false
                    return@launch
                }

                val request = UpdateProfileRequest(
                    username = username,
                    email = email,
                    deskripsi = deskripsi,
                    password = password,
                    passwordConfirmation = passwordConfirmation
                )

                val response = repository.updateProfile(request)
                if (response.isSuccessful && response.body()?.success == true) {
                    sessionManager.saveUsername(username)
                    loadAccount()
                    _updateProfileState.value = true
                } else {
                    _updateProfileState.value = false
                }
            } catch (e: Exception) {
                _updateProfileState.value = false
            }
        }
    }

    /**
     * Reset update profile state
     */
    fun resetUpdateProfileState() {
        _updateProfileState.value = null
    }
}