package com.example.al_kutub.model

data class SearchFilters(
    val query: String = "",
    val categories: List<String> = emptyList(),
    val authors: List<String> = emptyList(),
    val languages: List<String> = emptyList(),
    val minYear: Int? = null,
    val maxYear: Int? = null,
    val minPages: Int? = null,
    val maxPages: Int? = null,
    val sortBy: String = "relevance",
    val sortOrder: String = "desc",
    val contentSearch: Boolean = false,
    val includeDownloaded: Boolean = false
) {
    fun hasQueryOrFilters(): Boolean {
        return query.isNotBlank()
            || categories.isNotEmpty()
            || authors.isNotEmpty()
            || languages.isNotEmpty()
            || minYear != null
            || maxYear != null
            || minPages != null
            || maxPages != null
            || contentSearch
            || includeDownloaded
    }

    fun hasNonQueryFilters(): Boolean {
        return categories.isNotEmpty()
            || authors.isNotEmpty()
            || languages.isNotEmpty()
            || minYear != null
            || maxYear != null
            || minPages != null
            || maxPages != null
            || contentSearch
            || includeDownloaded
    }

    fun toHistoryFiltersMap(): Map<String, Any> {
        val filters = mutableMapOf<String, Any>()

        if (categories.isNotEmpty()) filters["categories"] = categories
        if (authors.isNotEmpty()) filters["authors"] = authors
        if (languages.isNotEmpty()) filters["languages"] = languages
        if (minYear != null) filters["min_year"] = minYear
        if (maxYear != null) filters["max_year"] = maxYear
        if (minPages != null) filters["min_pages"] = minPages
        if (maxPages != null) filters["max_pages"] = maxPages
        filters["sort_by"] = sortBy
        filters["sort_order"] = sortOrder
        if (contentSearch) filters["content_search"] = true
        if (includeDownloaded) filters["include_downloaded"] = true

        return filters
    }
}
