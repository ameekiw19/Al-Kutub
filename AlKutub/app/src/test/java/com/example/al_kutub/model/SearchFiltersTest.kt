package com.example.al_kutub.model

import org.junit.Assert.assertEquals
import org.junit.Assert.assertFalse
import org.junit.Assert.assertTrue
import org.junit.Test

class SearchFiltersTest {

    @Test
    fun `hasQueryOrFilters false when query and filters empty`() {
        val filters = SearchFilters()

        assertFalse(filters.hasQueryOrFilters())
        assertFalse(filters.hasNonQueryFilters())
    }

    @Test
    fun `hasQueryOrFilters true for filter only search`() {
        val filters = SearchFilters(categories = listOf("fiqih"))

        assertTrue(filters.hasQueryOrFilters())
        assertTrue(filters.hasNonQueryFilters())
    }

    @Test
    fun `toHistoryFiltersMap only contains active fields`() {
        val filters = SearchFilters(
            categories = listOf("aqidah"),
            minYear = 2020,
            sortBy = "latest",
            sortOrder = "asc",
            includeDownloaded = true
        )

        val map = filters.toHistoryFiltersMap()

        assertEquals(listOf("aqidah"), map["categories"])
        assertEquals(2020, map["min_year"])
        assertEquals("latest", map["sort_by"])
        assertEquals("asc", map["sort_order"])
        assertEquals(true, map["include_downloaded"])
        assertFalse(map.containsKey("authors"))
    }
}
