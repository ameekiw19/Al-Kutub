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
import androidx.compose.foundation.text.BasicTextField
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.List
import androidx.compose.material.icons.automirrored.filled.MenuBook
import androidx.compose.material.icons.filled.Bookmark
import androidx.compose.material.icons.filled.Delete
import androidx.compose.material.icons.filled.GridView
import androidx.compose.material.icons.filled.Search
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
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.TextStyle
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.hilt.navigation.compose.hiltViewModel
import coil.compose.AsyncImage
import com.example.al_kutub.api.ApiConfig
import com.example.al_kutub.model.BookmarkItem
import com.example.al_kutub.model.Kitab
import com.example.al_kutub.ui.components.SyncStatusChip
import com.example.al_kutub.ui.viewmodel.BookmarkUiState
import com.example.al_kutub.ui.viewmodel.BookmarkViewModel
import com.example.al_kutub.ui.viewmodel.RecommendedKitabUiState

enum class BookmarkViewMode {
    Grid,
    List
}

private data class BookmarkedKitabUi(
    val idKitab: Int,
    val judul: String,
    val penulis: String,
    val kategori: String,
    val cover: String,
    val createdAt: String
)

private val BookmarkBackground = Color(0xFFFAFAF5)
private val BookmarkHeaderStart = Color(0xFF1B5E3B)
private val BookmarkHeaderEnd = Color(0xFF2D7A52)
private val BookmarkGold = Color(0xFFC8A951)
private val BookmarkCardBorder = Color(0xFFF0EBE0)
private val BookmarkPrimaryText = Color(0xFF1A2E1A)
private val BookmarkSecondaryText = Color(0xFF6B5E4E)
private val BookmarkMutedText = Color(0xFF8B8070)

