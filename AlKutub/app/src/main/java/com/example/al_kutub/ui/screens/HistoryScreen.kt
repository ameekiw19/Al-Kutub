package com.example.al_kutub.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.border
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
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowForward
import androidx.compose.material.icons.automirrored.filled.MenuBook
import androidx.compose.material.icons.filled.Delete
import androidx.compose.material.icons.filled.Schedule
import androidx.compose.material.icons.filled.WarningAmber
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.Scaffold
import androidx.compose.material3.SnackbarHost
import androidx.compose.material3.SnackbarHostState
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.navigation.NavController
import coil.compose.AsyncImage
import com.example.al_kutub.api.ApiConfig
import com.example.al_kutub.data.repository.HistoriesState
import com.example.al_kutub.model.HistoryItemData
import com.example.al_kutub.ui.components.SyncStatusChip
import com.example.al_kutub.ui.navigation.AppScreen
import com.example.al_kutub.ui.viewmodel.ContinueReadingNavigationEvent
import com.example.al_kutub.ui.viewmodel.HistoryViewModel
import kotlinx.coroutines.flow.collectLatest
import java.net.URLEncoder
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale
import kotlin.math.roundToInt

private val HistoryBackground = Color(0xFFFAFAF5)
private val HistoryHeaderStart = Color(0xFF1B5E3B)
private val HistoryHeaderEnd = Color(0xFF2D7A52)
private val HistoryGold = Color(0xFFC8A951)
private val HistoryCardBorder = Color(0xFFF0EBE0)
private val HistoryPrimaryText = Color(0xFF1A2E1A)
private val HistorySecondaryText = Color(0xFF6B5E4E)
private val HistoryMutedText = Color(0xFF8B8070)

