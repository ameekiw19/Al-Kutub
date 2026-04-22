package com.example.al_kutub.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.horizontalScroll
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
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Apps
import androidx.compose.material.icons.automirrored.filled.List
import androidx.compose.material.icons.filled.Search
import androidx.compose.material.icons.filled.Tune
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
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
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.hilt.navigation.compose.hiltViewModel
import coil.compose.AsyncImage
import com.example.al_kutub.api.ApiConfig
import com.example.al_kutub.model.Kitab
import com.example.al_kutub.ui.viewmodel.KatalogUiState
import com.example.al_kutub.ui.viewmodel.KatalogViewModel

enum class CatalogViewMode {
    Grid,
    List
}

enum class CatalogSortOption(val key: String, val label: String) {
    Latest("latest", "Terbaru"),
    Views("views", "Terpopuler"),
    Rating("rating", "Rating"),
    Pages("pages", "Terpendek"),
    Title("title", "A-Z");

    companion object {
        fun fromKey(key: String): CatalogSortOption {
            return entries.firstOrNull { it.key == key } ?: Latest
        }
    }
}

private val CatalogBackground = Color(0xFFFAFAF5)
private val CatalogHeaderStart = Color(0xFF1B5E3B)
private val CatalogHeaderEnd = Color(0xFF2D7A52)
private val CatalogGold = Color(0xFFC8A951)
private val CatalogCardBorder = Color(0xFFE8E3D5)
private val CatalogPrimaryText = Color(0xFF1A2E1A)
private val CatalogSecondaryText = Color(0xFF6B5E4E)
private val CatalogMutedText = Color(0xFF8B8070)

@Composable
fun KatalogScreen(
    onKitabClick: (Int) -> Unit,
    onOpenSearch: (String?, String?, String?) -> Unit,
    initialCategory: String? = null,
    viewModel: KatalogViewModel = hiltViewModel()
) {
    val state by viewModel.uiState.collectAsState()
    val selectedCategory by viewModel.selectedCategory.collectAsState()
    val selectedLanguage by viewModel.selectedLanguage.collectAsState()
    val selectedSortKey by viewModel.selectedSort.collectAsState()

    var showFilter by rememberSaveable { mutableStateOf(false) }
    var viewMode by rememberSaveable { mutableStateOf(CatalogViewMode.List) }
    var sortBy by rememberSaveable { mutableStateOf(CatalogSortOption.fromKey(selectedSortKey)) }
    var hasAppliedInitialCategory by rememberSaveable(initialCategory) { mutableStateOf(false) }

    LaunchedEffect(selectedSortKey) {
        sortBy = CatalogSortOption.fromKey(selectedSortKey)
    }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(CatalogBackground)
    ) {
        CatalogHeader(
            totalCount = viewModel.getTotalKitab(),
            showFilter = showFilter,
            sortBy = sortBy,
            onOpenSearch = {
                onOpenSearch(
                    selectedCategory.takeUnless { it.equals("Semua", ignoreCase = true) },
                    selectedLanguage.takeUnless { it.equals("Semua", ignoreCase = true) },
                    selectedSortKey.takeUnless { it == KatalogViewModel.DEFAULT_SORT }
                )
            },
            onToggleFilter = { showFilter = !showFilter },
            onSortChange = {
                sortBy = it
                viewModel.selectSort(it.key)
            }
        )

        when (val uiState = state) {
            is KatalogUiState.Loading -> {
                Box(
                    modifier = Modifier.fillMaxSize(),
                    contentAlignment = Alignment.Center
                ) {
                    CircularProgressIndicator(color = CatalogHeaderStart)
                }
            }

            is KatalogUiState.Error -> {
                Box(
                    modifier = Modifier
                        .fillMaxSize()
                        .padding(20.dp),
                    contentAlignment = Alignment.Center
                ) {
                    Column(horizontalAlignment = Alignment.CenterHorizontally) {
                        Text(text = "📭", fontSize = 48.sp)
                        Text(
                            text = uiState.message,
                            fontSize = 14.sp,
                            color = MaterialTheme.colorScheme.error,
                            textAlign = androidx.compose.ui.text.style.TextAlign.Center,
                            modifier = Modifier.padding(top = 8.dp)
                        )
                        Text(
                            text = "Coba lagi",
                            fontSize = 13.sp,
                            color = Color.White,
                            fontWeight = FontWeight.SemiBold,
                            modifier = Modifier
                                .padding(top = 12.dp)
                                .clip(RoundedCornerShape(12.dp))
                                .background(CatalogHeaderStart)
                                .clickable { viewModel.retry() }
                                .padding(horizontal = 18.dp, vertical = 10.dp)
                        )
                    }
                }
            }

            is KatalogUiState.Success -> {
                val categories = remember(uiState.kategori) {
                    listOf("Semua") + uiState.kategori
                }
                val languageFilters = remember(uiState.bahasa) {
                    (listOf("Semua") + uiState.bahasa).distinct()
                }

                LaunchedEffect(initialCategory, categories, hasAppliedInitialCategory) {
                    if (hasAppliedInitialCategory || initialCategory.isNullOrBlank()) {
                        return@LaunchedEffect
                    }

                    val matchedCategory = categories.firstOrNull {
                        it.equals(initialCategory, ignoreCase = true)
                    }

                    if (matchedCategory != null) {
                        viewModel.selectCategory(matchedCategory)
                    }
                    hasAppliedInitialCategory = true
                }

                CatalogContent(
                    categories = categories,
                    selectedCategory = selectedCategory,
                    languageFilters = languageFilters,
                    selectedLanguage = selectedLanguage,
                    books = uiState.kitab,
                    viewMode = viewMode,
                    totalCount = uiState.kitab.size,
                    onSelectCategory = viewModel::selectCategory,
                    onSelectLanguage = viewModel::selectLanguage,
                    onBookClick = onKitabClick,
                    onChangeViewMode = { viewMode = it },
                    onResetFilter = {
                        viewModel.resetFilters()
                        sortBy = CatalogSortOption.fromKey(KatalogViewModel.DEFAULT_SORT)
                        showFilter = false
                    }
                )
            }
        }
    }
}

