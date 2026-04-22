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
class ForgotPasswordViewModel @Inject constructor(
    private val repository: AuthRecoveryRepository
) : ViewModel() {

    private val _isLoading = MutableStateFlow(false)
    val isLoading: StateFlow<Boolean> = _isLoading.asStateFlow()

    private val _message = MutableStateFlow<String?>(null)
    val message: StateFlow<String?> = _message.asStateFlow()

    private val _error = MutableStateFlow<String?>(null)
    val error: StateFlow<String?> = _error.asStateFlow()

    fun submit(email: String) {
        if (email.isBlank()) {
            _error.value = "Email wajib diisi"
            return
        }

        viewModelScope.launch {
            _isLoading.value = true
            _error.value = null
            _message.value = null

            repository.requestPasswordReset(email).onSuccess { msg ->
                _message.value = msg
                _isLoading.value = false
            }.onFailure { throwable ->
                _error.value = throwable.message ?: "Gagal mengirim request reset password"
                _isLoading.value = false
            }
        }
    }

    fun clearError() {
        _error.value = null
    }
}