@Composable
fun HistoryScreen(
    navController: NavController,
    viewModel: HistoryViewModel = hiltViewModel()
) {
    val historiesState by viewModel.historiesState.collectAsState()
    val syncSummary by viewModel.syncSummary.collectAsState()
    val toastMessage by viewModel.toastMessage.collectAsState()

    var showClearDialog by remember { mutableStateOf(false) }
    val snackbarHostState = remember { SnackbarHostState() }

    val historyItems = remember(historiesState) {
        val state = historiesState as? HistoriesState.Success ?: return@remember emptyList()
        state.data.raw_histories
            .filter { it.kitab != null }
            .sortedByDescending { parseToMillis(it.last_read_at) ?: 0L }
    }

    LaunchedEffect(toastMessage) {
        toastMessage?.let { message ->
            snackbarHostState.showSnackbar(message)
            viewModel.clearToastMessage()
        }
    }

    LaunchedEffect(Unit) {
        viewModel.continueReadingEvent.collectLatest { event ->
            when (event) {
                is ContinueReadingNavigationEvent.OpenPdf -> {
                    val encoded = URLEncoder.encode(
                        "${event.filePath}|${event.initialPage}",
                        "UTF-8"
                    )
                    navController.navigate("${AppScreen.PdfViewer.route}/${event.kitabId}/$encoded")
                }

                is ContinueReadingNavigationEvent.OpenKitabDetail -> {
                    navController.navigate("${AppScreen.KitabDetail.route}/${event.kitabId}")
                }
            }
        }
    }

    Scaffold(
        snackbarHost = { SnackbarHost(snackbarHostState) },
        containerColor = HistoryBackground
    ) { paddingValues ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(paddingValues)
        ) {
            HistoryTopHeader(
                total = historyItems.size,
                onClearAllClick = { showClearDialog = true }
            )

            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 16.dp, vertical = 8.dp),
                horizontalArrangement = Arrangement.End
            ) {
                SyncStatusChip(state = syncSummary.history)
            }

            Box(modifier = Modifier.weight(1f)) {
                when (val state = historiesState) {
                    is HistoriesState.Loading -> {
                        Box(
                            modifier = Modifier.fillMaxSize(),
                            contentAlignment = Alignment.Center
                        ) {
                            CircularProgressIndicator(color = HistoryHeaderStart)
                        }
                    }

                    is HistoriesState.Error -> {
                        HistoryErrorState(
                            message = state.message,
                            onRetry = { viewModel.loadHistories() }
                        )
                    }

                    is HistoriesState.Success -> {
                        if (historyItems.isEmpty()) {
                            EmptyHistoryState(
                                onNavigateToCatalog = {
                                    navController.navigate(AppScreen.Katalog.route)
                                }
                            )
                        } else {
                            HistoryListContent(
                                items = historyItems,
                                onBookClick = { kitabId ->
                                    navController.navigate("${AppScreen.KitabDetail.route}/$kitabId")
                                },
                                onContinueRead = { item ->
                                    viewModel.continueReading(item)
                                }
                            )
                        }
                    }
                }
            }
        }
    }

    if (showClearDialog) {
        AlertDialog(
            onDismissRequest = { showClearDialog = false },
            title = {
                Row(
                    horizontalArrangement = Arrangement.spacedBy(10.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Box(
                        modifier = Modifier
                            .size(40.dp)
                            .clip(RoundedCornerShape(12.dp))
                            .background(Color(0xFFFFF3E0)),
                        contentAlignment = Alignment.Center
                    ) {
                        Icon(
                            imageVector = Icons.Default.WarningAmber,
                            contentDescription = null,
                            tint = Color(0xFFF57C00),
                            modifier = Modifier.size(20.dp)
                        )
                    }
                    Column {
                        Text(
                            text = "Hapus Riwayat?",
                            fontSize = 16.sp,
                            color = HistoryPrimaryText,
                            fontWeight = FontWeight.ExtraBold
                        )
                        Text(
                            text = "Semua riwayat baca akan dihapus",
                            fontSize = 13.sp,
                            color = HistoryMutedText
                        )
                    }
                }
            },
            text = {},
            confirmButton = {
                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.spacedBy(8.dp)
                ) {
                    TextButton(
                        onClick = { showClearDialog = false },
                        modifier = Modifier
                            .weight(1f)
                            .border(1.dp, Color(0xFFE8E3D5), RoundedCornerShape(12.dp)),
                        shape = RoundedCornerShape(12.dp)
                    ) {
                        Text("Batal", color = HistorySecondaryText)
                    }
                    Button(
                        onClick = {
                            showClearDialog = false
                            viewModel.clearAllHistory()
                        },
                        colors = ButtonDefaults.buttonColors(containerColor = Color(0xFFE53935)),
                        shape = RoundedCornerShape(12.dp),
                        modifier = Modifier.weight(1f)
                    ) {
                        Text("Hapus Semua", color = Color.White)
                    }
                }
            },
            dismissButton = {},
            containerColor = Color.White,
            shape = RoundedCornerShape(24.dp)
        )
    }
}