@Composable
private fun CatalogHeader(
    totalCount: Int,
    showFilter: Boolean,
    sortBy: CatalogSortOption,
    onOpenSearch: () -> Unit,
    onToggleFilter: () -> Unit,
    onSortChange: (CatalogSortOption) -> Unit
) {
    Column(
        modifier = Modifier
            .fillMaxWidth()
            .background(
                Brush.linearGradient(colors = listOf(CatalogHeaderStart, CatalogHeaderEnd))
            )
            .padding(start = 20.dp, end = 20.dp, top = 24.dp, bottom = 18.dp)
    ) {
        Text(
            text = "Katalog Kitab",
            fontSize = 22.sp,
            fontWeight = FontWeight.ExtraBold,
            color = Color.White
        )
        Text(
            text = "$totalCount kitab tersedia",
            fontSize = 13.sp,
            color = Color.White.copy(alpha = 0.7f),
            modifier = Modifier.padding(top = 4.dp)
        )

        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(top = 14.dp),
            horizontalArrangement = Arrangement.spacedBy(8.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Row(
                modifier = Modifier
                    .weight(1f)
                    .clip(RoundedCornerShape(16.dp))
                    .background(Color.White.copy(alpha = 0.12f))
                    .border(1.dp, Color.White.copy(alpha = 0.15f), RoundedCornerShape(16.dp))
                    .clickable(onClick = onOpenSearch)
                    .padding(horizontal = 12.dp, vertical = 12.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                Icon(
                    imageVector = Icons.Default.Search,
                    contentDescription = null,
                    tint = Color.White.copy(alpha = 0.62f),
                    modifier = Modifier.size(16.dp)
                )

                Text(
                    text = "Buka pencarian lengkap...",
                    fontSize = 14.sp,
                    color = Color.White.copy(alpha = 0.62f),
                    modifier = Modifier
                        .weight(1f)
                        .padding(start = 8.dp)
                )
            }

            Box(
                modifier = Modifier
                    .size(width = 48.dp, height = 44.dp)
                    .clip(RoundedCornerShape(16.dp))
                    .background(if (showFilter) CatalogGold else Color.White.copy(alpha = 0.12f))
                    .border(1.dp, Color.White.copy(alpha = 0.15f), RoundedCornerShape(16.dp))
                    .clickable(onClick = onToggleFilter),
                contentAlignment = Alignment.Center
            ) {
                Icon(
                    imageVector = Icons.Default.Tune,
                    contentDescription = "Filter",
                    tint = if (showFilter) Color.White else Color.White.copy(alpha = 0.72f),
                    modifier = Modifier.size(18.dp)
                )
            }
        }

        if (showFilter) {
            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(top = 10.dp)
                    .clip(RoundedCornerShape(16.dp))
                    .background(Color.White.copy(alpha = 0.1f))
                    .padding(12.dp)
            ) {
                Text(
                    text = "URUTKAN",
                    fontSize = 12.sp,
                    fontWeight = FontWeight.SemiBold,
                    color = Color.White.copy(alpha = 0.7f)
                )

                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(top = 8.dp)
                        .horizontalScroll(rememberScrollState()),
                    horizontalArrangement = Arrangement.spacedBy(8.dp)
                ) {
                    CatalogSortOption.entries.forEach { option ->
                        Text(
                            text = option.label,
                            fontSize = 12.sp,
                            fontWeight = FontWeight.SemiBold,
                            color = if (sortBy == option) Color.White else Color.White.copy(alpha = 0.72f),
                            modifier = Modifier
                                .clip(RoundedCornerShape(10.dp))
                                .background(
                                    if (sortBy == option) CatalogGold else Color.White.copy(alpha = 0.1f)
                                )
                                .clickable { onSortChange(option) }
                                .padding(horizontal = 12.dp, vertical = 8.dp)
                        )
                    }
                }
            }
        }
    }
}

