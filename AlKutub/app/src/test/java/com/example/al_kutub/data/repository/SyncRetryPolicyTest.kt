package com.example.al_kutub.data.repository

import org.junit.Assert.assertEquals
import org.junit.Assert.assertFalse
import org.junit.Assert.assertTrue
import org.junit.Test

class SyncRetryPolicyTest {

    @Test
    fun backoffMillis_growsExponentiallyAndCaps() {
        assertEquals(5_000L, SyncRetryPolicy.backoffMillis(0))
        assertEquals(10_000L, SyncRetryPolicy.backoffMillis(1))
        assertEquals(20_000L, SyncRetryPolicy.backoffMillis(2))
        assertEquals(5 * 60_000L, SyncRetryPolicy.backoffMillis(10))
    }

    @Test
    fun canRetry_falseBeforeBackoff_trueAfterBackoff() {
        val lastAttempt = 1_000_000L
        val retryCount = 2
        val backoff = SyncRetryPolicy.backoffMillis(retryCount)

        assertFalse(SyncRetryPolicy.canRetry(lastAttempt, retryCount, nowMillis = lastAttempt + backoff - 1))
        assertTrue(SyncRetryPolicy.canRetry(lastAttempt, retryCount, nowMillis = lastAttempt + backoff))
    }

    @Test
    fun nextRetryAt_nullWhenNoAttempt() {
        assertEquals(null, SyncRetryPolicy.nextRetryAt(lastAttemptAt = null, retryCount = 2))
    }
}