@Composable
private fun HistoryTopHeader(
    total: Int,
    onClearAllClick: () -> Unit
) {
    Column(
        modifier = Modifier
            .fillMaxWidth()
            .background(Brush.linearGradient(colors = listOf(HistoryHeaderStart, HistoryHeaderEnd)))
            .padding(start = 20.dp, end = 20.dp, top = 24.dp, bottom = 18.dp)
    ) {
        Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            Column {
                Row(
                    horizontalArrangement = Arrangement.spacedBy(8.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Icon(
                        imageVector = Icons.Default.Schedule,
                        contentDescription = null,
                        tint = HistoryGold,
                        modifier = Modifier.size(18.dp)
                    )
                    Text(
                        text = "Riwayat Baca",
                        color = Color.White,
                        fontSize = 22.sp,
                        fontWeight = FontWeight.ExtraBold
                    )
                }
                Text(
                    text = "$total kitab pernah dibaca",
                    fontSize = 13.sp,
                    color = Color.White.copy(alpha = 0.72f),
                    modifier = Modifier.padding(top = 4.dp)
                )
            }

            if (total > 0) {
                Row(
                    modifier = Modifier
                        .clip(RoundedCornerShape(10.dp))
                        .background(Color(0xFFE53935).copy(alpha = 0.18f))
                        .border(1.dp, Color(0xFFE53935).copy(alpha = 0.3f), RoundedCornerShape(10.dp))
                        .clickable(onClick = onClearAllClick)
                        .padding(horizontal = 10.dp, vertical = 8.dp),
                    horizontalArrangement = Arrangement.spacedBy(4.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Icon(
                        imageVector = Icons.Default.Delete,
                        contentDescription = null,
                        tint = Color(0xFFEF9A9A),
                        modifier = Modifier.size(14.dp)
                    )
                    Text(
                        text = "Hapus",
                        fontSize = 12.sp,
                        color = Color(0xFFEF9A9A),
                        fontWeight = FontWeight.SemiBold
                    )
                }
            }
        }
    }
}

@Composable
private fun HistoryListContent(
    items: List<HistoryItemData>,
    onBookClick: (Int) -> Unit,
    onContinueRead: (HistoryItemData) -> Unit
) {
    LazyColumn(
        modifier = Modifier.fillMaxSize(),
        contentPadding = PaddingValues(start = 20.dp, end = 20.dp, top = 10.dp, bottom = 24.dp),
        verticalArrangement = Arrangement.spacedBy(10.dp)
    ) {
        items(items, key = { it.id }) { item ->
            HistoryBookCard(
                item = item,
                onBookClick = { onBookClick(item.kitab_id) },
                onContinueRead = { onContinueRead(item) }
            )
        }

        item {
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(top = 4.dp)
                    .clip(RoundedCornerShape(16.dp))
                    .background(Color(0xFFFFF8E1))
                    .border(1.dp, Color(0xFFFFE082), RoundedCornerShape(16.dp))
                    .padding(horizontal = 12.dp, vertical = 12.dp)
            ) {
                Text(
                    text = "💡 Riwayat bacamu tersimpan secara lokal di perangkat ini",
                    fontSize = 12.sp,
                    color = HistorySecondaryText,
                    lineHeight = 18.sp,
                    textAlign = TextAlign.Center,
                    modifier = Modifier.fillMaxWidth()
                )
            }
        }
    }
}

