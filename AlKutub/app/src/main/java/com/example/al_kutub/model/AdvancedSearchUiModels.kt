package com.example.al_kutub.model

data class AdvancedSearchUiState(
    val isInitialLoading: Boolean = false,
    val isLoadingMore: Boolean = false,
    val error: String? = null,
    val currentFilter: SearchFilter = SearchFilter(),
    val results: List<SearchResult> = emptyList(),
    val total: Int = 0,
    val offset: Int = 0,
    val hasMore: Boolean = false,
    val discoveryItems: List<SearchResult> = emptyList(),
    val historyItems: List<SearchHistoryUiItem> = emptyList(),
    val availableFilters: SearchFilterOptions = SearchFilterOptions()
)
