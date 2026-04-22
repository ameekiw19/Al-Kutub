package com.example.al_kutub.ui.viewmodel
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.al_kutub.data.repository.HomeRepository
import com.example.al_kutub.model.UiKitab
import com.example.al_kutub.model.SearchResult
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

import dagger.hilt.android.lifecycle.HiltViewModel
import com.example.al_kutub.utils.SessionManager
import javax.inject.Inject

@HiltViewModel
class HomeViewModel @Inject constructor(
    private val repository: HomeRepository,
    private val sessionManager: SessionManager
) : ViewModel() {

    private val _kitabList = MutableStateFlow<List<UiKitab>>(emptyList())
    val kitabList: StateFlow<List<UiKitab>> get() = _kitabList

    private val _recommendations = MutableStateFlow<List<UiKitab>>(emptyList())
    val recommendations: StateFlow<List<UiKitab>> get() = _recommendations

    private val _errorMessage = MutableStateFlow<String?>(null)
    val errorMessage: StateFlow<String?> get() = _errorMessage

    private val _loading = MutableStateFlow(false)
    val loading: StateFlow<Boolean> get() = _loading

    // Search states
    private val _searchQuery = MutableStateFlow("")
    val searchQuery: StateFlow<String> get() = _searchQuery.asStateFlow()

    private val _searchResults = MutableStateFlow<List<SearchResult>>(emptyList())
    val searchResults: StateFlow<List<SearchResult>> get() = _searchResults.asStateFlow()

    private val _isSearching = MutableStateFlow(false)
    val isSearching: StateFlow<Boolean> get() = _isSearching.asStateFlow()

    private val _showSearchResults = MutableStateFlow(false)
    val showSearchResults: StateFlow<Boolean> get() = _showSearchResults.asStateFlow()

    // Search filters
    private val _searchKategori = MutableStateFlow<String?>(null)
    val searchKategori: StateFlow<String?> get() = _searchKategori.asStateFlow()

    private val _searchBahasa = MutableStateFlow<String?>(null)
    val searchBahasa: StateFlow<String?> get() = _searchBahasa.asStateFlow()

    // Sort
    private val _searchSortBy = MutableStateFlow("relevance")
    val searchSortBy: StateFlow<String> get() = _searchSortBy.asStateFlow()

    fun loadKitab() {
        viewModelScope.launch {
            _loading.value = true
            
            // Load recommendations simultaneously
            val token = sessionManager.getToken()
            launch {
                repository.fetchRecommendations(token)
                    .onSuccess { _recommendations.value = it }
            }

            repository.fetchUiKitabList()
                .onSuccess {
                    _kitabList.value = it
                    _errorMessage.value = null
                }
                .onFailure {
                    _errorMessage.value = it.localizedMessage
                }
            _loading.value = false
        }
    }

    fun updateSearchQuery(query: String) {
        _searchQuery.value = query
        if (query.isBlank()) {
            _showSearchResults.value = false
            _searchResults.value = emptyList()
        } else {
            searchKitab(query)
        }
    }

    fun setSearchKategori(kategori: String?) {
        _searchKategori.value = kategori
        if (_searchQuery.value.isNotBlank()) {
            searchKitab(_searchQuery.value)
        }
    }

    fun setSearchBahasa(bahasa: String?) {
        _searchBahasa.value = bahasa
        if (_searchQuery.value.isNotBlank()) {
            searchKitab(_searchQuery.value)
        }
    }

    fun setSearchSortBy(sortBy: String) {
        _searchSortBy.value = sortBy
        if (_searchQuery.value.isNotBlank()) {
            searchKitab(_searchQuery.value)
        }
    }

    private fun searchKitab(query: String) {
        viewModelScope.launch {
            _isSearching.value = true
            repository.searchKitab(
                query = query,
                limit = 20,
                kategori = _searchKategori.value,
                bahasa = _searchBahasa.value,
                sortBy = _searchSortBy.value,
                sortOrder = "desc"
            )
                .onSuccess { response ->
                    if (response.success) {
                        _searchResults.value = response.data
                        _showSearchResults.value = true
                    } else {
                        _searchResults.value = emptyList()
                        _showSearchResults.value = false
                    }
                }
                .onFailure {
                    _searchResults.value = emptyList()
                    _showSearchResults.value = false
                }
            _isSearching.value = false
        }
    }

    fun clearSearch() {
        _searchQuery.value = ""
        _searchResults.value = emptyList()
        _showSearchResults.value = false
        _searchKategori.value = null
        _searchBahasa.value = null
        _searchSortBy.value = "relevance"
    }

    fun hideSearchResults() {
        _showSearchResults.value = false
    }
}
