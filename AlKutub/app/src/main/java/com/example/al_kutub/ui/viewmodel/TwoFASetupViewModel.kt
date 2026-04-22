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
class TwoFASetupViewModel @Inject constructor(
    private val twoFactorRepository: TwoFactorRepository,
    private val sessionManager: SessionManager
) : ViewModel() {

    private val _uiState = MutableStateFlow<TwoFASetupUiState>(TwoFASetupUiState.Idle)
    val uiState: StateFlow<TwoFASetupUiState> = _uiState.asStateFlow()

    private val _manageUiState = MutableStateFlow<ManageUiState>(ManageUiState.Idle)
    val manageUiState: StateFlow<ManageUiState> = _manageUiState.asStateFlow()

    private val _manageEvents = MutableSharedFlow<String>()
    val manageEvents: SharedFlow<String> = _manageEvents.asSharedFlow()

    fun setup2FA() {
        viewModelScope.launch {
            _uiState.value = TwoFASetupUiState.Loading

            try {
                val token = sessionManager.getToken()
                if (token.isNullOrBlank()) {
                    _uiState.value = TwoFASetupUiState.Error("Sesi login tidak ditemukan")
                    return@launch
                }

                val response = twoFactorRepository.setup2FA(token)
                if (response.isSuccessful && response.body()?.success == true) {
                    val data = response.body()!!.data
                    _uiState.value = TwoFASetupUiState.SetupReady(
                        qrCodeUrl = data.qrCodeUrl,
                        secretKey = data.secretKey,
                        backupCodes = data.backupCodes
                    )
                } else {
                    _uiState.value = TwoFASetupUiState.Error("Gagal memulai setup 2FA")
                }
            } catch (e: Exception) {
                _uiState.value = TwoFASetupUiState.Error("Terjadi kesalahan: ${e.message}")
            }
        }
    }

    fun enable2FA(code: String) {
        viewModelScope.launch {
            _uiState.value = TwoFASetupUiState.Enabling

            try {
                val token = sessionManager.getToken()
                if (token.isNullOrBlank()) {
                    _uiState.value = TwoFASetupUiState.Error("Sesi login tidak ditemukan")
                    return@launch
                }

                val response = twoFactorRepository.enable2FA(token, code)
                if (response.isSuccessful && response.body()?.success == true) {
                    val message = response.body()?.message ?: "2FA berhasil diaktifkan"
                    _uiState.value = TwoFASetupUiState.SetupComplete(message = message)
                } else {
                    _uiState.value = TwoFASetupUiState.Error(
                        response.body()?.message ?: "Kode verifikasi tidak valid"
                    )
                }
            } catch (e: Exception) {
                _uiState.value = TwoFASetupUiState.Error("Terjadi kesalahan: ${e.message}")
            }
        }
    }

    fun loadManageData() {
        val currentState = _manageUiState.value
        if (currentState is ManageUiState.ManageLoading || currentState is ManageUiState.Regenerating) {
            return
        }

        viewModelScope.launch {
            _manageUiState.value = ManageUiState.ManageLoading

            try {
                val token = sessionManager.getToken()
                if (token.isNullOrBlank()) {
                    _manageUiState.value = ManageUiState.Error("Sesi login tidak ditemukan")
                    return@launch
                }

                val statusResponse = twoFactorRepository.get2FAStatus(token)
                if (!statusResponse.isSuccessful || statusResponse.body()?.success != true) {
                    _manageUiState.value = ManageUiState.Error("Gagal memuat status 2FA")
                    return@launch
                }

                val statusData = statusResponse.body()!!.data
                if (!statusData.enabled) {
                    _manageUiState.value = ManageUiState.Disabled
                    return@launch
                }

                val backupResponse = twoFactorRepository.getBackupCodes(token)
                if (backupResponse.isSuccessful && backupResponse.body()?.success == true) {
                    val backupData = backupResponse.body()?.data
                    val backupCodes = backupData?.backupCodes ?: emptyList()
                    val remainingCount = backupData?.remainingCount ?: backupCodes.size

                    _manageUiState.value = ManageUiState.ManageReady(
                        backupCodes = backupCodes,
                        remainingCount = remainingCount,
                        lastUsedAt = statusData.lastUsedAt
                    )
                } else {
                    _manageUiState.value = ManageUiState.Error(
                        backupResponse.body()?.message ?: "Gagal memuat backup code"
                    )
                }
            } catch (e: Exception) {
                _manageUiState.value = ManageUiState.Error("Terjadi kesalahan: ${e.message}")
            }
        }
    }

    fun regenerateBackupCodes(password: String) {
        if (_manageUiState.value is ManageUiState.Regenerating) {
            return
        }

        viewModelScope.launch {
            val current = _manageUiState.value
            val currentLastUsedAt = if (current is ManageUiState.ManageReady) current.lastUsedAt else null
            _manageUiState.value = ManageUiState.Regenerating

            try {
                val token = sessionManager.getToken()
                if (token.isNullOrBlank()) {
                    _manageUiState.value = ManageUiState.Error("Sesi login tidak ditemukan")
                    return@launch
                }

                val response = twoFactorRepository.regenerateBackupCodes(token, password)
                if (response.isSuccessful && response.body()?.success == true) {
                    val data = response.body()?.data
                    val backupCodes = data?.backupCodes ?: emptyList()
                    val count = data?.remainingCount?.takeIf { it > 0 } ?: backupCodes.size

                    _manageUiState.value = ManageUiState.ManageReady(
                        backupCodes = backupCodes,
                        remainingCount = count,
                        lastUsedAt = currentLastUsedAt
                    )
                    _manageEvents.emit("Backup code berhasil diperbarui")
                } else {
                    _manageUiState.value = ManageUiState.Error(
                        response.body()?.message ?: "Gagal memperbarui backup code"
                    )
                }
            } catch (e: Exception) {
                _manageUiState.value = ManageUiState.Error("Terjadi kesalahan: ${e.message}")
            }
        }
    }

    fun resetState() {
        _uiState.value = TwoFASetupUiState.Idle
    }

    sealed class TwoFASetupUiState {
        object Idle : TwoFASetupUiState()
        object Loading : TwoFASetupUiState()
        object Enabling : TwoFASetupUiState()
        data class SetupReady(
            val qrCodeUrl: String,
            val secretKey: String,
            val backupCodes: List<String>
        ) : TwoFASetupUiState()
        data class SetupComplete(
            val message: String
        ) : TwoFASetupUiState()
        data class Error(
            val message: String
        ) : TwoFASetupUiState()
    }

    sealed class ManageUiState {
        object Idle : ManageUiState()
        object ManageLoading : ManageUiState()
        object Regenerating : ManageUiState()
        object Disabled : ManageUiState()
        data class ManageReady(
            val backupCodes: List<String>,
            val remainingCount: Int,
            val lastUsedAt: String?
        ) : ManageUiState()
        data class Error(val message: String) : ManageUiState()
    }
}
