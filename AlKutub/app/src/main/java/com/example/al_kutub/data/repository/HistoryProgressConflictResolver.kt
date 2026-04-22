package com.example.al_kutub.data.repository

internal object HistoryProgressConflictResolver {
    fun resolvePage(localPage: Int?, remotePage: Int?): Int {
        val normalizedLocal = localPage?.coerceAtLeast(1) ?: 1
        val normalizedRemote = remotePage?.coerceAtLeast(1) ?: 1
        return maxOf(normalizedLocal, normalizedRemote)
    }

    fun resolveTotalPages(localTotal: Int?, remoteTotal: Int?, resolvedPage: Int): Int {
        val normalizedLocal = localTotal?.coerceAtLeast(1) ?: 1
        val normalizedRemote = remoteTotal?.coerceAtLeast(1) ?: 1
        return maxOf(normalizedLocal, normalizedRemote, resolvedPage.coerceAtLeast(1))
    }
}
