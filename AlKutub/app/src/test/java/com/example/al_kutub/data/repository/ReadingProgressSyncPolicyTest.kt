package com.example.al_kutub.data.repository

import org.junit.Assert.assertFalse
import org.junit.Assert.assertTrue
import org.junit.Test

class ReadingProgressSyncPolicyTest {

    @Test
    fun shouldSync_whenForce_true() {
        val snapshot = ReadingProgressSyncPolicy.buildSnapshot(page = 5, totalPages = 100, nowMillis = 1_000L)
        assertTrue(
            ReadingProgressSyncPolicy.shouldSyncRemote(
                lastSnapshot = snapshot,
                page = 6,
                totalPages = 100,
                force = true,
                nowMillis = 1_001L
            )
        )
    }

    @Test
    fun shouldSync_whenSignificantPageJump() {
        val snapshot = ReadingProgressSyncPolicy.buildSnapshot(page = 10, totalPages = 120, nowMillis = 1_000L)
        assertTrue(
            ReadingProgressSyncPolicy.shouldSyncRemote(
                lastSnapshot = snapshot,
                page = 13,
                totalPages = 120,
                force = false,
                nowMillis = 1_500L
            )
        )
    }

    @Test
    fun shouldSync_whenIntervalElapsed() {
        val snapshot = ReadingProgressSyncPolicy.buildSnapshot(page = 10, totalPages = 120, nowMillis = 1_000L)
        assertTrue(
            ReadingProgressSyncPolicy.shouldSyncRemote(
                lastSnapshot = snapshot,
                page = 11,
                totalPages = 120,
                force = false,
                nowMillis = 21_100L
            )
        )
    }

    @Test
    fun shouldNotSync_whenSmallChangeAndIntervalNotElapsed() {
        val snapshot = ReadingProgressSyncPolicy.buildSnapshot(page = 10, totalPages = 120, nowMillis = 1_000L)
        assertFalse(
            ReadingProgressSyncPolicy.shouldSyncRemote(
                lastSnapshot = snapshot,
                page = 11,
                totalPages = 120,
                force = false,
                nowMillis = 5_000L
            )
        )
    }
}