@Composable
private fun CatalogContent(
    categories: List<String>,
    selectedCategory: String,
    languageFilters: List<String>,
    selectedLanguage: String,
    books: List<Kitab>,
    viewMode: CatalogViewMode,
    totalCount: Int,
    onSelectCategory: (String) -> Unit,
    onSelectLanguage: (String) -> Unit,
    onBookClick: (Int) -> Unit,
    onChangeViewMode: (CatalogViewMode) -> Unit,
    onResetFilter: () -> Unit
) {
    Column(modifier = Modifier.fillMaxSize()) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(start = 20.dp, end = 20.dp, top = 14.dp)
                .horizontalScroll(rememberScrollState()),
            horizontalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            categories.forEach { category ->
                val isSelected = selectedCategory == category
                Text(
                    text = category,
                    fontSize = 13.sp,
                    fontWeight = if (isSelected) FontWeight.Bold else FontWeight.Medium,
                    color = if (isSelected) Color.White else CatalogSecondaryText,
                    modifier = Modifier
                        .clip(RoundedCornerShape(16.dp))
                        .background(if (isSelected) CatalogHeaderStart else Color.White)
                        .border(
                            width = if (isSelected) 0.dp else 1.dp,
                            color = CatalogCardBorder,
                            shape = RoundedCornerShape(16.dp)
                        )
                        .clickable { onSelectCategory(category) }
                        .padding(horizontal = 14.dp, vertical = 8.dp)
                )
            }
        }

        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(start = 20.dp, end = 20.dp, top = 8.dp)
                .horizontalScroll(rememberScrollState()),
            horizontalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            languageFilters.forEach { language ->
                val isSelected = selectedLanguage == language
                Text(
                    text = language,
                    fontSize = 12.sp,
                    fontWeight = if (isSelected) FontWeight.Bold else FontWeight.Medium,
                    color = if (isSelected) Color.White else CatalogSecondaryText,
                    modifier = Modifier
                        .clip(RoundedCornerShape(14.dp))
                        .background(if (isSelected) CatalogGold else Color.White)
                        .border(
                            width = if (isSelected) 0.dp else 1.dp,
                            color = CatalogCardBorder,
                            shape = RoundedCornerShape(14.dp)
                        )
                        .clickable { onSelectLanguage(language) }
                        .padding(horizontal = 12.dp, vertical = 7.dp)
                )
            }
        }

        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(start = 20.dp, end = 20.dp, top = 12.dp, bottom = 8.dp),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            Text(
                text = buildString {
                    append("$totalCount kitab")
                    if (selectedCategory != "Semua") append(" · $selectedCategory")
                    if (selectedLanguage != "Semua") append(" · $selectedLanguage")
                },
                fontSize = 13.sp,
                color = CatalogMutedText
            )

            Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                CatalogViewModeButton(
                    active = viewMode == CatalogViewMode.List,
                    icon = {
                        Icon(
                            imageVector = Icons.AutoMirrored.Filled.List,
                            contentDescription = null,
                            tint = if (viewMode == CatalogViewMode.List) Color.White else CatalogMutedText,
                            modifier = Modifier.size(16.dp)
                        )
                    },
                    onClick = { onChangeViewMode(CatalogViewMode.List) }
                )
                CatalogViewModeButton(
                    active = viewMode == CatalogViewMode.Grid,
                    icon = {
                        Icon(
                            imageVector = Icons.Default.Apps,
                            contentDescription = null,
                            tint = if (viewMode == CatalogViewMode.Grid) Color.White else CatalogMutedText,
                            modifier = Modifier.size(16.dp)
                        )
                    },
                    onClick = { onChangeViewMode(CatalogViewMode.Grid) }
                )
            }
        }

        if (books.isEmpty()) {
            Box(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(20.dp),
                contentAlignment = Alignment.Center
            ) {
                Column(horizontalAlignment = Alignment.CenterHorizontally) {
                    Text(text = "📭", fontSize = 48.sp)
                    Text(
                        text = "Tidak ada kitab",
                        fontSize = 16.sp,
                        color = Color(0xFF3D2C1E),
                        fontWeight = FontWeight.Bold,
                        modifier = Modifier.padding(top = 10.dp)
                    )
                    Text(
                        text = "Coba kategori atau kata kunci lain",
                        fontSize = 13.sp,
                        color = CatalogMutedText,
                        modifier = Modifier.padding(top = 4.dp)
                    )
                    Text(
                        text = "Reset Filter",
                        fontSize = 13.sp,
                        fontWeight = FontWeight.SemiBold,
                        color = Color.White,
                        modifier = Modifier
                            .padding(top = 14.dp)
                            .clip(RoundedCornerShape(12.dp))
                            .background(CatalogHeaderStart)
                            .clickable(onClick = onResetFilter)
                            .padding(horizontal = 18.dp, vertical = 10.dp)
                    )
                }
            }
        } else if (viewMode == CatalogViewMode.Grid) {
            LazyVerticalGrid(
                columns = GridCells.Fixed(2),
                modifier = Modifier.fillMaxSize(),
                contentPadding = PaddingValues(start = 20.dp, end = 20.dp, bottom = 24.dp, top = 4.dp),
                horizontalArrangement = Arrangement.spacedBy(12.dp),
                verticalArrangement = Arrangement.spacedBy(12.dp)
            ) {
                items(books, key = { it.idKitab }) { book ->
                    CatalogGridCard(
                        book = book,
                        onClick = { onBookClick(book.idKitab) }
                    )
                }
            }
        } else {
            LazyColumn(
                modifier = Modifier.fillMaxSize(),
                contentPadding = PaddingValues(start = 20.dp, end = 20.dp, bottom = 24.dp, top = 4.dp),
                verticalArrangement = Arrangement.spacedBy(10.dp)
            ) {
                items(books, key = { it.idKitab }) { book ->
                    CatalogListCard(
                        book = book,
                        onClick = { onBookClick(book.idKitab) }
                    )
                }
            }
        }
    }
}

