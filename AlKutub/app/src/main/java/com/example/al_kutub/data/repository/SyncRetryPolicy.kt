package com.example.al_kutub.data.repository

internal object SyncRetryPolicy {
    private const val BASE_BACKOFF_MS = 5_000L
    private const val MAX_BACKOFF_MS = 5 * 60_000L

    fun backoffMillis(retryCount: Int): Long {
        val safeRetry = retryCount.coerceAtLeast(0).coerceAtMost(8)
        val exponential = BASE_BACKOFF_MS * (1L shl safeRetry)
        return exponential.coerceAtMost(MAX_BACKOFF_MS)
    }

    fun nextRetryAt(lastAttemptAt: Long?, retryCount: Int): Long? {
        val attempt = lastAttemptAt ?: return null
        return attempt + backoffMillis(retryCount)
    }

    fun canRetry(lastAttemptAt: Long?, retryCount: Int, nowMillis: Long = System.currentTimeMillis()): Boolean {
        val next = nextRetryAt(lastAttemptAt, retryCount) ?: return true
        return nowMillis >= next
    }
}
