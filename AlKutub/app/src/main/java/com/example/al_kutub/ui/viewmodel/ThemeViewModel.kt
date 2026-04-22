package com.example.al_kutub.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.al_kutub.data.repository.ThemeRepository
import com.example.al_kutub.model.ThemeMode
import com.example.al_kutub.utils.SessionManager
import com.example.al_kutub.utils.ThemeManager
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class ThemeViewModel @Inject constructor(
    private val themeRepository: ThemeRepository,
    private val themeManager: ThemeManager,
    private val sessionManager: SessionManager
) : ViewModel() {

    private val _uiState = MutableStateFlow<ThemeUiState>(ThemeUiState.Idle)
    val uiState: StateFlow<ThemeUiState> = _uiState.asStateFlow()

    private val _currentTheme = MutableStateFlow(ThemeMode.LIGHT)
    val currentTheme: StateFlow<ThemeMode> = _currentTheme.asStateFlow()

    init {
        loadLocalTheme()
    }

    /**
     * Load theme from local storage
     */
    private fun loadLocalTheme() {
        val theme = themeManager.getSavedTheme()
        _currentTheme.value = theme
        themeManager.applyTheme(theme)
    }

    /**
     * Load theme from server
     */
    fun loadThemeFromServer() {
        viewModelScope.launch {
            _uiState.value = ThemeUiState.Loading
            
            try {
                val token = sessionManager.getToken() ?: return@launch
                val response = themeRepository.getTheme("Bearer $token")
                
                if (response.isSuccessful && response.body() != null) {
                    val themeResponse = response.body()!!
                    if (themeResponse.success) {
                        val theme = ThemeMode.fromString(themeResponse.data.theme)
                        _currentTheme.value = theme
                        themeManager.applyTheme(theme)
                        themeManager.saveTheme(theme)
                        _uiState.value = ThemeUiState.Success("Theme loaded successfully")
                    } else {
                        _uiState.value = ThemeUiState.Error(themeResponse.message)
                    }
                } else {
                    _uiState.value = ThemeUiState.Error("Failed to load theme")
                }
            } catch (e: Exception) {
                _uiState.value = ThemeUiState.Error("Error: ${e.message}")
            }
        }
    }

    /**
     * Update theme locally and sync with server
     */
    fun updateTheme(theme: ThemeMode) {
        viewModelScope.launch {
            _uiState.value = ThemeUiState.Loading
            
            try {
                // Apply locally first
                _currentTheme.value = theme
                themeManager.applyTheme(theme)
                themeManager.saveTheme(theme)
                
                // Sync with server
                val token = sessionManager.getToken()
                if (token != null) {
                    val response = themeRepository.updateTheme("Bearer $token", theme)
                    
                    if (response.isSuccessful && response.body() != null) {
                        val themeResponse = response.body()!!
                        if (themeResponse.success) {
                            _uiState.value = ThemeUiState.Success("Theme updated successfully")
                        } else {
                            _uiState.value = ThemeUiState.Error(themeResponse.message)
                        }
                    } else {
                        _uiState.value = ThemeUiState.Error("Failed to sync theme with server")
                    }
                } else {
                    _uiState.value = ThemeUiState.Success("Theme updated locally")
                }
            } catch (e: Exception) {
                _uiState.value = ThemeUiState.Error("Error: ${e.message}")
            }
        }
    }

    /**
     * Toggle theme and sync with server
     */
    fun toggleTheme() {
        viewModelScope.launch {
            _uiState.value = ThemeUiState.Loading
            
            try {
                // Toggle locally first
                themeManager.toggleTheme()
                val newTheme = themeManager.getCurrentTheme()
                _currentTheme.value = newTheme
                
                // Sync with server
                val token = sessionManager.getToken()
                if (token != null) {
                    val response = themeRepository.toggleTheme("Bearer $token")
                    
                    if (response.isSuccessful && response.body() != null) {
                        val themeResponse = response.body()!!
                        if (themeResponse.success) {
                            val serverTheme = ThemeMode.fromString(themeResponse.data.theme)
                            if (serverTheme != newTheme) {
                                _currentTheme.value = serverTheme
                                themeManager.applyTheme(serverTheme)
                                themeManager.saveTheme(serverTheme)
                            }
                            _uiState.value = ThemeUiState.Success("Theme toggled successfully")
                        } else {
                            _uiState.value = ThemeUiState.Error(themeResponse.message)
                        }
                    } else {
                        _uiState.value = ThemeUiState.Error("Failed to sync theme with server")
                    }
                } else {
                    _uiState.value = ThemeUiState.Success("Theme toggled locally")
                }
            } catch (e: Exception) {
                _uiState.value = ThemeUiState.Error("Error: ${e.message}")
            }
        }
    }

    /**
     * Reset UI state
     */
    fun resetState() {
        _uiState.value = ThemeUiState.Idle
    }

    /**
     * Check if dark mode is active
     */
    fun isDarkMode(): Boolean {
        return themeManager.isDarkMode()
    }

    sealed class ThemeUiState {
        object Idle : ThemeUiState()
        object Loading : ThemeUiState()
        data class Success(val message: String) : ThemeUiState()
        data class Error(val message: String) : ThemeUiState()
    }
}
