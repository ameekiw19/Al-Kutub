package com.example.al_kutub.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.al_kutub.api.ApiService
import com.example.al_kutub.model.AchievementResponse
import com.example.al_kutub.model.HistoryItemData
import com.example.al_kutub.model.HistoryStatsResponse
import com.example.al_kutub.model.ReadingNotesStatsResponse
import com.example.al_kutub.model.ReadingStats
import com.example.al_kutub.model.ReadingStreakResponse
import com.example.al_kutub.utils.SessionManager
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.async
import kotlinx.coroutines.coroutineScope
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch
import java.time.LocalDate
import java.time.OffsetDateTime
import java.time.format.DateTimeFormatter
import java.util.Locale
import javax.inject.Inject

@HiltViewModel
class ReadingStatisticsViewModel @Inject constructor(
    private val apiService: ApiService,
    private val sessionManager: SessionManager
) : ViewModel() {

    private val _uiState = MutableStateFlow(ReadingStatisticsUiState())
    val uiState: StateFlow<ReadingStatisticsUiState> = _uiState

    init {
        loadReadingStatistics()
    }

    fun loadReadingStatistics() {
        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isLoading = true, error = null)

            try {
                val token = sessionManager.getToken()
                if (token.isNullOrBlank()) {
                    _uiState.value = _uiState.value.copy(
                        isLoading = false,
                        error = "Silakan login terlebih dahulu untuk melihat statistik membaca."
                    )
                    return@launch
                }

                val authHeader = "Bearer $token"

                coroutineScope {
                    val historiesDeferred = async { apiService.getHistories(authHeader) }
                    val historyStatsDeferred = async { apiService.getHistoryStatistics(authHeader) }
                    val streakDeferred = async { apiService.getReadingStreak(authHeader) }
                    val achievementsDeferred = async { apiService.getAchievements(authHeader) }
                    val notesStatsDeferred = async { apiService.getReadingNotesStats(authHeader) }

                    val historiesResponse = historiesDeferred.await()
                    val historyStatsResponse = historyStatsDeferred.await()
                    val streakResponse = streakDeferred.await()
                    val achievementsResponse = achievementsDeferred.await()
                    val notesStatsResponse = notesStatsDeferred.await()

                    val histories = historiesResponse.body()?.data?.raw_histories.orEmpty()
                    val historyStats = historyStatsResponse.body()?.data
                    val streakData = streakResponse.body()?.data
                    val notesStats = notesStatsResponse.body()?.data

                    if (!historiesResponse.isSuccessful && historyStats == null && streakData == null) {
                        _uiState.value = _uiState.value.copy(
                            isLoading = false,
                            error = "Gagal memuat statistik membaca."
                        )
                        return@coroutineScope
                    }

                    val totalBooksRead = historyStats?.total_kitab
                        ?: histories.map { it.kitab_id }.distinct().count()
                    val totalPagesRead = histories.sumOf { it.current_page.coerceAtLeast(0) }
                    val daysActive = streakData?.totalDays ?: 0
                    val averagePagesPerDay = if (daysActive > 0) {
                        totalPagesRead.toFloat() / daysActive.toFloat()
                    } else {
                        0f
                    }
                    val thisMonthBooks = histories.count { it.last_read_at.isInCurrentMonth() }

                    val monthlyData = buildMonthlyData(histories)
                    val categoryData = historyStats?.top_categories
                        ?.associate { it.category to it.total }
                        ?.takeIf { it.isNotEmpty() }
                        ?: buildCategoryData(histories)

                    val readingHabits = linkedMapOf(
                        "Status Streak" to (streakData?.statusMessage ?: "Belum ada streak aktif"),
                        "Rata-rata / Hari" to "${averagePagesPerDay.toInt()} halaman",
                        "Catatan Dibuat" to "${notesStats?.totalNotes ?: 0} catatan",
                        "Kitab Bulan Ini" to "$thisMonthBooks kitab"
                    )

                    val achievements = achievementsResponse.toAchievementLabels()

                    _uiState.value = ReadingStatisticsUiState(
                        stats = ReadingStats(
                            totalBooksRead = totalBooksRead,
                            totalPagesRead = totalPagesRead,
                            averagePagesPerDay = averagePagesPerDay,
                            daysActive = daysActive,
                            thisMonthBooks = thisMonthBooks,
                            currentStreak = streakData?.currentStreak ?: 0,
                            longestStreak = streakData?.longestStreak ?: 0,
                            monthlyData = monthlyData,
                            categoryData = categoryData,
                            readingHabits = readingHabits,
                            achievements = achievements
                        ),
                        isLoading = false,
                        error = null
                    )
                }
            } catch (e: Exception) {
                _uiState.value = _uiState.value.copy(
                    isLoading = false,
                    error = e.localizedMessage ?: "Terjadi kesalahan saat memuat statistik membaca."
                )
            }
        }
    }

    fun refreshStatistics() {
        loadReadingStatistics()
    }

    private fun buildMonthlyData(histories: List<HistoryItemData>): Map<String, Int> {
        val formatter = DateTimeFormatter.ofPattern("MMM", Locale.ENGLISH)
        val monthBuckets = mutableMapOf<String, Int>()

        histories.forEach { item ->
            val date = item.last_read_at.toLocalDateFlexible() ?: return@forEach
            val monthLabel = formatter.format(date)
            monthBuckets[monthLabel] = (monthBuckets[monthLabel] ?: 0) + 1
        }

        return if (monthBuckets.isNotEmpty()) {
            monthBuckets.toSortedMap(compareBy { monthOrder(it) })
        } else {
            emptyMap()
        }
    }

    private fun buildCategoryData(histories: List<HistoryItemData>): Map<String, Int> {
        return histories
            .mapNotNull { it.kitab?.kategori }
            .groupingBy { it }
            .eachCount()
            .toList()
            .sortedByDescending { it.second }
            .take(5)
            .toMap()
    }

    private fun retrofit2.Response<AchievementResponse>.toAchievementLabels(): List<String> {
        val body = body()
        if (!isSuccessful || body?.success != true) {
            return emptyList()
        }

        return body.data.achievements.map { achievement ->
            if (achievement.unlocked) {
                "${achievement.icon} ${achievement.name}"
            } else {
                "${achievement.icon} ${achievement.name} (${achievement.progress.toInt()}%)"
            }
        }
    }

    private fun String.toLocalDateFlexible(): LocalDate? {
        return runCatching { OffsetDateTime.parse(this).toLocalDate() }.getOrNull()
            ?: runCatching { LocalDate.parse(this.take(10)) }.getOrNull()
    }

    private fun String.isInCurrentMonth(): Boolean {
        val date = toLocalDateFlexible() ?: return false
        val now = LocalDate.now()
        return date.month == now.month && date.year == now.year
    }

    private fun monthOrder(monthLabel: String): Int {
        return listOf(
            "Jan", "Feb", "Mar", "Apr", "May", "Jun",
            "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
        ).indexOf(monthLabel).coerceAtLeast(0)
    }
}

data class ReadingStatisticsUiState(
    val stats: ReadingStats = ReadingStats(),
    val isLoading: Boolean = false,
    val error: String? = null
)