@Composable
fun BookmarkScreen(
    viewModel: BookmarkViewModel = hiltViewModel(),
    onKitabClick: (Int) -> Unit,
    onNavigateToCatalog: () -> Unit
) {
    val bookmarksState by viewModel.bookmarksState.collectAsState()
    val recommendedState by viewModel.recommendedKitabState.collectAsState()
    val messageState by viewModel.messageState.collectAsState()
    val syncSummary by viewModel.syncSummary.collectAsState()

    var query by rememberSaveable { mutableStateOf("") }
    var viewMode by rememberSaveable { mutableStateOf(BookmarkViewMode.Grid) }
    var showClearAllDialog by remember { mutableStateOf(false) }

    val snackbarHostState = remember { SnackbarHostState() }

    LaunchedEffect(messageState) {
        messageState?.let { message ->
            snackbarHostState.showSnackbar(message)
            viewModel.clearMessage()
        }
    }

    val normalizedBookmarks = remember(bookmarksState) {
        val state = bookmarksState as? BookmarkUiState.Success ?: return@remember emptyList()
        state.bookmarks
            .groupBy { it.idKitab }
            .mapNotNull { (_, group) ->
                val latest = group.maxByOrNull { it.createdAt } ?: return@mapNotNull null
                val kitab = latest.kitab ?: return@mapNotNull null
                BookmarkedKitabUi(
                    idKitab = kitab.idKitab,
                    judul = kitab.judul,
                    penulis = kitab.penulis,
                    kategori = kitab.kategori,
                    cover = kitab.cover,
                    createdAt = latest.createdAt
                )
            }
            .sortedByDescending { it.createdAt }
    }

    val filteredBookmarks = remember(normalizedBookmarks, query) {
        if (query.isBlank()) {
            normalizedBookmarks
        } else {
            val keyword = query.trim().lowercase()
            normalizedBookmarks.filter { item ->
                item.judul.lowercase().contains(keyword) ||
                    item.penulis.lowercase().contains(keyword)
            }
        }
    }

    Scaffold(
        snackbarHost = { SnackbarHost(snackbarHostState) },
        containerColor = BookmarkBackground
    ) { paddingValues ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(paddingValues)
        ) {
            BookmarkTopHeader(
                total = normalizedBookmarks.size,
                query = query,
                onQueryChange = { query = it },
                onClearQuery = { query = "" },
                onClearAllClick = { showClearAllDialog = true }
            )

            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 16.dp, vertical = 8.dp),
                horizontalArrangement = Arrangement.End
            ) {
                SyncStatusChip(state = syncSummary.bookmark)
            }

            Box(modifier = Modifier.weight(1f)) {
                when (val state = bookmarksState) {
                    is BookmarkUiState.Loading -> {
                        Box(
                            modifier = Modifier.fillMaxSize(),
                            contentAlignment = Alignment.Center
                        ) {
                            CircularProgressIndicator(color = BookmarkHeaderStart)
                        }
                    }

                    is BookmarkUiState.Error -> {
                        BookmarkErrorState(
                            message = state.message,
                            onRetry = {
                                viewModel.loadBookmarks()
                                viewModel.loadRecommendedKitab()
                            }
                        )
                    }

                    is BookmarkUiState.Success -> {
                        if (normalizedBookmarks.isEmpty()) {
                            EmptyBookmarkState(
                                recommendedState = recommendedState,
                                onNavigateToCatalog = onNavigateToCatalog,
                                onKitabClick = onKitabClick
                            )
                        } else {
                            BookmarkListContent(
                                bookmarks = filteredBookmarks,
                                viewMode = viewMode,
                                onChangeViewMode = { viewMode = it },
                                query = query,
                                onResetQuery = { query = "" },
                                onKitabClick = onKitabClick,
                                onRemoveBookmark = { kitabId -> viewModel.deleteBookmark(kitabId) }
                            )
                        }
                    }
                }
            }
        }
    }

    if (showClearAllDialog) {
        AlertDialog(
            onDismissRequest = { showClearAllDialog = false },
            title = {
                Text(
                    text = "Hapus Semua Simpanan?",
                    fontWeight = FontWeight.Bold
                )
            },
            text = {
                Text(
                    text = "Semua kitab tersimpan akan dihapus dari daftar bookmark Anda."
                )
            },
            confirmButton = {
                Button(
                    onClick = {
                        showClearAllDialog = false
                        viewModel.clearAllBookmarks()
                    },
                    colors = ButtonDefaults.buttonColors(containerColor = Color(0xFFE53935)),
                    shape = RoundedCornerShape(12.dp)
                ) {
                    Text("Hapus Semua", color = Color.White)
                }
            },
            dismissButton = {
                TextButton(onClick = { showClearAllDialog = false }) {
                    Text("Batal", color = BookmarkSecondaryText)
                }
            },
            containerColor = Color.White,
            shape = RoundedCornerShape(24.dp)
        )
    }
}

