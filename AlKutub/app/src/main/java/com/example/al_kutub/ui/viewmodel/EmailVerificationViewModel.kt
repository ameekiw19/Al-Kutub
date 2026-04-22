package com.example.al_kutub.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.al_kutub.data.repository.AuthRecoveryRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class EmailVerificationViewModel @Inject constructor(
    private val repository: AuthRecoveryRepository
) : ViewModel() {

    private val _isResending = MutableStateFlow(false)
    val isResending: StateFlow<Boolean> = _isResending.asStateFlow()

    private val _isChecking = MutableStateFlow(false)
    val isChecking: StateFlow<Boolean> = _isChecking.asStateFlow()

    private val _isVerified = MutableStateFlow(false)
    val isVerified: StateFlow<Boolean> = _isVerified.asStateFlow()

    private val _currentToken = MutableStateFlow<String?>(null)
    val currentToken: StateFlow<String?> = _currentToken.asStateFlow()

    private val _message = MutableStateFlow<String?>(null)
    val message: StateFlow<String?> = _message.asStateFlow()

    private val _error = MutableStateFlow<String?>(null)
    val error: StateFlow<String?> = _error.asStateFlow()

    fun setInitialToken(token: String) {
        if (_currentToken.value.isNullOrBlank() && token.isNotBlank()) {
            _currentToken.value = token
        }
    }

    fun resendVerification() {
        val token = _currentToken.value
        if (token.isNullOrBlank()) {
            _error.value = "Token verifikasi tidak tersedia. Silakan login ulang."
            return
        }

        viewModelScope.launch {
            _isResending.value = true
            _error.value = null
            _message.value = null

            repository.resendVerification(token).onSuccess { msg ->
                _message.value = msg
                _isResending.value = false
            }.onFailure { throwable ->
                _error.value = throwable.message ?: "Gagal mengirim ulang email verifikasi"
                _isResending.value = false
            }
        }
    }

    fun checkStatus() {
        val token = _currentToken.value
        if (token.isNullOrBlank()) {
            _error.value = "Token verifikasi tidak tersedia. Silakan login ulang."
            return
        }

        viewModelScope.launch {
            _isChecking.value = true
            _error.value = null
            _message.value = null

            repository.checkVerificationStatus(token).onSuccess { data ->
                _isVerified.value = data.verified
                _currentToken.value = data.verificationToken ?: token
                _message.value = if (data.verified) {
                    "Email sudah terverifikasi. Silakan login."
                } else {
                    "Email belum terverifikasi. Silakan cek inbox Anda."
                }
                _isChecking.value = false
            }.onFailure { throwable ->
                _error.value = throwable.message ?: "Gagal memeriksa status verifikasi"
                _isChecking.value = false
            }
        }
    }

    fun clearError() {
        _error.value = null
    }

    fun clearMessage() {
        _message.value = null
    }
}
