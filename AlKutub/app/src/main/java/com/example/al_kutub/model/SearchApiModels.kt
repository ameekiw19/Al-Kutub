package com.example.al_kutub.model

import com.google.gson.annotations.SerializedName

data class SearchSuggestionsResponse(
    @SerializedName("success") val success: Boolean,
    @SerializedName("message") val message: String,
    @SerializedName("data") val data: List<SearchSuggestion>
)

data class SearchHistoryResponse(
    @SerializedName("success") val success: Boolean,
    @SerializedName("message") val message: String,
    @SerializedName("data") val data: List<SearchHistoryItem>
)

data class SearchHistorySaveResponse(
    @SerializedName("success") val success: Boolean,
    @SerializedName("message") val message: String,
    @SerializedName("data") val data: SearchHistoryItem
)

data class SearchHistoryItem(
    @SerializedName("id") val id: Int,
    @SerializedName("query") val query: String,
    @SerializedName("created_at") val createdAt: String? = null,
    @SerializedName("updated_at") val updatedAt: String? = null,
    @SerializedName("result_count") val resultCount: Int = 0,
    @SerializedName("filters") val filters: Map<String, Any>? = null
)

data class SearchHistoryUiItem(
    val query: String,
    val id: Int? = null,
    val updatedAt: String? = null,
    val resultCount: Int? = null
)

data class SaveSearchHistoryRequest(
    @SerializedName("query") val query: String,
    @SerializedName("filters") val filters: Map<String, Any> = emptyMap(),
    @SerializedName("result_count") val resultCount: Int? = null
)

data class SearchAnalytics(
    @SerializedName("popular_queries") val popularQueries: List<PopularQuery>,
    @SerializedName("trending_categories") val trendingCategories: List<TrendingCategory>,
    @SerializedName("suggested_filters") val suggestedFilters: Map<String, List<String>>
)

data class PopularQuery(
    @SerializedName("query") val query: String,
    @SerializedName("count") val count: Int,
    @SerializedName("trend") val trend: String // "up", "down", "stable"
)

data class TrendingCategory(
    @SerializedName("category") val category: String,
    @SerializedName("count") val count: Int,
    @SerializedName("growth") val growth: Double
)
