package com.example.al_kutub.data.repository

import kotlin.math.abs

internal object ReadingProgressSyncPolicy {
    private const val MIN_SYNC_INTERVAL_MILLIS = 20_000L
    private const val SIGNIFICANT_PAGE_JUMP = 3

    data class Snapshot(
        val page: Int,
        val totalPages: Int,
        val syncedAtMillis: Long
    )

    fun shouldSyncRemote(
        lastSnapshot: Snapshot?,
        page: Int,
        totalPages: Int,
        force: Boolean,
        nowMillis: Long = System.currentTimeMillis()
    ): Boolean {
        if (force) return true

        val normalizedPage = page.coerceAtLeast(1)
        val normalizedTotal = totalPages.coerceAtLeast(normalizedPage)
        val previous = lastSnapshot ?: return true

        val intervalElapsed = nowMillis - previous.syncedAtMillis >= MIN_SYNC_INTERVAL_MILLIS
        val significantJump = abs(normalizedPage - previous.page) >= SIGNIFICANT_PAGE_JUMP
        val totalChanged = normalizedTotal != previous.totalPages
        val justCompleted = normalizedTotal > 0 &&
            normalizedPage >= normalizedTotal &&
            previous.page < previous.totalPages

        return intervalElapsed || significantJump || totalChanged || justCompleted
    }

    fun buildSnapshot(
        page: Int,
        totalPages: Int,
        nowMillis: Long = System.currentTimeMillis()
    ): Snapshot {
        val normalizedPage = page.coerceAtLeast(1)
        val normalizedTotal = totalPages.coerceAtLeast(normalizedPage)
        return Snapshot(
            page = normalizedPage,
            totalPages = normalizedTotal,
            syncedAtMillis = nowMillis
        )
    }
}
