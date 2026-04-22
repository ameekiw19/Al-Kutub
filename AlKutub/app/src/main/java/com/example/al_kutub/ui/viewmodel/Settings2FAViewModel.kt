package com.example.al_kutub.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.al_kutub.data.repository.TwoFactorRepository
import com.example.al_kutub.utils.SessionManager
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableSharedFlow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.SharedFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asSharedFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class Settings2FAViewModel @Inject constructor(
    private val twoFactorRepository: TwoFactorRepository,
    private val sessionManager: SessionManager
) : ViewModel() {

    private val _uiState = MutableStateFlow(Settings2FAUiState())
    val uiState: StateFlow<Settings2FAUiState> = _uiState.asStateFlow()

    private val _events = MutableSharedFlow<Settings2FAEvent>()
    val events: SharedFlow<Settings2FAEvent> = _events.asSharedFlow()

    fun load2FAStatus(showLoading: Boolean = true) {
        viewModelScope.launch {
            if (showLoading) {
                _uiState.value = _uiState.value.copy(isLoading = true, errorMessage = null)
            } else {
                _uiState.value = _uiState.value.copy(errorMessage = null)
            }

            try {
                val token = sessionManager.getToken()
                if (token.isNullOrBlank()) {
                    _uiState.value = _uiState.value.copy(
                        isLoading = false,
                        errorMessage = "Sesi login tidak ditemukan. Silakan login ulang."
                    )
                    return@launch
                }

                val response = twoFactorRepository.get2FAStatus(token)
                if (response.isSuccessful && response.body()?.success == true) {
                    val statusData = response.body()!!.data
                    _uiState.value = _uiState.value.copy(
                        isLoading = false,
                        status = if (statusData.enabled) TwoFAStatus.ENABLED else TwoFAStatus.DISABLED,
                        enabledAt = statusData.enabledAt,
                        lastUsedAt = statusData.lastUsedAt,
                        backupCodesCount = statusData.backupCodesCount,
                        errorMessage = null
                    )
                } else {
                    _uiState.value = _uiState.value.copy(
                        isLoading = false,
                        errorMessage = "Gagal memuat status 2FA"
                    )
                }
            } catch (e: Exception) {
                _uiState.value = _uiState.value.copy(
                    isLoading = false,
                    errorMessage = "Terjadi kesalahan: ${e.message}"
                )
            }
        }
    }

    fun disable2FA(password: String, code: String) {
        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isLoading = true, errorMessage = null)

            try {
                val token = sessionManager.getToken()
                if (token.isNullOrBlank()) {
                    _uiState.value = _uiState.value.copy(
                        isLoading = false,
                        errorMessage = "Sesi login tidak ditemukan. Silakan login ulang."
                    )
                    return@launch
                }

                val response = twoFactorRepository.disable2FA(token, password, code)
                if (response.isSuccessful && response.body()?.success == true) {
                    _events.emit(Settings2FAEvent.DisableSuccess)
                    load2FAStatus(showLoading = false)
                } else {
                    val message = response.body()?.message ?: "Gagal menonaktifkan 2FA"
                    _uiState.value = _uiState.value.copy(
                        isLoading = false,
                        errorMessage = message
                    )
                    _events.emit(Settings2FAEvent.Error(message))
                }
            } catch (e: Exception) {
                val message = "Terjadi kesalahan: ${e.message}"
                _uiState.value = _uiState.value.copy(
                    isLoading = false,
                    errorMessage = message
                )
                _events.emit(Settings2FAEvent.Error(message))
            }
        }
    }

    fun clearError() {
        _uiState.value = _uiState.value.copy(errorMessage = null)
    }

    data class Settings2FAUiState(
        val isLoading: Boolean = false,
        val status: TwoFAStatus = TwoFAStatus.UNKNOWN,
        val enabledAt: String? = null,
        val lastUsedAt: String? = null,
        val backupCodesCount: Int = 0,
        val errorMessage: String? = null
    )

    enum class TwoFAStatus {
        UNKNOWN,
        ENABLED,
        DISABLED
    }

    sealed class Settings2FAEvent {
        object DisableSuccess : Settings2FAEvent()
        data class Error(val message: String) : Settings2FAEvent()
    }
}