@Composable
private fun BookmarkTopHeader(
    total: Int,
    query: String,
    onQueryChange: (String) -> Unit,
    onClearQuery: () -> Unit,
    onClearAllClick: () -> Unit
) {
    Column(
        modifier = Modifier
            .fillMaxWidth()
            .background(
                Brush.linearGradient(
                    colors = listOf(BookmarkHeaderStart, BookmarkHeaderEnd)
                )
            )
            .padding(start = 20.dp, end = 20.dp, top = 24.dp, bottom = 18.dp)
    ) {
        Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            Row(
                horizontalArrangement = Arrangement.spacedBy(8.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                Icon(
                    imageVector = Icons.Default.Bookmark,
                    contentDescription = null,
                    tint = BookmarkGold,
                    modifier = Modifier.size(18.dp)
                )
                Text(
                    text = "Kitab Tersimpan",
                    fontSize = 22.sp,
                    fontWeight = FontWeight.ExtraBold,
                    color = Color.White
                )
            }

            if (total > 0) {
                IconButton(
                    onClick = onClearAllClick,
                    modifier = Modifier
                        .size(38.dp)
                        .clip(RoundedCornerShape(12.dp))
                        .background(Color(0xFFE53935).copy(alpha = 0.2f))
                ) {
                    Icon(
                        imageVector = Icons.Default.Delete,
                        contentDescription = "Hapus Semua",
                        tint = Color(0xFFEF9A9A),
                        modifier = Modifier.size(16.dp)
                    )
                }
            }
        }

        Text(
            text = "$total kitab disimpan",
            fontSize = 13.sp,
            color = Color.White.copy(alpha = 0.72f),
            modifier = Modifier.padding(top = 4.dp)
        )

        if (total > 0) {
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(top = 14.dp)
                    .clip(RoundedCornerShape(16.dp))
                    .background(Color.White.copy(alpha = 0.12f))
                    .border(1.dp, Color.White.copy(alpha = 0.15f), RoundedCornerShape(16.dp))
                    .padding(horizontal = 14.dp, vertical = 12.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                Icon(
                    imageVector = Icons.Default.Search,
                    contentDescription = null,
                    tint = Color.White.copy(alpha = 0.62f),
                    modifier = Modifier.size(16.dp)
                )

                Box(
                    modifier = Modifier
                        .weight(1f)
                        .padding(start = 8.dp),
                    contentAlignment = Alignment.CenterStart
                ) {
                    if (query.isBlank()) {
                        Text(
                            text = "Cari dari simpanan...",
                            fontSize = 14.sp,
                            color = Color.White.copy(alpha = 0.62f)
                        )
                    }
                    BasicTextField(
                        value = query,
                        onValueChange = onQueryChange,
                        singleLine = true,
                        textStyle = TextStyle(
                            color = Color.White,
                            fontSize = 14.sp
                        ),
                        modifier = Modifier.fillMaxWidth()
                    )
                }

                if (query.isNotBlank()) {
                    Text(
                        text = "×",
                        color = Color.White.copy(alpha = 0.62f),
                        fontSize = 18.sp,
                        modifier = Modifier.clickable(onClick = onClearQuery)
                    )
                }
            }
        }
    }
}

