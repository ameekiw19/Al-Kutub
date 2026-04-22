package com.example.al_kutub.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.MenuBook
import androidx.compose.material.icons.automirrored.filled.TrendingUp
import androidx.compose.material.icons.filled.AutoStories
import androidx.compose.material.icons.filled.CalendarToday
import androidx.compose.material.icons.filled.EmojiEvents
import androidx.compose.material.icons.filled.LocalFireDepartment
import androidx.compose.material.icons.filled.Refresh
import androidx.compose.material.icons.filled.Schedule
import androidx.compose.material3.Button
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.LinearProgressIndicator
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.example.al_kutub.model.ReadingStats
import com.example.al_kutub.ui.navigation.AppScreen
import com.example.al_kutub.ui.theme.SharedColors
import com.example.al_kutub.viewmodel.ReadingStatisticsViewModel

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ReadingStatisticsScreen(
    navController: androidx.navigation.NavController,
    modifier: Modifier = Modifier,
    viewModel: ReadingStatisticsViewModel = hiltViewModel()
) {
    val uiState = viewModel.uiState.collectAsStateWithLifecycle().value

    Column(
        modifier = modifier
            .fillMaxSize()
            .background(SharedColors.Slate50)
    ) {
        TopAppBar(
            title = {
                Text(
                    text = "Reading Statistics",
                    color = SharedColors.Slate800,
                    fontWeight = FontWeight.Bold
                )
            },
            navigationIcon = {
                IconButton(onClick = { navController.popBackStack() }) {
                    Icon(
                        imageVector = Icons.AutoMirrored.Filled.ArrowBack,
                        contentDescription = "Back",
                        tint = SharedColors.Slate600
                    )
                }
            },
            actions = {
                IconButton(onClick = { viewModel.refreshStatistics() }) {
                    Icon(
                        imageVector = Icons.Default.Refresh,
                        contentDescription = "Refresh",
                        tint = SharedColors.TealMain
                    )
                }
            },
            colors = TopAppBarDefaults.topAppBarColors(containerColor = Color.White)
        )

        when {
            uiState.isLoading -> {
                Box(
                    modifier = Modifier.fillMaxSize(),
                    contentAlignment = Alignment.Center
                ) {
                    CircularProgressIndicator(color = SharedColors.TealMain)
                }
            }

            uiState.error != null -> {
                StatisticsErrorCard(
                    message = uiState.error,
                    onRetry = { viewModel.refreshStatistics() }
                )
            }

            else -> {
                LazyColumn(
                    modifier = Modifier
                        .fillMaxSize()
                        .padding(horizontal = 16.dp),
                    verticalArrangement = Arrangement.spacedBy(16.dp),
                    contentPadding = PaddingValues(vertical = 16.dp)
                ) {
                    item { OverallStatsCard(stats = uiState.stats) }
                    item {
                        ReadingStreakCard(
                            currentStreak = uiState.stats.currentStreak,
                            longestStreak = uiState.stats.longestStreak,
                            onClick = { navController.navigate(AppScreen.Leaderboard.route) }
                        )
                    }
                    item { MonthlyProgressCard(monthlyData = uiState.stats.monthlyData) }
                    item { CategoryDistributionCard(categoryData = uiState.stats.categoryData) }
                    item { ReadingHabitsCard(habits = uiState.stats.readingHabits) }
                    item {
                        AchievementsCard(
                            achievements = uiState.stats.achievements,
                            onClick = { navController.navigate(AppScreen.Achievements.route) }
                        )
                    }
                }
            }
        }
    }
}

@Composable
private fun OverallStatsCard(stats: ReadingStats) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(modifier = Modifier.padding(20.dp)) {
            Text(
                text = "Overall Statistics",
                fontSize = 20.sp,
                fontWeight = FontWeight.Bold,
                color = SharedColors.Slate800
            )

            Spacer(modifier = Modifier.height(16.dp))

            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceEvenly
            ) {
                StatItem(
                    icon = Icons.AutoMirrored.Filled.MenuBook,
                    label = "Total Books",
                    value = stats.totalBooksRead.toString(),
                    color = SharedColors.TealMain
                )
                StatItem(
                    icon = Icons.Default.AutoStories,
                    label = "Pages Read",
                    value = stats.totalPagesRead.toString(),
                    color = SharedColors.Slate600
                )
                StatItem(
                    icon = Icons.Default.Schedule,
                    label = "Avg Daily",
                    value = stats.averagePagesPerDay.toInt().toString(),
                    color = SharedColors.SuccessGreen
                )
            }

            Spacer(modifier = Modifier.height(16.dp))

            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceEvenly
            ) {
                StatItem(
                    icon = Icons.Default.CalendarToday,
                    label = "Days Active",
                    value = stats.daysActive.toString(),
                    color = SharedColors.WarningAmber
                )
                StatItem(
                    icon = Icons.AutoMirrored.Filled.TrendingUp,
                    label = "This Month",
                    value = stats.thisMonthBooks.toString(),
                    color = SharedColors.TealMain
                )
            }
        }
    }
}

