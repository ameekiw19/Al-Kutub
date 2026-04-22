package com.example.al_kutub.data.repository

import org.junit.Assert.assertEquals
import org.junit.Test

class HistoryProgressConflictResolverTest {

    @Test
    fun resolvePage_picksGreaterProgress() {
        assertEquals(25, HistoryProgressConflictResolver.resolvePage(localPage = 25, remotePage = 10))
        assertEquals(30, HistoryProgressConflictResolver.resolvePage(localPage = 5, remotePage = 30))
    }

    @Test
    fun resolvePage_fallbacksToOneWhenInvalid() {
        assertEquals(1, HistoryProgressConflictResolver.resolvePage(localPage = 0, remotePage = null))
        assertEquals(1, HistoryProgressConflictResolver.resolvePage(localPage = -5, remotePage = 0))
    }

    @Test
    fun resolveTotalPages_respectsResolvedPage() {
        assertEquals(
            40,
            HistoryProgressConflictResolver.resolveTotalPages(localTotal = 20, remoteTotal = 40, resolvedPage = 10)
        )
        assertEquals(
            18,
            HistoryProgressConflictResolver.resolveTotalPages(localTotal = 0, remoteTotal = 0, resolvedPage = 18)
        )
    }
}
