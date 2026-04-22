package com.example.al_kutub.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.al_kutub.data.repository.LoginRepository
import com.example.al_kutub.data.repository.OfflineSyncRepository
import com.example.al_kutub.model.LoginResponse
import com.example.al_kutub.utils.FcmTokenManager
import com.example.al_kutub.utils.SessionManager
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class LoginViewModel @Inject constructor(
    private val repository: LoginRepository,
    private val sessionManager: SessionManager,
    private val fcmTokenManager: FcmTokenManager,
    private val offlineSyncRepository: OfflineSyncRepository
) : ViewModel() {

    private val _user = MutableStateFlow<LoginResponse?>(null)
    val user: StateFlow<LoginResponse?> = _user

    private val _isLoading = MutableStateFlow(false)
    val isLoading: StateFlow<Boolean> = _isLoading

    private val _error = MutableStateFlow<String?>(null)
    val error: StateFlow<String?> = _error

    private val _token = MutableStateFlow<String?>(null)
    val token: StateFlow<String?> = _token

    private val _requires2FA = MutableStateFlow(false)
    val requires2FA: StateFlow<Boolean> = _requires2FA

    private val _tempToken = MutableStateFlow<String?>(null)
    val tempToken: StateFlow<String?> = _tempToken

    private val _userIdFor2FA = MutableStateFlow<Int?>(null)
    val userIdFor2FA: StateFlow<Int?> = _userIdFor2FA

    private val _requiresEmailVerification = MutableStateFlow(false)
    val requiresEmailVerification: StateFlow<Boolean> = _requiresEmailVerification

    private val _verificationToken = MutableStateFlow<String?>(null)
    val verificationToken: StateFlow<String?> = _verificationToken

    private val _verificationEmail = MutableStateFlow<String?>(null)
    val verificationEmail: StateFlow<String?> = _verificationEmail

    fun login(
        username: String,
        password: String,
        onSuccess: () -> Unit,
        on2FARequired: (Int, String, String) -> Unit = { _, _, _ -> },
        onEmailVerificationRequired: (String, String) -> Unit = { _, _ -> }
    ) {
        if (username.isBlank() || password.isBlank()) {
            _error.value = "Username dan password wajib diisi"
            return
        }

        viewModelScope.launch {
            _isLoading.value = true
            _error.value = null
            _requires2FA.value = false
            _requiresEmailVerification.value = false
            _tempToken.value = null
            _userIdFor2FA.value = null
            _verificationToken.value = null
            _verificationEmail.value = null

            try {
                val result = repository.loginUser(username, password)
                result.onSuccess { loginResponse ->
                    _user.value = loginResponse
                    _isLoading.value = false

                    val data = loginResponse.data

                    if (data.requiresAdminApproval) {
                        _error.value = loginResponse.message.ifBlank { "Akun menunggu verifikasi admin." }
                        return@onSuccess
                    }

                    if (data.requiresEmailVerification) {
                        val token = data.verificationToken.orEmpty()
                        _requiresEmailVerification.value = true
                        _verificationToken.value = token
                        _verificationEmail.value = data.email
                        onEmailVerificationRequired(data.email, token)
                        return@onSuccess
                    }

                    if (data.requires2FA) {
                        _requires2FA.value = true
                        _tempToken.value = data.tempToken
                        _userIdFor2FA.value = data.userId
                        on2FARequired(
                            data.userId ?: 0,
                            data.tempToken.orEmpty(),
                            data.username
                        )
                        return@onSuccess
                    }

                    val token = data.token
                    if (token.isNullOrBlank()) {
                        _error.value = "Token login tidak ditemukan"
                        return@onSuccess
                    }

                    _token.value = token
                    sessionManager.saveToken(token)
                    data.refreshToken?.takeIf { it.isNotBlank() }?.let { refresh ->
                        sessionManager.saveRefreshToken(refresh)
                    } ?: run {
                        sessionManager.clearRefreshToken()
                    }
                    sessionManager.saveUserId(data.id)
                    sessionManager.saveUsername(data.username)

                    fcmTokenManager.fetchAndSyncToken()
                    triggerPostLoginSync()
                    onSuccess()
                }.onFailure { exception ->
                    _error.value = exception.message ?: "Login gagal"
                    _isLoading.value = false
                }
            } catch (e: Exception) {
                _error.value = "Terjadi kesalahan: ${e.message}"
                _isLoading.value = false
            }
        }
    }

    fun reset2FAState() {
        _requires2FA.value = false
        _tempToken.value = null
        _userIdFor2FA.value = null
        _requiresEmailVerification.value = false
        _verificationToken.value = null
        _verificationEmail.value = null
    }

    fun clearError() {
        _error.value = null
    }

    private fun triggerPostLoginSync() {
        viewModelScope.launch {
            runCatching {
                offlineSyncRepository.processQueue()
                offlineSyncRepository.refreshSummary()
            }
        }
    }
}