@Composable
private fun HistoryBookCard(
    item: HistoryItemData,
    onBookClick: () -> Unit,
    onContinueRead: () -> Unit
) {
    val kitab = item.kitab ?: return
    val progress = calculateProgress(item.current_page, item.total_pages)
    val pageRead = item.current_page.coerceAtLeast(1)
    val timeLabel = formatRelativeTime(item.last_read_at, item.time_ago)

    Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 0.dp)
    ) {
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .border(1.dp, HistoryCardBorder, RoundedCornerShape(16.dp))
                .padding(12.dp)
        ) {
            Row(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                Box(
                    modifier = Modifier
                        .width(64.dp)
                        .height(86.dp)
                        .clip(RoundedCornerShape(10.dp))
                        .clickable(onClick = onBookClick)
                ) {
                    AsyncImage(
                        model = ApiConfig.getCoverUrl(kitab.cover),
                        contentDescription = kitab.judul,
                        contentScale = ContentScale.Crop,
                        modifier = Modifier.fillMaxSize()
                    )

                    Box(
                        modifier = Modifier
                            .align(Alignment.BottomCenter)
                            .fillMaxWidth()
                            .height(6.dp)
                            .background(Color(0xFFE8E3D5))
                    ) {
                        Box(
                            modifier = Modifier
                                .fillMaxWidth(progress / 100f)
                                .height(6.dp)
                                .background(
                                    Brush.horizontalGradient(
                                        colors = listOf(HistoryHeaderStart, HistoryGold)
                                    )
                                )
                        )
                    }
                }

                Column(modifier = Modifier.weight(1f)) {
                    Column(
                        modifier = Modifier
                            .fillMaxWidth()
                            .clickable(onClick = onBookClick)
                    ) {
                        Text(
                            text = kitab.kategori,
                            fontSize = 11.sp,
                            color = HistoryGold,
                            fontWeight = FontWeight.SemiBold
                        )
                        Text(
                            text = kitab.judul,
                            fontSize = 15.sp,
                            color = HistoryPrimaryText,
                            fontWeight = FontWeight.Bold,
                            maxLines = 1,
                            overflow = TextOverflow.Ellipsis
                        )
                        Text(
                            text = kitab.penulis,
                            fontSize = 12.sp,
                            color = HistorySecondaryText,
                            maxLines = 1,
                            overflow = TextOverflow.Ellipsis
                        )
                    }

                    Row(
                        modifier = Modifier.padding(top = 6.dp),
                        horizontalArrangement = Arrangement.spacedBy(4.dp),
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Icon(
                            imageVector = Icons.Default.Schedule,
                            contentDescription = null,
                            tint = HistoryMutedText,
                            modifier = Modifier.size(11.dp)
                        )
                        Text(
                            text = timeLabel,
                            fontSize = 11.sp,
                            color = HistoryMutedText
                        )
                    }

                    Column(modifier = Modifier.padding(top = 8.dp)) {
                        Row(
                            modifier = Modifier.fillMaxWidth(),
                            horizontalArrangement = Arrangement.SpaceBetween
                        ) {
                            Text(
                                text = "Halaman $pageRead",
                                fontSize = 10.sp,
                                color = HistoryMutedText
                            )
                            Text(
                                text = "$progress%",
                                fontSize = 10.sp,
                                color = HistoryHeaderStart,
                                fontWeight = FontWeight.Bold
                            )
                        }

                        Box(
                            modifier = Modifier
                                .fillMaxWidth()
                                .padding(top = 4.dp)
                                .height(6.dp)
                                .clip(RoundedCornerShape(999.dp))
                                .background(Color(0xFFE8E3D5))
                        ) {
                            Box(
                                modifier = Modifier
                                    .fillMaxWidth(progress / 100f)
                                    .height(6.dp)
                                    .clip(RoundedCornerShape(999.dp))
                                    .background(
                                        Brush.horizontalGradient(
                                            colors = listOf(HistoryHeaderStart, HistoryGold)
                                        )
                                    )
                            )
                        }
                    }

                    Row(
                        modifier = Modifier
                            .padding(top = 10.dp)
                            .clip(RoundedCornerShape(10.dp))
                            .background(Color(0xFFF0F7F3))
                            .border(1.dp, Color(0xFFC8E6C9), RoundedCornerShape(10.dp))
                            .clickable(onClick = onContinueRead)
                            .padding(horizontal = 10.dp, vertical = 7.dp),
                        horizontalArrangement = Arrangement.spacedBy(4.dp),
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Icon(
                            imageVector = Icons.AutoMirrored.Filled.MenuBook,
                            contentDescription = null,
                            tint = HistoryHeaderStart,
                            modifier = Modifier.size(12.dp)
                        )
                        Text(
                            text = if (progress >= 100) "Baca Ulang" else "Lanjut Baca",
                            fontSize = 12.sp,
                            color = HistoryHeaderStart,
                            fontWeight = FontWeight.Bold
                        )
                        Icon(
                            imageVector = Icons.AutoMirrored.Filled.ArrowForward,
                            contentDescription = null,
                            tint = HistoryHeaderStart,
                            modifier = Modifier.size(12.dp)
                        )
                    }
                }
            }
        }
    }
}

@Composable
private fun EmptyHistoryState(onNavigateToCatalog: () -> Unit) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(horizontal = 24.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Box(
            modifier = Modifier
                .size(80.dp)
                .clip(RoundedCornerShape(24.dp))
                .background(Color(0xFFF0F7F3)),
            contentAlignment = Alignment.Center
        ) {
            Icon(
                imageVector = Icons.Default.Schedule,
                contentDescription = null,
                tint = HistoryMutedText,
                modifier = Modifier.size(36.dp)
            )
        }

        Text(
            text = "Belum Ada Riwayat",
            fontSize = 17.sp,
            color = HistoryPrimaryText,
            fontWeight = FontWeight.ExtraBold,
            modifier = Modifier.padding(top = 16.dp)
        )
        Text(
            text = "Mulai membaca kitab untuk melihat riwayat bacamu di sini",
            fontSize = 13.sp,
            color = HistoryMutedText,
            textAlign = TextAlign.Center,
            lineHeight = 19.sp,
            modifier = Modifier.padding(top = 6.dp)
        )

        Row(
            modifier = Modifier
                .padding(top = 18.dp)
                .clip(RoundedCornerShape(16.dp))
                .background(HistoryHeaderStart)
                .clickable(onClick = onNavigateToCatalog)
                .padding(horizontal = 20.dp, vertical = 12.dp),
            horizontalArrangement = Arrangement.spacedBy(8.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Icon(
                imageVector = Icons.AutoMirrored.Filled.MenuBook,
                contentDescription = null,
                tint = Color.White,
                modifier = Modifier.size(16.dp)
            )
            Text(
                text = "Jelajahi Kitab",
                fontSize = 14.sp,
                fontWeight = FontWeight.Bold,
                color = Color.White
            )
        }
    }
}

