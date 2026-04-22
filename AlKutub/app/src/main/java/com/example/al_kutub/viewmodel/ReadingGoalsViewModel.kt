package com.example.al_kutub.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.al_kutub.api.ApiService
import com.example.al_kutub.model.HistoryStatsResponse
import com.example.al_kutub.model.ReadingGoal
import com.example.al_kutub.model.ReadingGoalApiItem
import com.example.al_kutub.model.ReadingGoalsResponse
import com.example.al_kutub.model.ReadingStreakResponse
import com.example.al_kutub.utils.SessionManager
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.async
import kotlinx.coroutines.coroutineScope
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch
import java.time.LocalDate
import javax.inject.Inject

@HiltViewModel
class ReadingGoalsViewModel @Inject constructor(
    private val apiService: ApiService,
    private val sessionManager: SessionManager
) : ViewModel() {

    private val _uiState = MutableStateFlow(ReadingGoalsUiState())
    val uiState: StateFlow<ReadingGoalsUiState> = _uiState

    init {
        loadReadingGoals()
    }

    fun loadReadingGoals() {
        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isLoading = true, error = null)

            try {
                val token = sessionManager.getToken()
                if (token.isNullOrBlank()) {
                    _uiState.value = _uiState.value.copy(
                        isLoading = false,
                        error = "Silakan login terlebih dahulu untuk melihat target membaca."
                    )
                    return@launch
                }

                val authHeader = "Bearer $token"

                coroutineScope {
                    val goalsDeferred = async { apiService.getReadingGoals(authHeader) }
                    val historyStatsDeferred = async { apiService.getHistoryStatistics(authHeader) }
                    val streakDeferred = async { apiService.getReadingStreak(authHeader) }

                    val goalsResponse = goalsDeferred.await()
                    val historyStatsResponse = historyStatsDeferred.await()
                    val streakResponse = streakDeferred.await()

                    if (!goalsResponse.isSuccessful || goalsResponse.body()?.success != true) {
                        _uiState.value = _uiState.value.copy(
                            isLoading = false,
                            error = goalsResponse.body()?.message ?: "Gagal memuat target membaca."
                        )
                        return@coroutineScope
                    }

                    val goalsData = goalsResponse.body()!!.data
                    val goals = listOf(
                        goalsData.dailyGoal.toUiModel(),
                        goalsData.weeklyGoal.toUiModel()
                    )

                    val totalBooksRead = historyStatsResponse.body().safeTotalBooksRead()
                    val totalPagesRead = goals.maxOfOrNull { it.completedBooks } ?: 0
                    val streakMessage = streakResponse.safeStatusMessage()

                    _uiState.value = ReadingGoalsUiState(
                        goals = goals,
                        isLoading = false,
                        error = null,
                        totalBooksRead = totalBooksRead,
                        totalPagesRead = totalPagesRead,
                        streakMessage = streakMessage
                    )
                }
            } catch (e: Exception) {
                _uiState.value = _uiState.value.copy(
                    isLoading = false,
                    error = e.localizedMessage ?: "Terjadi kesalahan saat memuat target membaca."
                )
            }
        }
    }

    private fun ReadingGoalApiItem.toUiModel(): ReadingGoal {
        val resolvedDeadline = endDate?.toLocalDateOrNull()
            ?: startDate.toLocalDateOrNull()
            ?: LocalDate.now()

        return ReadingGoal(
            id = id.toString(),
            title = if (type == "weekly") "Target Mingguan" else "Target Harian",
            targetBooks = targetPages,
            completedBooks = currentPages,
            deadline = resolvedDeadline,
            isCompleted = isCompleted,
            goalType = type,
            targetMinutes = targetMinutes,
            completedMinutes = currentMinutes
        )
    }

    private fun HistoryStatsResponse?.safeTotalBooksRead(): Int {
        return if (this?.success == true) {
            this.data?.total_kitab ?: 0
        } else {
            0
        }
    }

    private fun retrofit2.Response<ReadingStreakResponse>.safeStatusMessage(): String {
        val body = body()
        return if (isSuccessful && body?.success == true) {
            body.data.statusMessage
        } else {
            ""
        }
    }

    private fun String.toLocalDateOrNull(): LocalDate? {
        return runCatching { LocalDate.parse(this.take(10)) }.getOrNull()
    }
}

data class ReadingGoalsUiState(
    val goals: List<ReadingGoal> = emptyList(),
    val isLoading: Boolean = false,
    val error: String? = null,
    val totalBooksRead: Int = 0,
    val totalPagesRead: Int = 0,
    val streakMessage: String = ""
)
