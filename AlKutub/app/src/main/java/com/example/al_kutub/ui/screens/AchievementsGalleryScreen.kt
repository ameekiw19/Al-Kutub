package com.example.al_kutub.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.Book
import androidx.compose.material.icons.filled.EmojiEvents
import androidx.compose.material.icons.filled.Lock
import androidx.compose.material.icons.filled.LocalFireDepartment
import androidx.compose.material.icons.filled.Star
import androidx.compose.material.icons.filled.MilitaryTech
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.alpha
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.example.al_kutub.model.AchievementEntry
import com.example.al_kutub.ui.theme.SharedColors
import com.example.al_kutub.ui.viewmodel.GamificationViewModel
import com.example.al_kutub.ui.viewmodel.UiState

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun AchievementsGalleryScreen(
    onBack: () -> Unit,
    viewModel: GamificationViewModel = hiltViewModel()
) {
    val achState by viewModel.achievementsState.collectAsStateWithLifecycle()

    LaunchedEffect(Unit) {
        viewModel.fetchAchievements()
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Koleksi Lencana", fontWeight = FontWeight.Bold) },
                navigationIcon = {
                    IconButton(onClick = onBack) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, "Back")
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = Color.White)
            )
        },
        containerColor = SharedColors.Slate50
    ) { padding ->
        Column(modifier = Modifier.padding(padding).fillMaxSize()) {
            when (val state = achState) {
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
                    val achievements = state.data
                    val unlockedCount = achievements.count { it.unlocked }
                    val totalCount = achievements.size

                    Column(modifier = Modifier.fillMaxSize()) {
                        AchievementGalleryHeader(unlockedCount, totalCount)
                        
                        LazyVerticalGrid(
                            columns = GridCells.Fixed(2),
                            modifier = Modifier.fillMaxSize(),
                            contentPadding = PaddingValues(16.dp),
                            horizontalArrangement = Arrangement.spacedBy(12.dp),
                            verticalArrangement = Arrangement.spacedBy(12.dp)
                        ) {
                            items(achievements) { ach ->
                                AchievementBadgeCard(ach)
                            }
                        }
                    }
                }
            }
        }
    }
}

@Composable
fun AchievementGalleryHeader(unlocked: Int, total: Int) {
    val percentage = if (total > 0) (unlocked.toFloat() / total.toFloat()) else 0f
    
    Card(
        modifier = Modifier.fillMaxWidth().padding(16.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        shape = RoundedCornerShape(20.dp),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(modifier = Modifier.padding(20.dp)) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Column {
                    Text(
                        "Progress Koleksi",
                        fontSize = 12.sp,
                        color = SharedColors.Slate500,
                        fontWeight = FontWeight.Bold
                    )
                    Text(
                        "$unlocked dari $total Lencana",
                        fontSize = 20.sp,
                        fontWeight = FontWeight.ExtraBold,
                        color = SharedColors.Slate800
                    )
                }
                Box(
                    modifier = Modifier.size(50.dp).background(SharedColors.TealMain.copy(alpha = 0.1f), CircleShape),
                    contentAlignment = Alignment.Center
                ) {
                    Icon(Icons.Default.EmojiEvents, null, tint = SharedColors.TealMain)
                }
            }
            Spacer(Modifier.height(16.dp))
            LinearProgressIndicator(
                progress = percentage,
                modifier = Modifier.fillMaxWidth().height(10.dp).clip(CircleShape),
                color = SharedColors.TealMain,
                trackColor = SharedColors.Slate100
            )
            Spacer(Modifier.height(8.dp))
            Text(
                "${(percentage * 100).toInt()}% Selesai",
                modifier = Modifier.fillMaxWidth(),
                textAlign = TextAlign.End,
                fontSize = 12.sp,
                fontWeight = FontWeight.Bold,
                color = SharedColors.TealMain
            )
        }
    }
}

@Composable
fun AchievementBadgeCard(ach: AchievementEntry) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        shape = RoundedCornerShape(16.dp),
        elevation = CardDefaults.cardElevation(defaultElevation = 1.dp),
        border = if (ach.unlocked) androidx.compose.foundation.BorderStroke(1.dp, SharedColors.TealMain.copy(alpha = 0.3f)) else null
    ) {
        Column(
            modifier = Modifier.padding(16.dp).fillMaxWidth(),
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.Center
        ) {
            Box(contentAlignment = Alignment.Center) {
                Box(
                    modifier = Modifier
                        .size(60.dp)
                        .clip(CircleShape)
                        .background(if (ach.unlocked) SharedColors.TealMain.copy(alpha = 0.1f) else SharedColors.Slate100)
                        .alpha(if (ach.unlocked) 1f else 0.5f),
                    contentAlignment = Alignment.Center
                ) {
                    Icon(
                        imageVector = getIconForAch(ach.id),
                        contentDescription = null,
                        tint = if (ach.unlocked) SharedColors.TealMain else SharedColors.Slate400,
                        modifier = Modifier.size(32.dp)
                    )
                }
                
                if (!ach.unlocked) {
                    Box(
                        modifier = Modifier
                            .align(Alignment.BottomEnd)
                            .size(20.dp)
                            .clip(CircleShape)
                            .background(Color.White)
                            .padding(2.dp)
                            .background(SharedColors.Slate400, CircleShape),
                        contentAlignment = Alignment.Center
                    ) {
                        Icon(Icons.Default.Lock, null, tint = Color.White, modifier = Modifier.size(10.dp))
                    }
                }
            }
            
            Spacer(Modifier.height(12.dp))
            
            Text(
                ach.name,
                fontSize = 14.sp,
                fontWeight = FontWeight.Bold,
                color = if (ach.unlocked) SharedColors.Slate800 else SharedColors.Slate500,
                textAlign = TextAlign.Center,
                maxLines = 1,
                overflow = TextOverflow.Ellipsis
            )
            
            Spacer(Modifier.height(4.dp))
            
            Text(
                ach.description,
                fontSize = 11.sp,
                color = SharedColors.Slate500,
                textAlign = TextAlign.Center,
                lineHeight = 14.sp,
                minLines = 2,
                maxLines = 2
            )
            
            Spacer(Modifier.height(12.dp))
            
            Column(horizontalAlignment = Alignment.CenterHorizontally) {
                LinearProgressIndicator(
                    progress = ach.progress / 100f,
                    modifier = Modifier.fillMaxWidth().height(6.dp).clip(CircleShape),
                    color = if (ach.unlocked) SharedColors.TealMain else SharedColors.Slate300,
                    trackColor = SharedColors.Slate100
                )
                Spacer(Modifier.height(4.dp))
                Text(
                    "${ach.progress.toInt()}%",
                    fontSize = 10.sp,
                    fontWeight = FontWeight.Bold,
                    color = if (ach.unlocked) SharedColors.TealMain else SharedColors.Slate400
                )
            }
        }
    }
}

fun getIconForAch(id: String): ImageVector {
    return when (id) {
        "first_read" -> Icons.Default.Book
        "week_streak" -> Icons.Default.LocalFireDepartment
        "month_streak" -> Icons.Default.Star
        "goal_master" -> Icons.Default.EmojiEvents
        "dedicated_reader" -> Icons.Default.MilitaryTech
        "legend" -> Icons.Default.EmojiEvents
        else -> Icons.Default.Star
    }
}
