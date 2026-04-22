package com.example.al_kutub.model

import com.google.gson.annotations.SerializedName

data class SearchFilter(
    @SerializedName("query")
    val query: String = "",
    
    @SerializedName("categories")
    val categories: List<String> = emptyList(),
    
    @SerializedName("authors")
    val authors: List<String> = emptyList(),
    
    @SerializedName("languages")
    val languages: List<String> = emptyList(),

    @SerializedName("sort_by")
    val sortBy: SortOption = SortOption.RELEVANCE,

    @SerializedName("sort_order")
    val sortOrder: SortOrder = SortOrder.DESC
) {
    fun hasActiveFilters(): Boolean {
        return query.isNotBlank() || hasNonQueryFilters()
    }

    fun hasNonQueryFilters(): Boolean {
        return categories.isNotEmpty() ||
            authors.isNotEmpty() ||
            languages.isNotEmpty() ||
            sortBy != SortOption.RELEVANCE ||
            sortOrder != SortOrder.DESC
    }

    fun getFilterCount(): Int {
        var count = 0
        if (categories.isNotEmpty()) count++
        if (authors.isNotEmpty()) count++
        if (languages.isNotEmpty()) count++
        if (sortBy != SortOption.RELEVANCE || sortOrder != SortOrder.DESC) count++
        return count
    }

    fun toQueryMap(): Map<String, String> {
        val params = mutableMapOf<String, String>()

        if (query.isNotBlank()) params["search"] = query.trim()
        if (categories.isNotEmpty()) params["categories"] = categories.joinToString(",")
        if (authors.isNotEmpty()) params["authors"] = authors.joinToString(",")
        if (languages.isNotEmpty()) params["languages"] = languages.joinToString(",")
        params["sort_by"] = sortBy.value
        params["sort_order"] = sortOrder.value

        return params
    }

    fun toHistoryFiltersMap(): Map<String, Any> {
        val params = mutableMapOf<String, Any>()
        if (categories.isNotEmpty()) params["categories"] = categories
        if (authors.isNotEmpty()) params["authors"] = authors
        if (languages.isNotEmpty()) params["languages"] = languages
        params["sort_by"] = sortBy.value
        params["sort_order"] = sortOrder.value
        return params
    }
}

enum class SortOption(val value: String, val label: String) {
    RELEVANCE("relevance", "Relevansi"),
    TITLE_ASC("title_asc", "Judul A-Z"),
    TITLE_DESC("title_desc", "Judul Z-A"),
    AUTHOR_ASC("author_asc", "Penulis A-Z"),
    AUTHOR_DESC("author_desc", "Penulis Z-A"),
    NEWEST("newest", "Terbaru"),
    OLDEST("oldest", "Terlama"),
    VIEWS("views", "Terpopuler"),
    DOWNLOADS("downloads", "Terbanyak Diunduh");

    companion object {
        fun fromValue(value: String?): SortOption {
            val normalized = value?.trim()?.lowercase().orEmpty()
            return entries.firstOrNull { it.value == normalized }
                ?: when (normalized) {
                    "latest" -> NEWEST
                    "title" -> TITLE_ASC
                    "author" -> AUTHOR_ASC
                    else -> RELEVANCE
                }
        }
    }
}

enum class SortOrder(val value: String) {
    ASC("asc"),
    DESC("desc");

    companion object {
        fun fromValue(value: String?): SortOrder {
            return if (value.equals("asc", ignoreCase = true)) ASC else DESC
        }
    }
}

data class SearchSuggestion(
    @SerializedName("id")
    val id: String = "",

    @SerializedName("text")
    val text: String = "",

    @SerializedName("type")
    val type: SuggestionType = SuggestionType.QUERY,

    @SerializedName("count")
    val count: Int? = null
)

enum class SuggestionType {
    @SerializedName("query")
    QUERY,

    @SerializedName("category")
    CATEGORY,

    @SerializedName("author")
    AUTHOR,

    @SerializedName("language")
    LANGUAGE
}