@Composable
private fun StatItem(
    icon: ImageVector,
    label: String,
    value: String,
    color: Color
) {
    Column(horizontalAlignment = Alignment.CenterHorizontally) {
        Box(
            modifier = Modifier
                .size(48.dp)
                .background(color = color.copy(alpha = 0.1f), shape = CircleShape),
            contentAlignment = Alignment.Center
        ) {
            Icon(
                imageVector = icon,
                contentDescription = null,
                tint = color,
                modifier = Modifier.size(24.dp)
            )
        }

        Spacer(modifier = Modifier.height(8.dp))

        Text(text = label, fontSize = 12.sp, color = SharedColors.Slate600)
        Text(
            text = value,
            fontSize = 16.sp,
            fontWeight = FontWeight.Bold,
            color = SharedColors.Slate800
        )
    }
}

@Composable
private fun ReadingStreakCard(
    currentStreak: Int,
    longestStreak: Int,
    onClick: () -> Unit
) {
    Card(
        modifier = Modifier
            .fillMaxWidth()
            .clickable { onClick() },
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(modifier = Modifier.padding(20.dp)) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = "Reading Streak",
                    fontSize = 18.sp,
                    fontWeight = FontWeight.Bold,
                    color = SharedColors.Slate800
                )

                Row(horizontalArrangement = Arrangement.spacedBy(4.dp)) {
                    repeat(currentStreak.coerceAtMost(5)) {
                        Icon(
                            imageVector = Icons.Default.LocalFireDepartment,
                            contentDescription = null,
                            tint = SharedColors.WarningAmber,
                            modifier = Modifier.size(20.dp)
                        )
                    }
                }
            }

            Spacer(modifier = Modifier.height(16.dp))

            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceEvenly
            ) {
                StreakValue(label = "Current Streak", value = currentStreak.toString(), color = SharedColors.WarningAmber)
                StreakValue(label = "Longest Streak", value = longestStreak.toString(), color = SharedColors.Slate600)
            }
        }
    }
}

@Composable
private fun StreakValue(label: String, value: String, color: Color) {
    Column(horizontalAlignment = Alignment.CenterHorizontally) {
        Text(
            text = value,
            fontSize = 32.sp,
            fontWeight = FontWeight.Bold,
            color = color
        )
        Text(
            text = label,
            fontSize = 12.sp,
            color = SharedColors.Slate600
        )
    }
}

@Composable
private fun MonthlyProgressCard(monthlyData: Map<String, Int>) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(modifier = Modifier.padding(20.dp)) {
            Text(
                text = "Monthly Progress",
                fontSize = 18.sp,
                fontWeight = FontWeight.Bold,
                color = SharedColors.Slate800
            )

            Spacer(modifier = Modifier.height(16.dp))

            if (monthlyData.isEmpty()) {
                Text(
                    text = "Belum ada data progres bulanan.",
                    color = SharedColors.Slate600
                )
            } else {
                val maxValue = monthlyData.values.maxOrNull() ?: 1
                monthlyData.entries.toList().takeLast(6).forEach { (month, books) ->
                    Row(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(vertical = 4.dp),
                        horizontalArrangement = Arrangement.SpaceBetween,
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Text(
                            text = month,
                            fontSize = 12.sp,
                            color = SharedColors.Slate600,
                            modifier = Modifier.width(60.dp)
                        )
                        LinearProgressIndicator(
                            progress = { books.toFloat() / maxValue.toFloat() },
                            modifier = Modifier
                                .weight(1f)
                                .height(8.dp)
                                .clip(RoundedCornerShape(4.dp))
                                .padding(horizontal = 8.dp),
                            color = SharedColors.TealMain,
                            trackColor = SharedColors.Slate200
                        )
                        Text(
                            text = books.toString(),
                            fontSize = 12.sp,
                            fontWeight = FontWeight.Bold,
                            color = SharedColors.Slate800,
                            modifier = Modifier.width(30.dp)
                        )
                    }
                }
            }
        }
    }
}

