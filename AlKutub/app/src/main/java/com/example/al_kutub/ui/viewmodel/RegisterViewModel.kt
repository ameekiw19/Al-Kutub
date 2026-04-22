package com.example.al_kutub.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.al_kutub.data.repository.OfflineSyncRepository
import com.example.al_kutub.data.repository.RegisterRepository
import com.example.al_kutub.model.LoginResponse
import com.example.al_kutub.utils.FcmTokenManager
import com.example.al_kutub.utils.SessionManager
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class RegisterViewModel @Inject constructor(
    private val repository: RegisterRepository,
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

    fun register(
        username: String,
        password: String,
        email: String,
        phone: String? = null,
        deskripsi: String? = null,
        onSuccess: () -> Unit,
        onEmailVerificationPending: (String, String) -> Unit = { _, _ -> }
    ) {
        if (username.isBlank()) {
            _error.value = "Username wajib diisi"
            return
        }
        if (password.isBlank()) {
            _error.value = "Password wajib diisi"
            return
        }
        if (password.length < 6) {
            _error.value = "Password minimal 6 karakter"
            return
        }
        if (email.isBlank()) {
            _error.value = "Email wajib diisi"
            return
        }
        if (!android.util.Patterns.EMAIL_ADDRESS.matcher(email).matches()) {
            _error.value = "Format email tidak valid"
            return
        }

        viewModelScope.launch {
            _isLoading.value = true
            _error.value = null

            try {
                val result = repository.registerUser(
                    username = username,
                    password = password,
                    email = email,
                    phone = phone,
                    deskripsi = deskripsi
                )

                result.onSuccess { registerResponse ->
                    _user.value = registerResponse
                    _isLoading.value = false

                    val data = registerResponse.data
                    if (data.requiresAdminApproval) {
                        _error.value = registerResponse.message.ifBlank {
                            "Registrasi berhasil. Akun menunggu verifikasi admin."
                        }
                        return@onSuccess
                    }

                    if (data.requiresEmailVerification) {
                        onEmailVerificationPending(data.email, data.verificationToken.orEmpty())
                        return@onSuccess
                    }

                    val token = data.token
                    if (token.isNullOrBlank()) {
                        _error.value = "Registrasi berhasil, tetapi token tidak tersedia"
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
                    _error.value = exception.message ?: "Registrasi gagal"
                    _isLoading.value = false
                }
            } catch (e: Exception) {
                _error.value = "Terjadi kesalahan: ${e.message}"
                _isLoading.value = false
            }
        }
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
