package com.example.al_kutub.model

import org.junit.Assert.assertEquals
import org.junit.Assert.assertFalse
import org.junit.Assert.assertTrue
import org.junit.Test

class SearchFilterTest {

    @Test
    fun `hasActiveFilters false when default filter`() {
        val filter = SearchFilter()
        assertFalse(filter.hasActiveFilters())
    }

    @Test
    fun `hasActiveFilters true when query exists`() {
        val filter = SearchFilter(query = "fiqih")
        assertTrue(filter.hasActiveFilters())
    }

    @Test
    fun `getFilterCount counts active fields`() {
        val filter = SearchFilter(
            query = "hadis",
            categories = listOf("hadis"),
            languages = listOf("Arab"),
            sortBy = SortOption.VIEWS
        )

        assertEquals(3, filter.getFilterCount())
    }
}