@Composable
private fun CategoryDistributionCard(categoryData: Map<String, Int>) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(modifier = Modifier.padding(20.dp)) {
            Text(
                text = "Category Distribution",
                fontSize = 18.sp,
                fontWeight = FontWeight.Bold,
                color = SharedColors.Slate800
            )

            Spacer(modifier = Modifier.height(16.dp))

            if (categoryData.isEmpty()) {
                Text(
                    text = "Kategori baca belum cukup untuk dihitung.",
                    color = SharedColors.Slate600
                )
            } else {
                categoryData.entries.forEach { (category, count) ->
                    Row(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(vertical = 4.dp),
                        horizontalArrangement = Arrangement.SpaceBetween,
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Text(
                            text = category,
                            fontSize = 14.sp,
                            color = SharedColors.Slate600,
                            modifier = Modifier.weight(1f)
                        )
                        Text(
                            text = "$count kitab",
                            fontSize = 14.sp,
                            fontWeight = FontWeight.Bold,
                            color = SharedColors.Slate800
                        )
                    }
                }
            }
        }
    }
}

@Composable
private fun ReadingHabitsCard(habits: Map<String, String>) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(modifier = Modifier.padding(20.dp)) {
            Text(
                text = "Reading Habits",
                fontSize = 18.sp,
                fontWeight = FontWeight.Bold,
                color = SharedColors.Slate800
            )

            Spacer(modifier = Modifier.height(16.dp))

            habits.forEach { (habit, value) ->
                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(vertical = 4.dp),
                    horizontalArrangement = Arrangement.SpaceBetween,
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Text(
                        text = habit,
                        fontSize = 14.sp,
                        color = SharedColors.Slate600,
                        modifier = Modifier.weight(1f)
                    )
                    Text(
                        text = value,
                        fontSize = 14.sp,
                        fontWeight = FontWeight.Bold,
                        color = SharedColors.TealMain
                    )
                }
            }
        }
    }
}

@Composable
private fun AchievementsCard(
    achievements: List<String>,
    onClick: () -> Unit
) {
    Card(
        modifier = Modifier
            .fillMaxWidth()
            .clickable { onClick() },
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(modifier = Modifier.padding(20.dp)) {
            Text(
                text = "Achievements",
                fontSize = 18.sp,
                fontWeight = FontWeight.Bold,
                color = SharedColors.Slate800
            )

            Spacer(modifier = Modifier.height(16.dp))

            if (achievements.isEmpty()) {
                Text(
                    text = "Belum ada achievement yang tercatat.",
                    color = SharedColors.Slate600
                )
            } else {
                achievements.forEach { achievement ->
                    Row(
                        modifier = Modifier.padding(vertical = 4.dp),
                        horizontalArrangement = Arrangement.spacedBy(8.dp),
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Icon(
                            imageVector = Icons.Default.EmojiEvents,
                            contentDescription = null,
                            tint = SharedColors.WarningAmber,
                            modifier = Modifier.size(20.dp)
                        )
                        Text(
                            text = achievement,
                            fontSize = 14.sp,
                            color = SharedColors.Slate600
                        )
                    }
                }
            }
        }
    }
}

@Composable
private fun StatisticsErrorCard(
    message: String,
    onRetry: () -> Unit
) {
    Box(
        modifier = Modifier
            .fillMaxSize()
            .padding(16.dp),
        contentAlignment = Alignment.Center
    ) {
        Card(
            colors = CardDefaults.cardColors(containerColor = Color.White),
            elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
        ) {
            Column(
                modifier = Modifier.padding(20.dp),
                horizontalAlignment = Alignment.CenterHorizontally,
                verticalArrangement = Arrangement.spacedBy(12.dp)
            ) {
                Text(
                    text = message,
                    color = SharedColors.Slate700
                )
                Button(onClick = onRetry) {
                    Text("Coba Lagi")
                }
            }
        }
    }
}
