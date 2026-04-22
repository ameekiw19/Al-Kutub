package com.example.al_kutub.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.al_kutub.data.repository.KatalogRepository
import com.example.al_kutub.model.Kitab
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.Job
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.MutableSharedFlow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.SharedFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asSharedFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class KatalogViewModel @Inject constructor(
    private val repository: KatalogRepository
) : ViewModel() {
    companion object {
        val LANGUAGE_FILTERS = listOf("Semua", "Indonesia", "Arab")
        const val DEFAULT_SORT = "latest"
    }

    private val _uiState = MutableStateFlow<KatalogUiState>(KatalogUiState.Loading)
    val uiState: StateFlow<KatalogUiState> = _uiState.asStateFlow()

    private val _selectedCategory = MutableStateFlow("Semua")
    val selectedCategory: StateFlow<String> = _selectedCategory.asStateFlow()

    private val _selectedLanguage = MutableStateFlow("Semua")
    val selectedLanguage: StateFlow<String> = _selectedLanguage.asStateFlow()

    private val _searchQuery = MutableStateFlow("")
    val searchQuery: StateFlow<String> = _searchQuery.asStateFlow()

    private val _selectedSort = MutableStateFlow(DEFAULT_SORT)
    val selectedSort: StateFlow<String> = _selectedSort.asStateFlow()

    private val _toastMessage = MutableSharedFlow<String>()
    val toastMessage: SharedFlow<String> = _toastMessage.asSharedFlow()

    private var categories: List<String> = emptyList()
    private var languages: List<String> = LANGUAGE_FILTERS
    private var totalKitabCount: Int = 0
    private var isInitialLoaded: Boolean = false

    private var searchJob: Job? = null

    init {
        loadKatalog()
    }

    fun loadKatalog() {
        viewModelScope.launch {
            _uiState.value = KatalogUiState.Loading

            repository.getKatalog(
                sortBy = _selectedSort.value,
                sortOrder = "desc",
                perPage = 50
            ).onSuccess { response ->
                val data = response.data
                categories = data?.kategori.orEmpty()
                val apiLanguages = data?.bahasa.orEmpty()
                languages = (LANGUAGE_FILTERS + apiLanguages).distinct()
                val books = data?.kitab.orEmpty()
                totalKitabCount = response.pagination?.total ?: books.size
                isInitialLoaded = true

                _uiState.value = KatalogUiState.Success(
                    kategori = categories,
                    kitab = books,
                    bahasa = apiLanguages.ifEmpty { LANGUAGE_FILTERS.drop(1) }
                )
            }.onFailure { error ->
                _uiState.value = KatalogUiState.Error(
                    message = error.message ?: "Gagal memuat katalog"
                )
                showToast("Gagal memuat katalog: ${error.message}")
            }
        }
    }

    fun selectCategory(category: String) {
        if (_selectedCategory.value == category) return
        _selectedCategory.value = category
        fetchFiltered()
    }

    fun selectLanguage(language: String) {
        if (_selectedLanguage.value == language) return
        _selectedLanguage.value = language
        fetchFiltered()
    }

    fun searchKitab(query: String) {
        _searchQuery.value = query
        searchJob?.cancel()
        searchJob = viewModelScope.launch {
            delay(280)
            fetchFiltered()
        }
    }

    fun selectSort(sortKey: String) {
        if (_selectedSort.value == sortKey) return
        _selectedSort.value = sortKey
        fetchFiltered()
    }

    fun resetFilters() {
        _selectedCategory.value = "Semua"
        _selectedLanguage.value = "Semua"
        _searchQuery.value = ""
        _selectedSort.value = DEFAULT_SORT
        fetchFiltered()
    }

    fun getTotalKitab(): Int = totalKitabCount

    fun getLanguageFilters(): List<String> = languages.ifEmpty { LANGUAGE_FILTERS }

    fun retry() {
        if (!isInitialLoaded) {
            loadKatalog()
        } else {
            fetchFiltered()
        }
    }

    private fun fetchFiltered() {
        viewModelScope.launch {
            if (!isInitialLoaded) {
                loadKatalog()
                return@launch
            }

            val oldState = _uiState.value
            if (oldState !is KatalogUiState.Success) {
                _uiState.value = KatalogUiState.Loading
            }

            repository.filterKatalog(
                kategori = normalizeCategory(_selectedCategory.value),
                bahasa = normalizeLanguage(_selectedLanguage.value),
                search = _searchQuery.value.trim().ifBlank { null },
                sortBy = _selectedSort.value,
                sortOrder = "desc",
                perPage = 50
            ).onSuccess { response ->
                val books = response.data?.kitab.orEmpty()
                totalKitabCount = response.pagination?.total ?: books.size

                _uiState.value = KatalogUiState.Success(
                    kategori = categories,
                    kitab = books,
                    bahasa = languages.filterNot { it.equals("Semua", ignoreCase = true) }
                )
            }.onFailure { error ->
                _uiState.value = KatalogUiState.Error(
                    message = error.message ?: "Gagal memuat filter katalog"
                )
                showToast("Gagal memuat filter katalog: ${error.message}")
            }
        }
    }

    private fun normalizeCategory(value: String): String? {
        return value.takeUnless { it.equals("Semua", ignoreCase = true) }?.trim()?.ifBlank { null }
    }

    private fun normalizeLanguage(value: String): String? {
        val normalized = value.trim().lowercase()
        if (normalized.isBlank() || normalized == "semua") return null
        return when {
            normalized.contains("arab") || normalized.contains("عرب") -> "arab"
            normalized.contains("indo") || normalized.contains("indonesia") -> "indonesia"
            else -> normalized
        }
    }

    private fun showToast(message: String) {
        viewModelScope.launch {
            _toastMessage.emit(message)
        }
    }
}

sealed class KatalogUiState {
    object Loading : KatalogUiState()

    data class Success(
        val kategori: List<String>,
        val kitab: List<Kitab>,
        val bahasa: List<String>
    ) : KatalogUiState()

    data class Error(
        val message: String,
        val canRetry: Boolean = true
    ) : KatalogUiState()
}
