package com.example.al_kutub.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.al_kutub.data.repository.EnhancedSearchRepository
import com.example.al_kutub.model.AdvancedSearchUiState
import com.example.al_kutub.model.SearchFilter
import com.example.al_kutub.model.SearchHistoryUiItem
import com.example.al_kutub.model.SearchSuggestion
import com.example.al_kutub.model.SuggestionType
import com.example.al_kutub.model.SortOption
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.Job
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.collectLatest
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class AdvancedSearchViewModel @Inject constructor(
    private val searchRepository: EnhancedSearchRepository
) : ViewModel() {

    private val _uiState = MutableStateFlow(AdvancedSearchUiState())
    val uiState: StateFlow<AdvancedSearchUiState> = _uiState.asStateFlow()

    private val _suggestions = MutableStateFlow<List<SearchSuggestion>>(emptyList())
    val suggestions: StateFlow<List<SearchSuggestion>> = _suggestions.asStateFlow()

    private var suggestionJob: Job? = null
    private var appliedArgsKey: String? = null

    init {
        observeHistory()
        refreshSearchHistory()
        loadInitialDiscovery()
    }

    fun applyInitialArgs(
        query: String? = null,
        category: String? = null,
        language: String? = null,
        sort: String? = null
    ) {
        val key = listOf(query.orEmpty(), category.orEmpty(), language.orEmpty(), sort.orEmpty())
            .joinToString("|")
        if (appliedArgsKey == key) {
            return
        }
        appliedArgsKey = key

        val nextFilter = SearchFilter(
            query = query?.trim().orEmpty(),
            categories = category?.takeIf { it.isNotBlank() }?.let(::listOf) ?: emptyList(),
            languages = language?.takeIf { it.isNotBlank() }?.let(::listOf) ?: emptyList(),
            sortBy = SortOption.fromValue(sort)
        )

        _uiState.value = _uiState.value.copy(currentFilter = nextFilter)

        if (nextFilter.hasActiveFilters()) {
            executeSearch(reset = true)
        } else {
            loadInitialDiscovery()
        }
    }

    fun updateQuery(query: String) {
        val currentState = _uiState.value
        val updatedFilter = currentState.currentFilter.copy(query = query)
        _uiState.value = currentState.copy(currentFilter = updatedFilter, error = null)

        suggestionJob?.cancel()
        if (query.trim().length < 2) {
            _suggestions.value = emptyList()
            if (query.isBlank() && !updatedFilter.hasNonQueryFilters()) {
                loadInitialDiscovery()
            }
            return
        }

        suggestionJob = viewModelScope.launch {
            delay(300L)
            searchRepository.getSuggestions(query).fold(
                onSuccess = { _suggestions.value = it },
                onFailure = { _suggestions.value = emptyList() }
            )
        }
    }

    fun clearQuery() {
        val currentFilter = _uiState.value.currentFilter
        val updatedFilter = currentFilter.copy(query = "")
        _uiState.value = _uiState.value.copy(currentFilter = updatedFilter, error = null)
        _suggestions.value = emptyList()

        if (updatedFilter.hasNonQueryFilters()) {
            executeSearch(reset = true)
        } else {
            loadInitialDiscovery()
        }
    }

    fun updateFilter(filter: SearchFilter) {
        _uiState.value = _uiState.value.copy(currentFilter = filter, error = null)
    }

    fun executeSearch(reset: Boolean = true) {
        val currentFilter = _uiState.value.currentFilter
        if (currentFilter.query.isBlank() && !currentFilter.hasNonQueryFilters()) {
            loadInitialDiscovery()
            return
        }

        viewModelScope.launch {
            val nextOffset = if (reset) 0 else _uiState.value.offset
            _uiState.value = _uiState.value.copy(
                isInitialLoading = reset,
                isLoadingMore = !reset,
                error = null
            )

            searchRepository.performSearch(
                searchFilter = currentFilter,
                offset = nextOffset,
                limit = PAGE_SIZE,
                saveHistory = reset
            ).fold(
                onSuccess = { response ->
                    val combinedResults = if (reset) {
                        response.data
                    } else {
                        _uiState.value.results + response.data
                    }

                    _uiState.value = _uiState.value.copy(
                        isInitialLoading = false,
                        isLoadingMore = false,
                        results = combinedResults,
                        total = response.total,
                        offset = combinedResults.size,
                        hasMore = combinedResults.size < response.total,
                        discoveryItems = if (reset) emptyList() else _uiState.value.discoveryItems,
                        availableFilters = response.filters
                    )
                    _suggestions.value = emptyList()
                    refreshSearchHistory()
                },
                onFailure = { error ->
                    _uiState.value = _uiState.value.copy(
                        isInitialLoading = false,
                        isLoadingMore = false,
                        error = error.message ?: "Gagal memuat hasil pencarian"
                    )
                }
            )
        }
    }

    fun loadMore() {
        val state = _uiState.value
        if (!state.hasMore || state.isInitialLoading || state.isLoadingMore) {
            return
        }
        executeSearch(reset = false)
    }

    fun loadInitialDiscovery() {
        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(
                isInitialLoading = true,
                isLoadingMore = false,
                results = emptyList(),
                total = 0,
                offset = 0,
                hasMore = false,
                error = null
            )

            searchRepository.performSearch(
                searchFilter = SearchFilter(sortBy = SortOption.VIEWS),
                offset = 0,
                limit = PAGE_SIZE,
                saveHistory = false
            ).fold(
                onSuccess = { response ->
                    _uiState.value = _uiState.value.copy(
                        isInitialLoading = false,
                        discoveryItems = response.data,
                        availableFilters = response.filters
                    )
                },
                onFailure = { error ->
                    _uiState.value = _uiState.value.copy(
                        isInitialLoading = false,
                        error = error.message ?: "Gagal memuat discovery"
                    )
                }
            )
        }
    }

    fun applySuggestion(suggestion: SearchSuggestion) {
        val currentFilter = _uiState.value.currentFilter
        val nextFilter = when (suggestion.type) {
            SuggestionType.AUTHOR -> currentFilter.copy(
                query = "",
                authors = listOf(suggestion.text),
                categories = emptyList(),
                languages = emptyList()
            )
            SuggestionType.CATEGORY -> currentFilter.copy(
                query = "",
                categories = listOf(suggestion.text),
                authors = emptyList(),
                languages = emptyList()
            )
            SuggestionType.LANGUAGE -> currentFilter.copy(
                query = "",
                languages = listOf(suggestion.text),
                categories = emptyList(),
                authors = emptyList()
            )
            SuggestionType.QUERY -> currentFilter.copy(query = suggestion.text)
        }

        _uiState.value = _uiState.value.copy(currentFilter = nextFilter, error = null)
        executeSearch(reset = true)
    }

    fun applyHistoryItem(item: SearchHistoryUiItem) {
        val nextFilter = _uiState.value.currentFilter.copy(query = item.query)
        _uiState.value = _uiState.value.copy(currentFilter = nextFilter, error = null)
        executeSearch(reset = true)
    }

    fun clearAllHistory() {
        viewModelScope.launch {
            searchRepository.clearAllHistory()
            _uiState.value = _uiState.value.copy(historyItems = emptyList())
        }
    }

    fun deleteHistoryItem(item: SearchHistoryUiItem) {
        viewModelScope.launch {
            searchRepository.deleteSearchHistoryItem(item)
            _uiState.value = _uiState.value.copy(
                historyItems = _uiState.value.historyItems.filterNot {
                    it.query.equals(item.query, ignoreCase = true)
                }
            )
        }
    }

    fun clearError() {
        _uiState.value = _uiState.value.copy(error = null)
    }

    private fun observeHistory() {
        viewModelScope.launch {
            searchRepository.searchHistory.collectLatest { history ->
                _uiState.value = _uiState.value.copy(historyItems = history)
            }
        }
    }

    private fun refreshSearchHistory() {
        viewModelScope.launch {
            searchRepository.refreshSearchHistory()
        }
    }

    companion object {
        private const val PAGE_SIZE = 20
    }
}
