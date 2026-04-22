package com.example.al_kutub.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.al_kutub.data.repository.TwoFactorRepository
import com.example.al_kutub.model.LoginResponse
import com.example.al_kutub.model.BaseResponse
import com.example.al_kutub.utils.SessionManager
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class TwoFactorViewModel @Inject constructor(
    private val twoFactorRepository: TwoFactorRepository,
    private val sessionManager: SessionManager
) : ViewModel() {

    private val _uiState = MutableStateFlow<TwoFactorUiState>(TwoFactorUiState.Idle)
    val uiState: StateFlow<TwoFactorUiState> = _uiState.asStateFlow()

    fun verify2FA(userId: Int, code: String, tempToken: String) {
        viewModelScope.launch {
            _uiState.value = TwoFactorUiState.Loading
            
            try {
                val response = twoFactorRepository.verify2FALogin(userId, code, tempToken)
                
                if (response.isSuccessful && response.body() != null) {
                    val loginResponse = response.body()!!
                    if (loginResponse.success) {
                        // Save token and user data to session
                        sessionManager.saveToken(loginResponse.data.token ?: "")
                        sessionManager.saveUserId(loginResponse.data.id ?: 0)
                        sessionManager.saveUsername(loginResponse.data.username ?: "")
                        
                        _uiState.value = TwoFactorUiState.Success(
                            token = loginResponse.data.token ?: "",
                            userData = loginResponse.data
                        )
                    } else {
                        _uiState.value = TwoFactorUiState.Error(
                            message = loginResponse.message ?: "Verifikasi gagal"
                        )
                    }
                } else {
                    _uiState.value = TwoFactorUiState.Error(
                        message = "Kode verifikasi tidak valid"
                    )
                }
            } catch (e: Exception) {
                _uiState.value = TwoFactorUiState.Error(
                    message = "Terjadi kesalahan: ${e.message}"
                )
            }
        }
    }

    fun verifyBackupCode(code: String) {
        viewModelScope.launch {
            _uiState.value = TwoFactorUiState.Loading
            
            try {
                val token = sessionManager.getToken() ?: return@launch
                val response = twoFactorRepository.verifyBackupCode(token, code)
                
                if (response.isSuccessful && response.body() != null) {
                    val baseResponse = response.body()!!
                    if (baseResponse.success) {
                        _uiState.value = TwoFactorUiState.BackupCodeSuccess(
                            message = baseResponse.message
                        )
                    } else {
                        _uiState.value = TwoFactorUiState.Error(
                            message = baseResponse.message
                        )
                    }
                } else {
                    _uiState.value = TwoFactorUiState.Error(
                        message = "Backup code tidak valid"
                    )
                }
            } catch (e: Exception) {
                _uiState.value = TwoFactorUiState.Error(
                    message = "Terjadi kesalahan: ${e.message}"
                )
            }
        }
    }

    fun resetState() {
        _uiState.value = TwoFactorUiState.Idle
    }

    sealed class TwoFactorUiState {
        object Idle : TwoFactorUiState()
        object Loading : TwoFactorUiState()
        data class Success(
            val token: String,
            val userData: com.example.al_kutub.model.LoginData
        ) : TwoFactorUiState()
        data class BackupCodeSuccess(
            val message: String
        ) : TwoFactorUiState()
        data class Error(
            val message: String
        ) : TwoFactorUiState()
    }
}
