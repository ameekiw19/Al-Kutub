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
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.MenuBook
import androidx.compose.material.icons.filled.AutoStories
import androidx.compose.material.icons.filled.Flag
import androidx.compose.material.icons.filled.Refresh
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
import com.example.al_kutub.model.ReadingGoal
import com.example.al_kutub.ui.theme.SharedColors
import com.example.al_kutub.viewmodel.ReadingGoalsViewModel
import java.time.format.DateTimeFormatter

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ReadingGoalsScreen(
    navController: androidx.navigation.NavController,
    modifier: Modifier = Modifier,
    viewModel: ReadingGoalsViewModel = hiltViewModel()
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
                    text = "Reading Goals",
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
                IconButton(onClick = { viewModel.loadReadingGoals() }) {
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
                ErrorStateCard(
                    message = uiState.error,
                    onRetry = { viewModel.loadReadingGoals() }
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
                    item {
                        ProgressOverviewCard(
                            totalGoals = uiState.goals.size,
                            completedGoals = uiState.goals.count { it.isCompleted },
                            totalBooksRead = uiState.totalBooksRead,
                            totalPagesRead = uiState.totalPagesRead,
                            streakMessage = uiState.streakMessage,
                            onNavigateToStatistics = { navController.navigate("reading_statistics_screen") }
                        )
                    }

                    if (uiState.goals.isEmpty()) {
                        item {
                            EmptyGoalsCard()
                        }
                    } else {
                        items(uiState.goals) { goal ->
                            ReadingGoalItem(goal = goal)
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun ProgressOverviewCard(
    totalGoals: Int,
    completedGoals: Int,
    totalBooksRead: Int,
    totalPagesRead: Int,
    streakMessage: String,
    onNavigateToStatistics: () -> Unit
) {
    val completionRate = if (totalGoals > 0) {
        (completedGoals.toFloat() / totalGoals.toFloat()) * 100f
    } else {
        0f
    }

    Card(
        modifier = Modifier
            .fillMaxWidth()
            .clickable { onNavigateToStatistics() },
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(modifier = Modifier.padding(20.dp)) {
            Text(
                text = "Ringkasan Progress",
                fontSize = 20.sp,
                fontWeight = FontWeight.Bold,
                color = SharedColors.Slate800
            )

            if (streakMessage.isNotBlank()) {
                Spacer(modifier = Modifier.height(8.dp))
                Text(
                    text = streakMessage,
                    style = MaterialTheme.typography.bodyMedium,
                    color = SharedColors.TealMain
                )
            }

            Spacer(modifier = Modifier.height(16.dp))

            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceEvenly
            ) {
                ProgressStat(
                    icon = Icons.Default.Flag,
                    label = "Goals",
                    value = "$completedGoals/$totalGoals",
                    color = SharedColors.TealMain
                )
                ProgressStat(
                    icon = Icons.AutoMirrored.Filled.MenuBook,
                    label = "Kitab",
                    value = totalBooksRead.toString(),
                    color = SharedColors.TealMain
                )
                ProgressStat(
                    icon = Icons.Default.AutoStories,
                    label = "Halaman",
                    value = totalPagesRead.toString(),
                    color = SharedColors.Slate600
                )
            }

            Spacer(modifier = Modifier.height(16.dp))

            Text(
                text = "Penyelesaian keseluruhan ${completionRate.toInt()}%",
                style = MaterialTheme.typography.bodyMedium,
                color = SharedColors.Slate600
            )

            Spacer(modifier = Modifier.height(8.dp))

            LinearProgressIndicator(
                progress = { completionRate / 100f },
                modifier = Modifier
                    .fillMaxWidth()
                    .height(8.dp)
                    .clip(RoundedCornerShape(4.dp)),
                color = SharedColors.TealMain,
                trackColor = SharedColors.Slate200
            )
        }
    }
}

@Composable
private fun ProgressStat(
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
private fun ReadingGoalItem(goal: ReadingGoal) {
    val progress = goal.progressPercentage()
    val goalLabel = if (goal.goalType == "weekly") "Target 7 hari" else "Target hari ini"

    Card(
        modifier = Modifier.fillMaxWidth(),
        colors = CardDefaults.cardColors(
            containerColor = if (goal.isCompleted) {
                SharedColors.TealMain.copy(alpha = 0.1f)
            } else {
                Color.White
            }
        ),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(modifier = Modifier.padding(16.dp)) {
            Text(
                text = goal.title,
                fontSize = 18.sp,
                fontWeight = FontWeight.Bold,
                color = SharedColors.Slate800
            )

            Spacer(modifier = Modifier.height(4.dp))
            Text(
                text = goalLabel,
                style = MaterialTheme.typography.bodySmall,
                color = SharedColors.TealMain
            )

            Spacer(modifier = Modifier.height(12.dp))

            GoalMetricRow(
                label = "Halaman",
                current = goal.completedBooks,
                target = goal.targetBooks
            )

            Spacer(modifier = Modifier.height(8.dp))

            GoalMetricRow(
                label = "Menit",
                current = goal.completedMinutes,
                target = goal.targetMinutes
            )

            Spacer(modifier = Modifier.height(12.dp))

            Text(
                text = "Berlaku sampai ${goal.deadline.format(DateTimeFormatter.ofPattern("dd MMM yyyy"))}",
                style = MaterialTheme.typography.bodySmall,
                color = SharedColors.Slate500
            )

            Spacer(modifier = Modifier.height(8.dp))

            LinearProgressIndicator(
                progress = { progress },
                modifier = Modifier
                    .fillMaxWidth()
                    .height(8.dp)
                    .clip(RoundedCornerShape(4.dp)),
                color = SharedColors.TealMain,
                trackColor = SharedColors.Slate200
            )
        }
    }
}

@Composable
private fun GoalMetricRow(
    label: String,
    current: Int,
    target: Int
) {
    Row(
        modifier = Modifier.fillMaxWidth(),
        horizontalArrangement = Arrangement.SpaceBetween,
        verticalAlignment = Alignment.CenterVertically
    ) {
        Text(
            text = label,
            style = MaterialTheme.typography.bodyMedium,
            color = SharedColors.Slate600
        )
        Text(
            text = "$current / $target",
            style = MaterialTheme.typography.bodyMedium,
            fontWeight = FontWeight.SemiBold,
            color = SharedColors.Slate800
        )
    }
}

@Composable
private fun EmptyGoalsCard() {
    Card(
        modifier = Modifier.fillMaxWidth(),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(
            modifier = Modifier.padding(20.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            Text(
                text = "Belum ada target aktif",
                fontWeight = FontWeight.Bold,
                color = SharedColors.Slate800
            )
            Text(
                text = "Target harian dan mingguan akan muncul otomatis setelah akun memiliki data membaca.",
                color = SharedColors.Slate600
            )
        }
    }
}

@Composable
private fun ErrorStateCard(
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
