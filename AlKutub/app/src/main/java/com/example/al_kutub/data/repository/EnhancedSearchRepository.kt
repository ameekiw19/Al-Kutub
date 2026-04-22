package com.example.al_kutub.data.repository

import android.content.Context
import android.content.SharedPreferences
import com.example.al_kutub.api.ApiService
import com.example.al_kutub.model.SaveSearchHistoryRequest
import com.example.al_kutub.model.SearchFilter
import com.example.al_kutub.model.SearchHistoryItem
import com.example.al_kutub.model.SearchHistoryUiItem
import com.example.al_kutub.model.SearchResponse
import com.example.al_kutub.model.SearchSuggestion
import com.example.al_kutub.utils.SessionManager
import com.google.gson.Gson
import com.google.gson.reflect.TypeToken
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import java.time.Instant
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class EnhancedSearchRepository @Inject constructor(
    private val apiService: ApiService,
    private val sessionManager: SessionManager,
    @ApplicationContext context: Context
) {
    private val sharedPreferences: SharedPreferences =
        context.getSharedPreferences("search_preferences", Context.MODE_PRIVATE)
    private val gson = Gson()

    private val _searchHistory = MutableStateFlow<List<SearchHistoryUiItem>>(emptyList())
    val searchHistory: StateFlow<List<SearchHistoryUiItem>> = _searchHistory.asStateFlow()

    private val _suggestions = MutableStateFlow<List<SearchSuggestion>>(emptyList())
    val suggestions: StateFlow<List<SearchSuggestion>> = _suggestions.asStateFlow()

    companion object {
        private const val MAX_HISTORY_SIZE = 20
        private const val HISTORY_KEY = "search_history_items"
    }

    init {
        _searchHistory.value = loadLocalSearchHistory()
    }

    suspend fun performSearch(
        searchFilter: SearchFilter,
        offset: Int = 0,
        limit: Int = 20,
        saveHistory: Boolean = true
    ): Result<SearchResponse> {
        return try {
            val response = apiService.searchKitab(
                query = searchFilter.query.trim(),
                limit = limit,
                offset = offset,
                categories = searchFilter.categories.takeIf { it.isNotEmpty() }?.joinToString(","),
                authors = searchFilter.authors.takeIf { it.isNotEmpty() }?.joinToString(","),
                languages = searchFilter.languages.takeIf { it.isNotEmpty() }?.joinToString(","),
                sortBy = searchFilter.sortBy.value,
                sortOrder = searchFilter.sortOrder.value
            )

            if (response.isSuccessful && response.body() != null) {
                val body = response.body()!!
                if (saveHistory) {
                    saveSearchToHistory(
                        query = searchFilter.query,
                        filter = searchFilter,
                        resultCount = body.total
                    )
                }
                Result.success(body)
            } else {
                Result.failure(Exception(response.message()))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun getSuggestions(query: String): Result<List<SearchSuggestion>> {
        return try {
            if (query.trim().length < 2) {
                _suggestions.value = emptyList()
                return Result.success(emptyList())
            }

            val response = apiService.getSearchSuggestions(query.trim(), limit = 10)
            if (response.isSuccessful && response.body() != null) {
                val items = response.body()!!.data
                _suggestions.value = items
                Result.success(items)
            } else {
                _suggestions.value = emptyList()
                Result.failure(Exception(response.message()))
            }
        } catch (e: Exception) {
            _suggestions.value = emptyList()
            Result.failure(e)
        }
    }

    suspend fun refreshSearchHistory(): Result<List<SearchHistoryUiItem>> {
        val localHistory = loadLocalSearchHistory()
        _searchHistory.value = localHistory

        val token = sessionManager.getToken()
        if (token.isNullOrBlank()) {
            return Result.success(localHistory)
        }

        return try {
            val response = apiService.getSearchHistory(authHeader(token))
            if (response.isSuccessful && response.body() != null) {
                val mergedHistory = mergeHistory(
                    localHistory,
                    response.body()!!.data.map { it.toUiItem() }
                )
                persistLocalSearchHistory(mergedHistory)
                _searchHistory.value = mergedHistory
                Result.success(mergedHistory)
            } else {
                Result.success(localHistory)
            }
        } catch (_: Exception) {
            Result.success(localHistory)
        }
    }

    suspend fun clearSearchHistoryFromServer(): Result<Unit> {
        val token = sessionManager.getToken()
        if (token.isNullOrBlank()) {
            return Result.success(Unit)
        }

        return try {
            val response = apiService.clearSearchHistory(authHeader(token))
            if (response.isSuccessful) {
                Result.success(Unit)
            } else {
                Result.failure(Exception(response.message()))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun clearAllHistory() {
        persistLocalSearchHistory(emptyList())
        _searchHistory.value = emptyList()
        clearSearchHistoryFromServer()
    }

    suspend fun deleteSearchHistoryItem(item: SearchHistoryUiItem) {
        val remainingItems = _searchHistory.value.filterNot {
            normalizeQuery(it.query).equals(normalizeQuery(item.query), ignoreCase = true)
        }

        persistLocalSearchHistory(remainingItems)
        _searchHistory.value = remainingItems

        val token = sessionManager.getToken()
        if (token.isNullOrBlank() || item.id == null) {
            return
        }

        try {
            apiService.deleteSearchHistory(item.id, authHeader(token))
        } catch (_: Exception) {
        }
    }

    private suspend fun saveSearchToHistory(
        query: String,
        filter: SearchFilter,
        resultCount: Int
    ) {
        val normalizedQuery = normalizeQuery(query)
        if (normalizedQuery.isBlank()) {
            return
        }

        val localItem = SearchHistoryUiItem(
            query = normalizedQuery,
            updatedAt = Instant.now().toString(),
            resultCount = resultCount
        )
        val mergedLocalHistory = mergeHistory(listOf(localItem), _searchHistory.value)
        persistLocalSearchHistory(mergedLocalHistory)
        _searchHistory.value = mergedLocalHistory

        val token = sessionManager.getToken()
        if (token.isNullOrBlank()) {
            return
        }

        try {
            val response = apiService.saveSearchHistory(
                authHeader(token),
                SaveSearchHistoryRequest(
                    query = normalizedQuery,
                    filters = filter.toHistoryFiltersMap(),
                    resultCount = resultCount
                )
            )

            val serverItem = response.body()?.data?.toUiItem()

            if (serverItem != null) {
                val syncedHistory = mergeHistory(listOf(serverItem), _searchHistory.value)
                persistLocalSearchHistory(syncedHistory)
                _searchHistory.value = syncedHistory
            }
        } catch (_: Exception) {
        }
    }

    private fun loadLocalSearchHistory(): List<SearchHistoryUiItem> {
        val raw = sharedPreferences.getString(HISTORY_KEY, null) ?: return emptyList()
        return try {
            val type = object : TypeToken<List<SearchHistoryUiItem>>() {}.type
            (gson.fromJson<List<SearchHistoryUiItem>>(raw, type) ?: emptyList())
                .mapNotNull { item ->
                    val normalizedQuery = normalizeQuery(item.query)
                    if (normalizedQuery.isBlank()) {
                        null
                    } else {
                        item.copy(query = normalizedQuery)
                    }
                }
        } catch (_: Exception) {
            emptyList()
        }
    }

    private fun persistLocalSearchHistory(items: List<SearchHistoryUiItem>) {
        val payload = gson.toJson(items.take(MAX_HISTORY_SIZE))
        sharedPreferences.edit().putString(HISTORY_KEY, payload).apply()
    }

    private fun mergeHistory(
        primaryItems: List<SearchHistoryUiItem>,
        secondaryItems: List<SearchHistoryUiItem>
    ): List<SearchHistoryUiItem> {
        val merged = linkedMapOf<String, SearchHistoryUiItem>()

        (primaryItems + secondaryItems).forEach { item ->
            val normalizedQuery = normalizeQuery(item.query)
            if (normalizedQuery.isBlank()) {
                return@forEach
            }

            val key = normalizedQuery.lowercase()
            val candidate = item.copy(query = normalizedQuery)
            val existing = merged[key]

            if (existing == null || sortTimestamp(candidate.updatedAt) >= sortTimestamp(existing.updatedAt)) {
                merged[key] = if (existing != null && candidate.id == null && existing.id != null) {
                    candidate.copy(id = existing.id)
                } else {
                    candidate
                }
            }
        }

        return merged.values
            .sortedByDescending { sortTimestamp(it.updatedAt) }
            .take(MAX_HISTORY_SIZE)
    }

    private fun sortTimestamp(value: String?): Long {
        if (value.isNullOrBlank()) {
            return 0L
        }

        return value.toLongOrNull()
            ?: runCatching { Instant.parse(value).toEpochMilli() }.getOrDefault(0L)
    }

    private fun normalizeQuery(query: String): String {
        return query.trim().replace("\\s+".toRegex(), " ")
    }

    private fun authHeader(token: String): String {
        return if (token.startsWith("Bearer ", ignoreCase = true)) {
            token
        } else {
            "Bearer $token"
        }
    }

    private fun SearchHistoryItem.toUiItem(): SearchHistoryUiItem {
        return SearchHistoryUiItem(
            query = normalizeQuery(query),
            id = id,
            updatedAt = updatedAt ?: createdAt,
            resultCount = resultCount
        )
    }
}