@Composable
private fun BookmarkListContent(
    bookmarks: List<BookmarkedKitabUi>,
    viewMode: BookmarkViewMode,
    onChangeViewMode: (BookmarkViewMode) -> Unit,
    query: String,
    onResetQuery: () -> Unit,
    onKitabClick: (Int) -> Unit,
    onRemoveBookmark: (Int) -> Unit
) {
    LazyColumn(
        modifier = Modifier.fillMaxSize(),
        contentPadding = PaddingValues(start = 20.dp, end = 20.dp, top = 12.dp, bottom = 24.dp),
        verticalArrangement = Arrangement.spacedBy(10.dp)
    ) {
        item {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = "${bookmarks.size} kitab",
                    fontSize = 13.sp,
                    color = BookmarkMutedText
                )

                Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                    BookmarkViewModeButton(
                        active = viewMode == BookmarkViewMode.List,
                        icon = {
                            Icon(
                                imageVector = Icons.AutoMirrored.Filled.List,
                                contentDescription = null,
                                tint = if (viewMode == BookmarkViewMode.List) Color.White else BookmarkMutedText,
                                modifier = Modifier.size(16.dp)
                            )
                        },
                        onClick = { onChangeViewMode(BookmarkViewMode.List) }
                    )
                    BookmarkViewModeButton(
                        active = viewMode == BookmarkViewMode.Grid,
                        icon = {
                            Icon(
                                imageVector = Icons.Default.GridView,
                                contentDescription = null,
                                tint = if (viewMode == BookmarkViewMode.Grid) Color.White else BookmarkMutedText,
                                modifier = Modifier.size(16.dp)
                            )
                        },
                        onClick = { onChangeViewMode(BookmarkViewMode.Grid) }
                    )
                }
            }
        }

        if (bookmarks.isEmpty()) {
            item {
                Column(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(vertical = 44.dp),
                    horizontalAlignment = Alignment.CenterHorizontally
                ) {
                    Text(text = "🔍", fontSize = 40.sp)
                    Text(
                        text = "Kitab tidak ditemukan",
                        fontSize = 15.sp,
                        fontWeight = FontWeight.Bold,
                        color = Color(0xFF3D2C1E),
                        modifier = Modifier.padding(top = 8.dp)
                    )
                    Text(
                        text = "Reset",
                        fontSize = 13.sp,
                        color = Color.White,
                        modifier = Modifier
                            .padding(top = 10.dp)
                            .clip(RoundedCornerShape(10.dp))
                            .background(BookmarkHeaderStart)
                            .clickable(onClick = onResetQuery)
                            .padding(horizontal = 16.dp, vertical = 8.dp)
                    )
                }
            }
        } else if (viewMode == BookmarkViewMode.Grid) {
            items(bookmarks.chunked(2)) { rowItems ->
                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.spacedBy(12.dp)
                ) {
                    rowItems.forEach { item ->
                        BookmarkGridCard(
                            item = item,
                            modifier = Modifier.weight(1f),
                            onClick = { onKitabClick(item.idKitab) },
                            onRemove = { onRemoveBookmark(item.idKitab) }
                        )
                    }

                    repeat(2 - rowItems.size) {
                        Spacer(modifier = Modifier.weight(1f))
                    }
                }
            }
        } else {
            items(bookmarks, key = { it.idKitab }) { item ->
                BookmarkListCard(
                    item = item,
                    onClick = { onKitabClick(item.idKitab) },
                    onRemove = { onRemoveBookmark(item.idKitab) }
                )
            }
        }

        item {
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(top = 6.dp)
                    .clip(RoundedCornerShape(16.dp))
                    .background(Color(0xFFFFF8E1))
                    .border(1.dp, Color(0xFFFFE082), RoundedCornerShape(16.dp))
                    .padding(horizontal = 12.dp, vertical = 12.dp)
            ) {
                Text(
                    text = "💡 Tekan ikon bookmark pada kartu kitab untuk menambah atau menghapus dari simpanan",
                    fontSize = 12.sp,
                    color = BookmarkSecondaryText,
                    lineHeight = 18.sp,
                    textAlign = TextAlign.Center,
                    modifier = Modifier.fillMaxWidth()
                )
            }
        }
    }
}

@Composable
private fun BookmarkViewModeButton(
    active: Boolean,
    icon: @Composable () -> Unit,
    onClick: () -> Unit
) {
    Box(
        modifier = Modifier
            .clip(RoundedCornerShape(10.dp))
            .background(if (active) BookmarkHeaderStart else Color(0xFFF0EBE0))
            .clickable(onClick = onClick)
            .padding(8.dp),
        contentAlignment = Alignment.Center
    ) {
        icon()
    }
}

@Composable
private fun BookmarkGridCard(
    item: BookmarkedKitabUi,
    modifier: Modifier = Modifier,
    onClick: () -> Unit,
    onRemove: () -> Unit
) {
    Card(
        modifier = modifier,
        shape = RoundedCornerShape(14.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 0.dp),
        onClick = onClick
    ) {
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .border(1.dp, BookmarkCardBorder, RoundedCornerShape(14.dp))
                .padding(10.dp)
        ) {
            Box {
                AsyncImage(
                    model = ApiConfig.getCoverUrl(item.cover),
                    contentDescription = item.judul,
                    contentScale = ContentScale.Crop,
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(150.dp)
                        .clip(RoundedCornerShape(10.dp))
                )

                Box(
                    modifier = Modifier
                        .align(Alignment.TopEnd)
                        .padding(6.dp)
                        .size(28.dp)
                        .clip(RoundedCornerShape(8.dp))
                        .background(Color.White.copy(alpha = 0.9f))
                        .clickable(onClick = onRemove),
                    contentAlignment = Alignment.Center
                ) {
                    Icon(
                        imageVector = Icons.Default.Bookmark,
                        contentDescription = "Hapus Bookmark",
                        tint = BookmarkHeaderStart,
                        modifier = Modifier.size(16.dp)
                    )
                }
            }

            Text(
                text = item.judul,
                fontSize = 13.sp,
                fontWeight = FontWeight.Bold,
                color = BookmarkPrimaryText,
                maxLines = 2,
                overflow = TextOverflow.Ellipsis,
                modifier = Modifier.padding(top = 8.dp)
            )
            Text(
                text = item.penulis,
                fontSize = 11.sp,
                color = BookmarkMutedText,
                maxLines = 1,
                overflow = TextOverflow.Ellipsis,
                modifier = Modifier.padding(top = 2.dp)
            )
            Text(
                text = item.kategori,
                fontSize = 10.sp,
                fontWeight = FontWeight.SemiBold,
                color = BookmarkHeaderStart,
                maxLines = 1,
                overflow = TextOverflow.Ellipsis,
                modifier = Modifier.padding(top = 6.dp)
            )
        }
    }
}