@Composable
private fun CatalogViewModeButton(
    active: Boolean,
    icon: @Composable () -> Unit,
    onClick: () -> Unit
) {
    Box(
        modifier = Modifier
            .clip(RoundedCornerShape(10.dp))
            .background(if (active) CatalogHeaderStart else Color(0xFFF0EBE0))
            .clickable(onClick = onClick)
            .padding(8.dp),
        contentAlignment = Alignment.Center
    ) {
        icon()
    }
}

@Composable
private fun CatalogListCard(book: Kitab, onClick: () -> Unit) {
    Card(
        onClick = onClick,
        shape = RoundedCornerShape(14.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 0.dp),
        modifier = Modifier.fillMaxWidth()
    ) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .border(1.dp, CatalogCardBorder, RoundedCornerShape(14.dp))
                .padding(10.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            AsyncImage(
                model = ApiConfig.getCoverUrl(book.cover),
                contentDescription = book.judul,
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
                    text = book.judul,
                    fontSize = 14.sp,
                    lineHeight = 18.sp,
                    fontWeight = FontWeight.Bold,
                    color = CatalogPrimaryText,
                    maxLines = 2,
                    overflow = TextOverflow.Ellipsis
                )
                Text(
                    text = book.penulis,
                    fontSize = 12.sp,
                    color = CatalogMutedText,
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis,
                    modifier = Modifier.padding(top = 2.dp)
                )
                Text(
                    text = "${book.kategori} • ${book.bahasa}",
                    fontSize = 11.sp,
                    color = CatalogHeaderStart,
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis,
                    modifier = Modifier.padding(top = 6.dp)
                )
            }
        }
    }
}

@Composable
private fun CatalogGridCard(book: Kitab, onClick: () -> Unit) {
    Card(
        onClick = onClick,
        shape = RoundedCornerShape(14.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 0.dp),
        modifier = Modifier.fillMaxWidth()
    ) {
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .border(1.dp, CatalogCardBorder, RoundedCornerShape(14.dp))
                .padding(10.dp)
        ) {
            AsyncImage(
                model = ApiConfig.getCoverUrl(book.cover),
                contentDescription = book.judul,
                contentScale = ContentScale.Crop,
                modifier = Modifier
                    .fillMaxWidth()
                    .height(150.dp)
                    .clip(RoundedCornerShape(10.dp))
            )

            Text(
                text = book.judul,
                fontSize = 13.sp,
                lineHeight = 17.sp,
                fontWeight = FontWeight.Bold,
                color = CatalogPrimaryText,
                maxLines = 2,
                overflow = TextOverflow.Ellipsis,
                modifier = Modifier.padding(top = 8.dp)
            )

            Text(
                text = book.penulis,
                fontSize = 11.sp,
                color = CatalogMutedText,
                maxLines = 1,
                overflow = TextOverflow.Ellipsis,
                modifier = Modifier.padding(top = 2.dp)
            )

            Text(
                text = book.kategori,
                fontSize = 10.sp,
                fontWeight = FontWeight.SemiBold,
                color = CatalogHeaderStart,
                maxLines = 1,
                overflow = TextOverflow.Ellipsis,
                modifier = Modifier.padding(top = 6.dp)
            )
        }
    }
}
