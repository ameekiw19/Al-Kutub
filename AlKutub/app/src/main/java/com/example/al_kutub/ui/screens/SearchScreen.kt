package com.example.al_kutub.ui.screens

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
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material3.AssistChip
import androidx.compose.material3.Button
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.SnackbarHost
import androidx.compose.material3.SnackbarHostState
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.material3.TopAppBar
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
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import coil.compose.AsyncImage
import com.example.al_kutub.model.SearchFilter
import com.example.al_kutub.model.SearchResult
import com.example.al_kutub.model.SortOption
import com.example.al_kutub.model.SortOrder
import com.example.al_kutub.model.UiKitab
import com.example.al_kutub.ui.components.EnhancedSearchBarComponent
import com.example.al_kutub.ui.viewmodel.AdvancedSearchViewModel

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun SearchScreen(
    viewModel: AdvancedSearchViewModel,
    initialQuery: String? = null,
    initialCategory: String? = null,
    initialLanguage: String? = null,
    initialSort: String? = null,
    onNavigateBack: () -> Unit,
    onKitabClick: (UiKitab) -> Unit
) {
    val uiState by viewModel.uiState.collectAsState()
    val suggestions by viewModel.suggestions.collectAsState()
    val snackbarHostState = remember { SnackbarHostState() }
    var showFilterSheet by rememberSaveable { mutableStateOf(false) }

    LaunchedEffect(initialQuery, initialCategory, initialLanguage, initialSort) {
        viewModel.applyInitialArgs(
            query = initialQuery,
            category = initialCategory,
            language = initialLanguage,
            sort = initialSort
        )
    }

    LaunchedEffect(uiState.error) {
        uiState.error?.let {
            snackbarHostState.showSnackbar(it)
            viewModel.clearError()
        }
    }

    val showOverlaySuggestions =
        uiState.currentFilter.query.trim().length >= 2 ||
            (uiState.currentFilter.query.isBlank() && !uiState.currentFilter.hasNonQueryFilters())

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Cari Kitab") },
                navigationIcon = {
                    IconButton(onClick = onNavigateBack) {
                        Icon(
                            imageVector = Icons.AutoMirrored.Filled.ArrowBack,
                            contentDescription = "Kembali"
                        )
                    }
                }
            )
        },
        snackbarHost = { SnackbarHost(hostState = snackbarHostState) }
    ) { innerPadding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(innerPadding)
                .padding(horizontal = 16.dp)
        ) {
            EnhancedSearchBarComponent(
                query = uiState.currentFilter.query,
                onQueryChange = viewModel::updateQuery,
                onClearSearch = viewModel::clearQuery,
                onOpenFilters = { showFilterSheet = true },
                onSearchSubmit = { viewModel.executeSearch(reset = true) },
                suggestions = suggestions,
                searchHistory = if (uiState.currentFilter.query.isBlank()) uiState.historyItems else emptyList(),
                showSuggestions = showOverlaySuggestions,
                isSearching = uiState.isInitialLoading,
                onSuggestionClick = viewModel::applySuggestion,
                onHistoryItemClick = viewModel::applyHistoryItem,
                onDeleteHistoryItem = viewModel::deleteHistoryItem,
                onClearAllHistory = viewModel::clearAllHistory
            )

            Spacer(modifier = Modifier.height(12.dp))

            FilterSummaryRow(
                filter = uiState.currentFilter,
                onClearFilters = {
                    val preservedQuery = uiState.currentFilter.query
                    viewModel.updateFilter(
                        SearchFilter(
                            query = preservedQuery,
                            sortBy = SortOption.RELEVANCE,
                            sortOrder = SortOrder.DESC
                        )
                    )
                    if (preservedQuery.isBlank()) {
                        viewModel.loadInitialDiscovery()
                    } else {
                        viewModel.executeSearch(reset = true)
                    }
                }
            )

            SearchContent(
                uiState = uiState,
                onRetry = {
                    if (uiState.currentFilter.query.isBlank() && !uiState.currentFilter.hasNonQueryFilters()) {
                        viewModel.loadInitialDiscovery()
                    } else {
                        viewModel.executeSearch(reset = true)
                    }
                },
                onLoadMore = viewModel::loadMore,
                onKitabClick = onKitabClick
            )
        }
    }

    if (showFilterSheet) {
        AdvancedSearchFilterSheet(
            filter = uiState.currentFilter,
            onFilterChange = viewModel::updateFilter,
            onApply = { viewModel.executeSearch(reset = true) },
            onReset = {
                viewModel.updateFilter(
                    uiState.currentFilter.copy(
                        categories = emptyList(),
                        authors = emptyList(),
                        languages = emptyList(),
                        sortBy = SortOption.RELEVANCE,
                        sortOrder = SortOrder.DESC
                    )
                )
            },
            onDismiss = { showFilterSheet = false },
            availableCategories = uiState.availableFilters.categories,
            availableAuthors = uiState.availableFilters.authors,
            availableLanguages = uiState.availableFilters.languages
        )
    }
}

