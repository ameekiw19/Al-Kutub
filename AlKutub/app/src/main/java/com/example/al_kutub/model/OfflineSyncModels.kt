package com.example.al_kutub.model

data class DomainSyncState(
    val domain: SyncDomain,
    val pendingCount: Int = 0,
    val failedCount: Int = 0,
    val lastSyncedAt: Long? = null,
    val lastError: String? = null
) {
    val isSynced: Boolean
        get() = pendingCount == 0 && failedCount == 0 && lastError.isNullOrBlank()
}

data class SyncSummary(
    val bookmark: DomainSyncState = DomainSyncState(SyncDomain.BOOKMARK),
    val history: DomainSyncState = DomainSyncState(SyncDomain.HISTORY),
    val pageMarker: DomainSyncState = DomainSyncState(SyncDomain.PAGE_MARKER),
    val notes: DomainSyncState = DomainSyncState(SyncDomain.NOTES),
    val isSyncRunning: Boolean = false,
    val authRequired: Boolean = false
) {
    val totalPending: Int
        get() = bookmark.pendingCount + history.pendingCount + pageMarker.pendingCount + notes.pendingCount

    val totalFailed: Int
        get() = bookmark.failedCount + history.failedCount + pageMarker.failedCount + notes.failedCount
}

enum class SyncDomain(val key: String, val title: String) {
    BOOKMARK("bookmark", "Bookmark"),
    HISTORY("history", "Riwayat"),
    PAGE_MARKER("page_marker", "Marker Halaman"),
    NOTES("notes", "Catatan");

    companion object {
        fun fromKey(value: String): SyncDomain {
            return values().firstOrNull { it.key == value } ?: NOTES
        }
    }
}

data class SyncOperationUiState(
    val id: Long,
    val domain: SyncDomain,
    val operationType: String,
    val status: String,
    val retryCount: Int,
    val createdAt: Long,
    val updatedAt: Long,
    val lastAttemptAt: Long?,
    val nextRetryAt: Long?,
    val lastError: String?
)

data class DownloadTaskUiState(
    val taskId: Long,
    val kitabId: Int,
    val title: String,
    val status: String,
    val downloadedBytes: Long,
    val totalBytes: Long,
    val progressPercent: Int,
    val errorMessage: String? = null
) {
    val isTerminal: Boolean
        get() = status == "completed" || status == "canceled"
}
