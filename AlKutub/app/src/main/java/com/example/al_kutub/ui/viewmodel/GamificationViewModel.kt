package com.example.al_kutub.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.al_kutub.api.ApiService
import com.example.al_kutub.model.AchievementEntry
import com.example.al_kutub.model.LeaderboardData
import com.example.al_kutub.utils.SessionManager
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

sealed class UiState<out T> {
    object Loading : UiState<Nothing>()
    data class Success<out T>(val data: T) : UiState<T>()
    data class Error(val message: String) : UiState<Nothing>()
}

@HiltViewModel
class GamificationViewModel @Inject constructor(
    private val apiService: ApiService,
    private val sessionManager: SessionManager
) : ViewModel() {

    private val _leaderboardState = MutableStateFlow<UiState<LeaderboardData>>(UiState.Loading)
    val leaderboardState: StateFlow<UiState<LeaderboardData>> = _leaderboardState.asStateFlow()

    private val _achievementsState = MutableStateFlow<UiState<List<AchievementEntry>>>(UiState.Loading)
    val achievementsState: StateFlow<UiState<List<AchievementEntry>>> = _achievementsState.asStateFlow()

    fun fetchLeaderboard() {
        viewModelScope.launch {
            _leaderboardState.value = UiState.Loading
            try {
                val token = sessionManager.getToken()
                if (token.isNullOrEmpty()) {
                    _leaderboardState.value = UiState.Error("Token missing")
                    return@launch
                }
                val response = apiService.getLeaderboard(authorization = "Bearer $token")
                if (response.isSuccessful) {
                    val data = response.body()?.data
                    if (data != null) {
                        _leaderboardState.value = UiState.Success(data)
                    } else {
                        _leaderboardState.value = UiState.Error("Data is empty")
                    }
                } else {
                    _leaderboardState.value = UiState.Error(response.message())
                }
            } catch (e: Exception) {
                _leaderboardState.value = UiState.Error(e.localizedMessage ?: "Unknown error")
            }
        }
    }

    fun fetchAchievements() {
        viewModelScope.launch {
            _achievementsState.value = UiState.Loading
            try {
                val token = sessionManager.getToken()
                if (token.isNullOrEmpty()) {
                    _achievementsState.value = UiState.Error("Token missing")
                    return@launch
                }
                val response = apiService.getAchievements(authorization = "Bearer $token")
                if (response.isSuccessful) {
                    val achievements = response.body()?.data?.achievements
                    if (achievements != null) {
                        _achievementsState.value = UiState.Success(achievements)
                    } else {
                        _achievementsState.value = UiState.Error("Data is empty")
                    }
                } else {
                    _achievementsState.value = UiState.Error(response.message())
                }
            } catch (e: Exception) {
                _achievementsState.value = UiState.Error(e.localizedMessage ?: "Unknown error")
            }
        }
    }
}