@Composable
private fun HistoryErrorState(
    message: String,
    onRetry: () -> Unit
) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(horizontal = 24.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Text(text = "⚠️", fontSize = 44.sp)
        Text(
            text = "Terjadi Kesalahan",
            fontSize = 16.sp,
            color = HistoryPrimaryText,
            fontWeight = FontWeight.Bold,
            modifier = Modifier.padding(top = 8.dp)
        )
        Text(
            text = message,
            fontSize = 13.sp,
            color = HistoryMutedText,
            textAlign = TextAlign.Center,
            modifier = Modifier.padding(top = 6.dp)
        )

        Button(
            onClick = onRetry,
            colors = ButtonDefaults.buttonColors(containerColor = HistoryHeaderStart),
            shape = RoundedCornerShape(12.dp),
            modifier = Modifier.padding(top = 12.dp)
        ) {
            Text("Coba Lagi", color = Color.White)
        }
    }
}

private fun calculateProgress(currentPage: Int, totalPages: Int): Int {
    if (totalPages <= 0) return 0
    return ((currentPage.toFloat() / totalPages.toFloat()) * 100f)
        .roundToInt()
        .coerceIn(0, 100)
}

private fun formatRelativeTime(lastReadAt: String, fallback: String): String {
    val eventMillis = parseToMillis(lastReadAt)
    if (eventMillis == null) {
        return normalizeTimeAgo(fallback.ifBlank { "Baru saja" })
    }

    val diffSeconds = ((System.currentTimeMillis() - eventMillis) / 1000).coerceAtLeast(0)
    return when {
        diffSeconds < 3600 -> {
            val minutes = (diffSeconds / 60).coerceAtLeast(1)
            "$minutes menit lalu"
        }

        diffSeconds < 86400 -> {
            val hours = (diffSeconds / 3600).coerceAtLeast(1)
            "$hours jam lalu"
        }

        diffSeconds < 172800 -> "Kemarin"
        else -> SimpleDateFormat("d MMM yyyy", Locale("id", "ID")).format(Date(eventMillis))
    }
}

private fun parseToMillis(value: String): Long? {
    if (value.isBlank()) return null

    val patterns = listOf(
        "yyyy-MM-dd'T'HH:mm:ss.SSSSSS'Z'",
        "yyyy-MM-dd'T'HH:mm:ss.SSS'Z'",
        "yyyy-MM-dd'T'HH:mm:ss'Z'",
        "yyyy-MM-dd'T'HH:mm:ssXXX",
        "yyyy-MM-dd HH:mm:ss"
    )

    for (pattern in patterns) {
        try {
            val formatter = SimpleDateFormat(pattern, Locale.US).apply {
                isLenient = false
                if (pattern.contains("Z") || pattern.contains("XXX")) {
                    timeZone = java.util.TimeZone.getTimeZone("UTC")
                }
            }
            val parsed = formatter.parse(value)
            if (parsed != null) {
                return parsed.time
            }
        } catch (_: Exception) {
            // Try next parser
        }
    }

    return null
}

private fun normalizeTimeAgo(timeAgo: String): String {
    return timeAgo
        .replace("ago", "lalu", ignoreCase = true)
        .replace("Today", "Hari Ini", ignoreCase = true)
        .replace("Yesterday", "Kemarin", ignoreCase = true)
        .replace("minutes", "menit", ignoreCase = true)
        .replace("minute", "menit", ignoreCase = true)
        .replace("hours", "jam", ignoreCase = true)
        .replace("hour", "jam", ignoreCase = true)
}
