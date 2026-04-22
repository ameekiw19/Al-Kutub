package com.example.al_kutub.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.itemsIndexed
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.EmojiEvents
import androidx.compose.material.icons.filled.LocalFireDepartment
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.Star
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.example.al_kutub.model.LeaderboardEntry
import com.example.al_kutub.ui.theme.SharedColors
import com.example.al_kutub.ui.viewmodel.GamificationViewModel
import com.example.al_kutub.ui.viewmodel.UiState

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun LeaderboardScreen(
    onBack: () -> Unit,
    viewModel: GamificationViewModel = hiltViewModel()
) {
    val leaderboardState by viewModel.leaderboardState.collectAsStateWithLifecycle()

    LaunchedEffect(Unit) {
        viewModel.fetchLeaderboard()
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Papan Peringkat", fontWeight = FontWeight.Bold) },
                navigationIcon = {
                    IconButton(onClick = onBack) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, "Back")
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = Color.White)
            )
        },
        bottomBar = {
            if (leaderboardState is UiState.Success) {
                val data = (leaderboardState as UiState.Success).data
                UserRankBottomBar(
                    rank = data.user_rank.rank ?: 0,
                    username = data.user_rank.username,
                    streak = data.user_rank.current_streak
                )
            }
        },
        containerColor = SharedColors.Slate50
    ) { padding ->
        Column(modifier = Modifier.padding(padding).fillMaxSize()) {
            when (val state = leaderboardState) {
                is UiState.Loading -> {
                    Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                        CircularProgressIndicator(color = SharedColors.TealMain)
                    }
                }
                is UiState.Error -> {
                    Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                        Text(state.message, color = SharedColors.ErrorRed)
                    }
                }
                is UiState.Success -> {
                    val entries = state.data.leaderboard
                    LazyColumn(
                        modifier = Modifier.fillMaxSize(),
                        contentPadding = PaddingValues(16.dp),
                        verticalArrangement = Arrangement.spacedBy(12.dp)
                    ) {
                        if (entries.isNotEmpty()) {
                            item {
                                LeaderboardHeader()
                            }
                            item {
                                PodiumSection(entries.take(3))
                                Spacer(Modifier.height(24.dp))
                            }
                            itemsIndexed(entries.drop(3)) { index, entry ->
                                LeaderboardItem(rank = index + 4, entry = entry)
                            }
                        } else {
                            item {
                                Box(Modifier.fillParentMaxSize(), contentAlignment = Alignment.Center) {
                                    Text("Belum ada data peringkat", color = SharedColors.Slate500)
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

@Composable
fun LeaderboardHeader() {
    Column(modifier = Modifier.fillMaxWidth().padding(bottom = 24.dp)) {
        Text(
            "Berlomba-lomba dalam Kebaikan",
            fontSize = 14.sp,
            color = SharedColors.TealMain,
            fontWeight = FontWeight.Bold
        )
        Text(
            "Santri Teraktif",
            fontSize = 24.sp,
            fontWeight = FontWeight.ExtraBold,
            color = SharedColors.Slate800
        )
        Text(
            "Pertahankan streak membacamu dan jadilah yang terbaik di komunitas Al-Kutub.",
            fontSize = 14.sp,
            color = SharedColors.Slate500
        )
    }
}

@Composable
fun PodiumSection(topThree: List<LeaderboardEntry>) {
    Row(
        modifier = Modifier.fillMaxWidth().height(200.dp),
        horizontalArrangement = Arrangement.spacedBy(8.dp),
        verticalAlignment = Alignment.Bottom
    ) {
        // Rank 2
        if (topThree.size >= 2) {
            PodiumItem(
                modifier = Modifier.weight(1f),
                entry = topThree[1],
                rank = 2,
                barHeight = 100.dp,
                color = Color(0xFFAAAAAA)
            )
        }
        // Rank 1
        if (topThree.size >= 1) {
            PodiumItem(
                modifier = Modifier.weight(1.2f),
                entry = topThree[0],
                rank = 1,
                barHeight = 140.dp,
                color = Color(0xFFFFD700)
            )
        }
        // Rank 3
        if (topThree.size >= 3) {
            PodiumItem(
                modifier = Modifier.weight(1f),
                entry = topThree[2],
                rank = 3,
                barHeight = 80.dp,
                color = Color(0xFFCD7F32)
            )
        }
    }
}

@Composable
fun PodiumItem(
    modifier: Modifier,
    entry: LeaderboardEntry,
    rank: Int,
    barHeight: androidx.compose.ui.unit.Dp,
    color: Color
) {
    Column(
        modifier = modifier,
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Box(contentAlignment = Alignment.TopCenter) {
            Column(horizontalAlignment = Alignment.CenterHorizontally) {
                Box(
                    modifier = Modifier
                        .size(if (rank == 1) 64.dp else 54.dp)
                        .clip(CircleShape)
                        .background(SharedColors.Slate200)
                        .padding(2.dp)
                        .background(Color.White, CircleShape),
                    contentAlignment = Alignment.Center
                ) {
                    Icon(
                        imageVector = if (rank == 1) Icons.Default.EmojiEvents else Icons.Default.Person,
                        contentDescription = null,
                        tint = if (rank == 1) color else SharedColors.Slate400,
                        modifier = Modifier.size(if (rank == 1) 32.dp else 24.dp)
                    )
                }
                Spacer(Modifier.height(4.dp))
                Text(
                    entry.username,
                    fontSize = 12.sp,
                    fontWeight = FontWeight.Bold,
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis
                )
                Text(
                    "${entry.current_streak} hari",
                    fontSize = 11.sp,
                    color = SharedColors.TealMain,
                    fontWeight = FontWeight.Bold
                )
                Spacer(Modifier.height(8.dp))
                Box(
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(barHeight)
                        .clip(RoundedCornerShape(topStart = 12.dp, topEnd = 12.dp))
                        .background(
                            Brush.verticalGradient(
                                colors = listOf(color.copy(alpha = 0.3f), color.copy(alpha = 0.1f))
                            )
                        ),
                    contentAlignment = Alignment.TopCenter
                ) {
                    Text(
                        rank.toString(),
                        modifier = Modifier.padding(top = 8.dp),
                        fontSize = 20.sp,
                        fontWeight = FontWeight.ExtraBold,
                        color = color
                    )
                }
            }
        }
    }
}

@Composable
fun LeaderboardItem(rank: Int, entry: LeaderboardEntry) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        shape = RoundedCornerShape(16.dp),
        elevation = CardDefaults.cardElevation(defaultElevation = 1.dp)
    ) {
        Row(
            modifier = Modifier.padding(16.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Text(
                rank.toString(),
                modifier = Modifier.width(32.dp),
                fontSize = 16.sp,
                fontWeight = FontWeight.Bold,
                color = SharedColors.Slate500,
                textAlign = TextAlign.Center
            )
            Box(
                modifier = Modifier.size(40.dp).clip(CircleShape).background(SharedColors.Slate100),
                contentAlignment = Alignment.Center
            ) {
                Icon(Icons.Default.Person, null, tint = SharedColors.Slate400)
            }
            Spacer(Modifier.width(12.dp))
            Text(
                entry.username,
                modifier = Modifier.weight(1f),
                fontWeight = FontWeight.Bold,
                color = SharedColors.Slate800
            )
            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(
                    Icons.Default.LocalFireDepartment,
                    null,
                    tint = Color(0xFFF59E0B),
                    modifier = Modifier.size(16.dp)
                )
                Spacer(Modifier.width(4.dp))
                Text(
                    entry.current_streak.toString(),
                    fontWeight = FontWeight.Bold,
                    color = SharedColors.Slate800
                )
                Text(
                    " hari",
                    fontSize = 12.sp,
                    color = SharedColors.Slate500
                )
            }
        }
    }
}

@Composable
fun UserRankBottomBar(rank: Int, username: String, streak: Int) {
    Surface(
        modifier = Modifier.fillMaxWidth(),
        color = Color.White,
        shadowElevation = 16.dp,
        tonalElevation = 4.dp
    ) {
        Row(
            modifier = Modifier.padding(20.dp).fillMaxWidth(),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Box(
                modifier = Modifier.size(36.dp).clip(CircleShape).background(SharedColors.TealMain),
                contentAlignment = Alignment.Center
            ) {
                Text(
                    rank.toString(),
                    color = Color.White,
                    fontWeight = FontWeight.Bold,
                    fontSize = 14.sp
                )
            }
            Spacer(Modifier.width(12.dp))
            Column(modifier = Modifier.weight(1f)) {
                Text(
                    username,
                    fontWeight = FontWeight.Bold,
                    fontSize = 15.sp,
                    color = SharedColors.Slate800
                )
                Text(
                    "Peringkat Kamu",
                    fontSize = 12.sp,
                    color = SharedColors.Slate500
                )
            }
            Row(
                verticalAlignment = Alignment.CenterVertically,
                modifier = Modifier
                    .clip(RoundedCornerShape(20.dp))
                    .background(Color(0xFFF59E0B).copy(alpha = 0.1f))
                    .padding(horizontal = 12.dp, vertical = 6.dp)
            ) {
                Icon(
                    Icons.Default.LocalFireDepartment,
                    null,
                    tint = Color(0xFFF59E0B),
                    modifier = Modifier.size(16.dp)
                )
                Spacer(Modifier.width(4.dp))
                Text(
                    streak.toString(),
                    fontWeight = FontWeight.Bold,
                    color = Color(0xFFF59E0B)
                )
                Text(
                    " hari",
                    fontSize = 11.sp,
                    color = Color(0xFFF59E0B)
                )
            }
        }
    }
}