@Composable
private fun BookmarkListCard(
    item: BookmarkedKitabUi,
    onClick: () -> Unit,
    onRemove: () -> Unit
) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(14.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 0.dp),
        onClick = onClick
    ) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .border(1.dp, BookmarkCardBorder, RoundedCornerShape(14.dp))
                .padding(10.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            AsyncImage(
                model = ApiConfig.getCoverUrl(item.cover),
                contentDescription = item.judul,
                contentScale = ContentScale.Crop,
                modifier = Modifier
                    .width(62.dp)
                    .height(86.dp)
                    .clip(RoundedCornerShape(10.dp))
            )

            Column(
                modifier = Modifier
                    .weight(1f)
                    .padding(start = 10.dp)
            ) {
                Text(
                    text = item.judul,
                    fontSize = 14.sp,
                    lineHeight = 18.sp,
                    fontWeight = FontWeight.Bold,
                    color = BookmarkPrimaryText,
                    maxLines = 2,
                    overflow = TextOverflow.Ellipsis
                )
                Text(
                    text = item.penulis,
                    fontSize = 12.sp,
                    color = BookmarkMutedText,
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis,
                    modifier = Modifier.padding(top = 2.dp)
                )
                Text(
                    text = item.kategori,
                    fontSize = 11.sp,
                    fontWeight = FontWeight.SemiBold,
                    color = BookmarkHeaderStart,
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis,
                    modifier = Modifier.padding(top = 6.dp)
                )
            }

            Box(
                modifier = Modifier
                    .size(30.dp)
                    .clip(RoundedCornerShape(10.dp))
                    .background(Color(0xFFF0F7F3))
                    .clickable(onClick = onRemove),
                contentAlignment = Alignment.Center
            ) {
                Icon(
                    imageVector = Icons.Default.Bookmark,
                    contentDescription = "Hapus Bookmark",
                    tint = BookmarkHeaderStart,
                    modifier = Modifier.size(16.dp)
                )
            }
        }
    }
}