@Composable
private fun SearchContent(
    uiState: com.example.al_kutub.model.AdvancedSearchUiState,
    onRetry: () -> Unit,
    onLoadMore: () -> Unit,
    onKitabClick: (UiKitab) -> Unit
) {
    val isDiscoveryState = uiState.currentFilter.query.isBlank() && !uiState.currentFilter.hasNonQueryFilters()

    when {
        uiState.isInitialLoading && isDiscoveryState && uiState.discoveryItems.isEmpty() -> {
            Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                CircularProgressIndicator()
            }
        }

        uiState.isInitialLoading && !isDiscoveryState && uiState.results.isEmpty() -> {
            Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                CircularProgressIndicator()
            }
        }

        isDiscoveryState -> {
            LazyColumn(
                modifier = Modifier.fillMaxSize(),
                contentPadding = PaddingValues(bottom = 24.dp),
                verticalArrangement = Arrangement.spacedBy(12.dp)
            ) {
                item {
                    SectionTitle("Jelajahi Populer")
                }

                if (uiState.discoveryItems.isEmpty()) {
                    item {
                        EmptyState(
                            title = "Belum ada discovery",
                            description = "Coba lagi sebentar, atau mulai dengan kata kunci."
                        )
                    }
                } else {
                    items(uiState.discoveryItems, key = { it.idKitab }) { result ->
                        SearchResultCard(
                            result = result,
                            onClick = { onKitabClick(result.toUiKitab()) }
                        )
                    }
                }
            }
        }

        uiState.results.isEmpty() -> {
            EmptyState(
                title = "Tidak ada hasil",
                description = "Ubah kata kunci atau filter, lalu coba lagi.",
                actionLabel = "Coba Lagi",
                onAction = onRetry
            )
        }

        else -> {
            LazyColumn(
                modifier = Modifier.fillMaxSize(),
                contentPadding = PaddingValues(bottom = 24.dp),
                verticalArrangement = Arrangement.spacedBy(12.dp)
            ) {
                item {
                    Text(
                        text = "${uiState.results.size} dari ${uiState.total} hasil",
                        style = MaterialTheme.typography.labelLarge,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                }

                items(uiState.results, key = { it.idKitab }) { result ->
                    SearchResultCard(
                        result = result,
                        onClick = { onKitabClick(result.toUiKitab()) }
                    )
                }

                if (uiState.hasMore) {
                    item {
                        Box(
                            modifier = Modifier
                                .fillMaxWidth()
                                .padding(top = 4.dp),
                            contentAlignment = Alignment.Center
                        ) {
                            if (uiState.isLoadingMore) {
                                CircularProgressIndicator(modifier = Modifier.size(28.dp))
                            } else {
                                Button(onClick = onLoadMore) {
                                    Text("Muat Lagi")
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
private fun FilterSummaryRow(
    filter: SearchFilter,
    onClearFilters: () -> Unit
) {
    val chips = buildList {
        addAll(filter.categories.map { "Kategori: $it" })
        addAll(filter.authors.map { "Penulis: $it" })
        addAll(filter.languages.map { "Bahasa: $it" })
        if (filter.sortBy != SortOption.RELEVANCE || filter.sortOrder != SortOrder.DESC) {
            add("Urut: ${filter.sortBy.label}")
        }
    }

    if (chips.isEmpty()) {
        return
    }

    Column(
        modifier = Modifier
            .fillMaxWidth()
            .padding(bottom = 12.dp),
        verticalArrangement = Arrangement.spacedBy(8.dp)
    ) {
        Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            Text(
                text = "Filter Aktif",
                style = MaterialTheme.typography.titleSmall,
                fontWeight = FontWeight.SemiBold
            )
            TextButton(onClick = onClearFilters) {
                Text("Reset Filter")
            }
        }

        Column(verticalArrangement = Arrangement.spacedBy(8.dp)) {
            chips.forEach { label ->
                AssistChip(
                    onClick = {},
                    label = { Text(label, maxLines = 1, overflow = TextOverflow.Ellipsis) }
                )
            }
        }
    }
}

@Composable
private fun SearchResultCard(
    result: SearchResult,
    onClick: () -> Unit
) {
    Surface(
        modifier = Modifier
            .fillMaxWidth()
            .clickable(onClick = onClick),
        shape = RoundedCornerShape(18.dp),
        tonalElevation = 2.dp
    ) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(14.dp),
            horizontalArrangement = Arrangement.spacedBy(14.dp)
        ) {
            AsyncImage(
                model = result.cover,
                contentDescription = result.judul,
                modifier = Modifier.size(72.dp),
                contentScale = ContentScale.Crop
            )

            Column(modifier = Modifier.weight(1f)) {
                Text(
                    text = result.judul,
                    style = MaterialTheme.typography.titleMedium,
                    fontWeight = FontWeight.SemiBold,
                    maxLines = 2,
                    overflow = TextOverflow.Ellipsis
                )
                Text(
                    text = result.penulis,
                    style = MaterialTheme.typography.bodyMedium,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                    modifier = Modifier.padding(top = 4.dp)
                )
                Text(
                    text = "${result.kategori} • ${result.bahasa.ifBlank { "Bahasa belum tersedia" }}",
                    style = MaterialTheme.typography.labelMedium,
                    color = MaterialTheme.colorScheme.primary,
                    modifier = Modifier.padding(top = 8.dp)
                )
                if (result.deskripsi.isNotBlank()) {
                    Text(
                        text = result.deskripsi,
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                        maxLines = 2,
                        overflow = TextOverflow.Ellipsis,
                        modifier = Modifier.padding(top = 8.dp)
                    )
                }
            }
        }
    }
}

@Composable
private fun SectionTitle(title: String) {
    Column(verticalArrangement = Arrangement.spacedBy(8.dp)) {
        Text(
            text = title,
            style = MaterialTheme.typography.titleMedium,
            fontWeight = FontWeight.Bold
        )
        HorizontalDivider()
    }
}

@Composable
private fun EmptyState(
    title: String,
    description: String,
    actionLabel: String? = null,
    onAction: (() -> Unit)? = null
) {
    Box(
        modifier = Modifier
            .fillMaxSize()
            .padding(horizontal = 24.dp),
        contentAlignment = Alignment.Center
    ) {
        Column(horizontalAlignment = Alignment.CenterHorizontally) {
            Text(
                text = title,
                style = MaterialTheme.typography.titleMedium,
                fontWeight = FontWeight.Bold,
                textAlign = TextAlign.Center
            )
            Text(
                text = description,
                style = MaterialTheme.typography.bodyMedium,
                color = MaterialTheme.colorScheme.onSurfaceVariant,
                textAlign = TextAlign.Center,
                modifier = Modifier.padding(top = 8.dp)
            )
            if (actionLabel != null && onAction != null) {
                Button(
                    onClick = onAction,
                    modifier = Modifier.padding(top = 16.dp)
                ) {
                    Text(actionLabel)
                }
            }
        }
    }
}

private fun SearchResult.toUiKitab(): UiKitab {
    return UiKitab(
        idKitab = idKitab,
        judul = judul,
        penulis = penulis,
        kategori = kategori,
        deskripsi = deskripsi,
        bahasa = bahasa,
        cover = cover,
        views = views,
        downloads = downloads
    )
}