@Composable
private fun EmptyBookmarkState(
    recommendedState: RecommendedKitabUiState,
    onNavigateToCatalog: () -> Unit,
    onKitabClick: (Int) -> Unit
) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(horizontal = 20.dp)
    ) {
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .padding(top = 30.dp),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            Box(
                modifier = Modifier
                    .size(80.dp)
                    .clip(RoundedCornerShape(24.dp))
                    .background(Color(0xFFF0F7F3)),
                contentAlignment = Alignment.Center
            ) {
                Icon(
                    imageVector = Icons.Default.Bookmark,
                    contentDescription = null,
                    tint = BookmarkMutedText,
                    modifier = Modifier.size(36.dp)
                )
            }

            Text(
                text = "Belum Ada Simpanan",
                fontSize = 17.sp,
                fontWeight = FontWeight.ExtraBold,
                color = BookmarkPrimaryText,
                modifier = Modifier.padding(top = 16.dp)
            )
            Text(
                text = "Simpan kitab favoritmu agar mudah ditemukan kembali",
                fontSize = 13.sp,
                color = BookmarkMutedText,
                textAlign = TextAlign.Center,
                lineHeight = 19.sp,
                modifier = Modifier
                    .padding(top = 6.dp)
                    .width(240.dp)
            )

            Row(
                modifier = Modifier
                    .padding(top = 18.dp)
                    .clip(RoundedCornerShape(16.dp))
                    .background(BookmarkHeaderStart)
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
                    text = "Jelajahi Katalog",
                    fontSize = 14.sp,
                    fontWeight = FontWeight.Bold,
                    color = Color.White
                )
            }
        }

        Column(
            modifier = Modifier
                .fillMaxWidth()
                .padding(top = 26.dp),
            verticalArrangement = Arrangement.spacedBy(10.dp)
        ) {
            Text(
                text = "Rekomendasi untuk Disimpan",
                fontSize = 14.sp,
                fontWeight = FontWeight.Bold,
                color = BookmarkPrimaryText
            )

            when (recommendedState) {
                is RecommendedKitabUiState.Loading -> {
                    Box(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(vertical = 16.dp),
                        contentAlignment = Alignment.Center
                    ) {
                        CircularProgressIndicator(
                            color = BookmarkHeaderStart,
                            strokeWidth = 2.dp,
                            modifier = Modifier.size(22.dp)
                        )
                    }
                }

                is RecommendedKitabUiState.Error -> {
                    Text(
                        text = recommendedState.message,
                        fontSize = 12.sp,
                        color = BookmarkMutedText
                    )
                }

                is RecommendedKitabUiState.Success -> {
                    recommendedState.kitab.forEach { kitab ->
                        RecommendationBookCard(
                            kitab = kitab,
                            onClick = { onKitabClick(kitab.idKitab) }
                        )
                    }
                }
            }
        }
    }
}

@Composable
private fun RecommendationBookCard(
    kitab: Kitab,
    onClick: () -> Unit
) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(14.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 0.dp),
        onClick = onClick
    ) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .border(1.dp, BookmarkCardBorder, RoundedCornerShape(14.dp))
                .padding(10.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            AsyncImage(
                model = ApiConfig.getCoverUrl(kitab.cover),
                contentDescription = kitab.judul,
                contentScale = ContentScale.Crop,
                modifier = Modifier
                    .width(62.dp)
                    .height(86.dp)
                    .clip(RoundedCornerShape(10.dp))
            )

            Column(
                modifier = Modifier
                    .weight(1f)
                    .padding(start = 10.dp)
            ) {
                Text(
                    text = kitab.judul,
                    fontSize = 14.sp,
                    lineHeight = 18.sp,
                    fontWeight = FontWeight.Bold,
                    color = BookmarkPrimaryText,
                    maxLines = 2,
                    overflow = TextOverflow.Ellipsis
                )
                Text(
                    text = kitab.penulis,
                    fontSize = 12.sp,
                    color = BookmarkMutedText,
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis,
                    modifier = Modifier.padding(top = 2.dp)
                )
                Text(
                    text = kitab.kategori,
                    fontSize = 11.sp,
                    fontWeight = FontWeight.SemiBold,
                    color = BookmarkHeaderStart,
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis,
                    modifier = Modifier.padding(top = 6.dp)
                )
            }
        }
    }
}

@Composable
private fun BookmarkErrorState(
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
            fontWeight = FontWeight.Bold,
            color = BookmarkPrimaryText,
            modifier = Modifier.padding(top = 10.dp)
        )
        Text(
            text = message,
            fontSize = 13.sp,
            color = BookmarkMutedText,
            textAlign = TextAlign.Center,
            modifier = Modifier.padding(top = 6.dp)
        )
        Button(
            onClick = onRetry,
            colors = ButtonDefaults.buttonColors(containerColor = BookmarkHeaderStart),
            shape = RoundedCornerShape(12.dp),
            modifier = Modifier.padding(top = 14.dp)
        ) {
            Text("Coba Lagi", color = Color.White)
        }
    }
}
