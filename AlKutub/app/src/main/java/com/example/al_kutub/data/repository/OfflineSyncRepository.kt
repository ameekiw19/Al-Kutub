package com.example.al_kutub.data.repository

import android.util.Log
import com.example.al_kutub.api.ApiService
import com.example.al_kutub.data.dao.ReadingProgressDao
import com.example.al_kutub.data.local.dao.CachedBookmarkDao
import com.example.al_kutub.data.local.dao.CachedHistoryDao
import com.example.al_kutub.data.local.dao.CachedReadingNoteDao
import com.example.al_kutub.data.local.dao.PageBookmarkDao
import com.example.al_kutub.data.local.dao.SyncOperationDao
import com.example.al_kutub.data.local.entity.CachedBookmarkEntity
import com.example.al_kutub.data.local.entity.CachedHistoryEntity
import com.example.al_kutub.data.local.entity.PageBookmarkEntity
import com.example.al_kutub.data.local.entity.CachedReadingNoteEntity
import com.example.al_kutub.data.local.entity.SyncOperationEntity
import com.example.al_kutub.model.BookmarkItem
import com.example.al_kutub.model.CreateReadingNoteRequest
import com.example.al_kutub.model.DomainSyncState
import com.example.al_kutub.model.HistoryItemData
import com.example.al_kutub.model.Kitab
import com.example.al_kutub.model.KitabData
import com.example.al_kutub.model.ReadingNoteData
import com.example.al_kutub.model.SyncDomain
import com.example.al_kutub.model.SyncOperationUiState
import com.example.al_kutub.model.SyncSummary
import com.example.al_kutub.model.UpdateReadingNoteRequest
import com.example.al_kutub.model.ReadingProgress
import com.example.al_kutub.utils.SessionManager
import com.google.gson.Gson
import com.google.gson.reflect.TypeToken
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.flowOf
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.sync.Mutex
import java.time.Instant
import java.util.UUID
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class OfflineSyncRepository @Inject constructor(
    private val apiService: ApiService,
    private val sessionManager: SessionManager,
    private val syncOperationDao: SyncOperationDao,
    private val cachedBookmarkDao: CachedBookmarkDao,
    private val cachedHistoryDao: CachedHistoryDao,
    private val cachedReadingNoteDao: CachedReadingNoteDao,
    private val pageBookmarkDao: PageBookmarkDao,
    private val readingProgressDao: ReadingProgressDao,
    private val gson: Gson
) {
    private val tag = "OfflineSyncRepository"
    private val syncMutex = Mutex()

    private val _syncSummary = MutableStateFlow(SyncSummary())
    val syncSummary: StateFlow<SyncSummary> = _syncSummary

    companion object {
        const val OP_BOOKMARK_TOGGLE = "bookmark_toggle"
        const val OP_BOOKMARK_DELETE = "bookmark_delete"
        const val OP_BOOKMARK_CLEAR_ALL = "bookmark_clear_all"
        const val OP_HISTORY_UPSERT = "history_upsert"
        const val OP_HISTORY_DELETE = "history_delete"
        const val OP_HISTORY_CLEAR_ALL = "history_clear_all"
        const val OP_PAGE_MARKER_UPSERT = "page_marker_upsert"
        const val OP_PAGE_MARKER_DELETE = "page_marker_delete"
        const val OP_NOTE_CREATE = "note_create"
        const val OP_NOTE_UPDATE = "note_update"
        const val OP_NOTE_DELETE = "note_delete"
    }

    suspend fun refreshSummary() {
        val userId = currentUserId()
        if (userId <= 0) {
            _syncSummary.value = SyncSummary()
            return
        }
        val bookmark = buildDomainState(userId, SyncDomain.BOOKMARK)
        val history = buildDomainState(userId, SyncDomain.HISTORY)
        val pageMarker = buildDomainState(userId, SyncDomain.PAGE_MARKER)
        val notes = buildDomainState(userId, SyncDomain.NOTES)
        _syncSummary.value = _syncSummary.value.copy(
            bookmark = bookmark,
            history = history,
            pageMarker = pageMarker,
            notes = notes
        )
    }

    suspend fun enqueueBookmarkToggle(kitabId: Int) {
        enqueue(
            domain = SyncDomain.BOOKMARK,
            operationType = OP_BOOKMARK_TOGGLE,
            payload = mapOf("kitab_id" to kitabId)
        )
    }

    suspend fun enqueueBookmarkDelete(kitabId: Int) {
        enqueue(
            domain = SyncDomain.BOOKMARK,
            operationType = OP_BOOKMARK_DELETE,
            payload = mapOf("kitab_id" to kitabId)
        )
    }

    suspend fun enqueueBookmarkClearAll() {
        enqueue(
            domain = SyncDomain.BOOKMARK,
            operationType = OP_BOOKMARK_CLEAR_ALL,
            payload = emptyMap<String, Any>()
        )
    }

    suspend fun enqueueHistoryUpsert(
        kitabId: Int,
        currentPage: Int? = null,
        totalPages: Int? = null,
        lastPosition: String? = null,
        readingTimeMinutes: Int? = null,
        readingTimeAdded: Int? = null,
        clientUpdatedAt: String? = null
    ) {
        val userId = currentUserId()
        if (userId <= 0) return
        val resolvedClientUpdatedAt = clientUpdatedAt ?: Instant.now().toString()

        val payload = mapOf(
            "kitab_id" to kitabId,
            "current_page" to currentPage,
            "total_pages" to totalPages,
            "last_position" to lastPosition,
            "reading_time_minutes" to readingTimeMinutes,
            "reading_time_added" to readingTimeAdded,
            "client_updated_at" to resolvedClientUpdatedAt
        )

        val activeOps = syncOperationDao.getActiveByDomainAndType(
            userId = userId,
            domain = SyncDomain.HISTORY.key,
            operationType = OP_HISTORY_UPSERT
        )
        val existing = HistorySyncQueueDeduper.findMatch(activeOps, kitabId, gson)

        if (existing != null) {
            val duplicateOps = activeOps.filter { it.id != existing.id }
                .filter { HistorySyncQueueDeduper.extractKitabId(it.payloadJson, gson) == kitabId }
            duplicateOps.forEach { syncOperationDao.delete(it) }

            syncOperationDao.update(
                existing.copy(
                    payloadJson = gson.toJson(payload),
                    status = SyncOperationEntity.STATUS_PENDING,
                    retryCount = 0,
                    lastError = null,
                    updatedAt = System.currentTimeMillis(),
                    lastAttemptAt = null
                )
            )
            refreshSummary()
            return
        }

        enqueue(
            domain = SyncDomain.HISTORY,
            operationType = OP_HISTORY_UPSERT,
            payload = payload
        )
    }

    suspend fun enqueueHistoryDelete(historyId: Int) {
        enqueue(
            domain = SyncDomain.HISTORY,
            operationType = OP_HISTORY_DELETE,
            payload = mapOf("history_id" to historyId)
        )
    }

    suspend fun enqueueHistoryClearAll() {
        enqueue(
            domain = SyncDomain.HISTORY,
            operationType = OP_HISTORY_CLEAR_ALL,
            payload = emptyMap<String, Any>()
        )
    }

    suspend fun enqueuePageMarkerUpsert(
        kitabId: Int,
        pageNumber: Int,
        label: String,
        clientUpdatedAt: String? = null
    ) {
        val resolvedClientUpdatedAt = clientUpdatedAt ?: Instant.now().toString()
        enqueue(
            domain = SyncDomain.PAGE_MARKER,
            operationType = OP_PAGE_MARKER_UPSERT,
            payload = mapOf(
                "kitab_id" to kitabId,
                "page_number" to pageNumber,
                "label" to label,
                "client_updated_at" to resolvedClientUpdatedAt
            )
        )
    }

    suspend fun enqueuePageMarkerDelete(kitabId: Int, pageNumber: Int) {
        enqueue(
            domain = SyncDomain.PAGE_MARKER,
            operationType = OP_PAGE_MARKER_DELETE,
            payload = mapOf(
                "kitab_id" to kitabId,
                "page_number" to pageNumber
            )
        )
    }

    suspend fun enqueueReadingNoteCreate(request: CreateReadingNoteRequest): String {
        val clientRequestId = request.clientRequestId ?: UUID.randomUUID().toString().replace("-", "").take(16)
        val clientUpdatedAt = request.clientUpdatedAt ?: Instant.now().toString()
        enqueue(
            domain = SyncDomain.NOTES,
            operationType = OP_NOTE_CREATE,
            payload = request.copy(
                clientRequestId = clientRequestId,
                clientUpdatedAt = clientUpdatedAt
            ),
            clientRequestId = clientRequestId
        )
        return clientRequestId
    }

    suspend fun enqueueReadingNoteUpdate(noteId: Int, request: UpdateReadingNoteRequest) {
        enqueue(
            domain = SyncDomain.NOTES,
            operationType = OP_NOTE_UPDATE,
            payload = mapOf(
                "note_id" to noteId,
                "note_content" to request.noteContent,
                "page_number" to request.pageNumber,
                "highlighted_text" to request.highlightedText,
                "note_color" to request.noteColor,
                "is_private" to request.isPrivate,
                "client_updated_at" to (request.clientUpdatedAt ?: Instant.now().toString())
            )
        )
    }

    suspend fun enqueueReadingNoteDelete(noteId: Int) {
        enqueue(
            domain = SyncDomain.NOTES,
            operationType = OP_NOTE_DELETE,
            payload = mapOf("note_id" to noteId)
        )
    }

    suspend fun processQueue(): Result<Unit> {
        if (!syncMutex.tryLock()) {
            return Result.success(Unit)
        }
        return try {
            val token = sessionManager.getToken()
            if (token.isNullOrBlank()) {
                _syncSummary.value = _syncSummary.value.copy(authRequired = true, isSyncRunning = false)
                return Result.failure(IllegalStateException("AUTH_401"))
            }

            _syncSummary.value = _syncSummary.value.copy(isSyncRunning = true, authRequired = false)

            val now = System.currentTimeMillis()
            val operations = syncOperationDao.getProcessableOperations().filter {
                it.status == SyncOperationEntity.STATUS_PENDING ||
                    SyncRetryPolicy.canRetry(it.lastAttemptAt, it.retryCount, now)
            }
            for (operation in operations) {
                val userId = currentUserId()
                if (operation.userId != userId || userId <= 0) continue
                markProcessing(operation)

                try {
                    executeOperation(operation, "Bearer $token")
                    syncOperationDao.update(
                        operation.copy(
                            status = SyncOperationEntity.STATUS_DONE,
                            lastError = null,
                            updatedAt = System.currentTimeMillis(),
                            lastAttemptAt = System.currentTimeMillis()
                        )
                    )
                } catch (throwable: Throwable) {
                    val isAuthError = throwable.message?.contains("AUTH_401") == true
                    syncOperationDao.update(
                        operation.copy(
                            status = SyncOperationEntity.STATUS_FAILED,
                            retryCount = operation.retryCount + 1,
                            lastError = throwable.message,
                            updatedAt = System.currentTimeMillis(),
                            lastAttemptAt = System.currentTimeMillis()
                        )
                    )
                    if (isAuthError) {
                        sessionManager.clearAuthSession()
                        _syncSummary.value = _syncSummary.value.copy(authRequired = true)
                        break
                    }
                }
            }

            try {
                pullRemoteSnapshot(authHeader = "Bearer $token")
            } catch (pullError: Exception) {
                if (pullError.message?.contains("AUTH_401") == true) {
                    sessionManager.clearAuthSession()
                    _syncSummary.value = _syncSummary.value.copy(authRequired = true, isSyncRunning = false)
                    return Result.failure(IllegalStateException("AUTH_401"))
                }
                Log.w(tag, "pullRemoteSnapshot gagal: ${pullError.message}")
            }

            syncOperationDao.deleteDoneOperations()
            refreshSummary()
            _syncSummary.value = _syncSummary.value.copy(isSyncRunning = false)
            Result.success(Unit)
        } catch (e: CancellationException) {
            Log.d(tag, "processQueue cancelled: ${e.message}")
            _syncSummary.value = _syncSummary.value.copy(isSyncRunning = false)
            Result.success(Unit)
        } catch (e: Exception) {
            Log.e(tag, "processQueue failed", e)
            _syncSummary.value = _syncSummary.value.copy(isSyncRunning = false)
            refreshSummary()
            Result.failure(e)
        } finally {
            if (syncMutex.isLocked) {
                syncMutex.unlock()
            }
        }
    }

    suspend fun getPendingOperationCount(): Int {
        val userId = currentUserId()
        if (userId <= 0) return 0
        return syncOperationDao.countActiveOperations(userId)
    }

    fun observeOperations(limit: Int = 25): Flow<List<SyncOperationUiState>> {
        val userId = currentUserId()
        if (userId <= 0) return flowOf(emptyList())
        return syncOperationDao.observeUserOperations(userId).map { operations ->
            operations
                .take(limit.coerceIn(1, 100))
                .map { operation ->
                    val nextRetryAt = if (operation.status == SyncOperationEntity.STATUS_FAILED) {
                        SyncRetryPolicy.nextRetryAt(operation.lastAttemptAt, operation.retryCount)
                    } else {
                        null
                    }
                    SyncOperationUiState(
                        id = operation.id,
                        domain = SyncDomain.fromKey(operation.domain),
                        operationType = operation.operationType,
                        status = operation.status,
                        retryCount = operation.retryCount,
                        createdAt = operation.createdAt,
                        updatedAt = operation.updatedAt,
                        lastAttemptAt = operation.lastAttemptAt,
                        nextRetryAt = nextRetryAt,
                        lastError = operation.lastError
                    )
                }
        }
    }

    suspend fun clearOperationQueue() {
        syncOperationDao.clearAll()
        refreshSummary()
    }

    suspend fun cacheBookmarks(items: List<BookmarkItem>) {
        val userId = currentUserId()
        if (userId <= 0) return
        val entities = items.map {
            CachedBookmarkEntity(
                userId = userId,
                kitabId = it.idKitab,
                bookmarkId = it.idBookmark,
                createdAt = it.createdAt,
                judul = it.kitab?.judul.orEmpty(),
                penulis = it.kitab?.penulis.orEmpty(),
                cover = it.kitab?.cover,
                kategori = it.kitab?.kategori
            )
        }
        cachedBookmarkDao.clearForUser(userId)
        cachedBookmarkDao.upsertAll(entities)
    }

    suspend fun getCachedBookmarks(): List<BookmarkItem> {
        val userId = currentUserId()
        if (userId <= 0) return emptyList()
        return cachedBookmarkDao.getForUser(userId).map {
            BookmarkItem(
                idBookmark = it.bookmarkId ?: 0,
                userId = userId,
                idKitab = it.kitabId,
                createdAt = it.createdAt,
                updatedAt = it.createdAt,
                kitab = Kitab(
                    idKitab = it.kitabId,
                    judul = it.judul,
                    penulis = it.penulis,
                    kategori = it.kategori.orEmpty(),
                    cover = it.cover.orEmpty()
                )
            )
        }
    }

    suspend fun cacheHistories(items: List<HistoryItemData>) {
        val userId = currentUserId()
        if (userId <= 0) return
        val entities = items.map {
            CachedHistoryEntity(
                userId = userId,
                historyId = it.id,
                kitabId = it.kitab_id,
                judul = it.kitab?.judul.orEmpty(),
                penulis = it.kitab?.penulis.orEmpty(),
                cover = it.kitab?.cover,
                kategori = it.kitab?.kategori,
                timeAgo = it.time_ago,
                lastReadAt = it.last_read_at,
                currentPage = it.current_page,
                totalPages = it.total_pages,
                readingTimeMinutes = it.reading_time_minutes
            )
        }
        cachedHistoryDao.clearForUser(userId)
        cachedHistoryDao.upsertAll(entities)
    }

    suspend fun getCachedHistories(): List<HistoryItemData> {
        val userId = currentUserId()
        if (userId <= 0) return emptyList()
        return cachedHistoryDao.getForUser(userId).map {
            HistoryItemData(
                id = it.historyId,
                kitab_id = it.kitabId,
                last_read_at = it.lastReadAt,
                time_ago = it.timeAgo,
                current_page = it.currentPage,
                total_pages = it.totalPages,
                reading_time_minutes = it.readingTimeMinutes,
                kitab = KitabData(
                    id = it.kitabId,
                    judul = it.judul,
                    penulis = it.penulis,
                    kategori = it.kategori.orEmpty(),
                    bahasa = "",
                    cover = it.cover.orEmpty(),
                    views = 0,
                    downloads = 0
                )
            )
        }
    }

    suspend fun cacheReadingNotes(items: List<ReadingNoteData>) {
        val userId = currentUserId()
        if (userId <= 0) return
        val pendingCreateOps = syncOperationDao.getActiveByDomainAndType(
            userId = userId,
            domain = SyncDomain.NOTES.key,
            operationType = OP_NOTE_CREATE
        )
        val pendingUpdateOps = syncOperationDao.getActiveByDomainAndType(
            userId = userId,
            domain = SyncDomain.NOTES.key,
            operationType = OP_NOTE_UPDATE
        )
        val pendingDeleteOps = syncOperationDao.getActiveByDomainAndType(
            userId = userId,
            domain = SyncDomain.NOTES.key,
            operationType = OP_NOTE_DELETE
        )
        val pendingCreateClientRequestIds = pendingCreateOps
            .mapNotNull { it.clientRequestId?.takeIf { requestId -> requestId.isNotBlank() } }
            .toSet()
        val pendingUpdateNoteIds = parsePendingNoteIds(pendingUpdateOps)
        val pendingDeleteNoteIds = parsePendingNoteIds(pendingDeleteOps)

        val existingLocal = cachedReadingNoteDao.getForUser(userId)
        val remoteEntities = items
            .map {
                val remoteUpdatedAtMillis = parseIsoToMillis(it.updatedAt) ?: System.currentTimeMillis()
                CachedReadingNoteEntity(
                    userId = userId,
                    noteId = it.id,
                    kitabId = it.kitabId,
                    kitabTitle = it.kitab?.judul,
                    noteContent = it.noteContent,
                    pageNumber = it.pageNumber,
                    highlightedText = it.highlightedText,
                    noteColor = it.noteColor,
                    isPrivate = it.isPrivate,
                    createdAt = it.createdAt,
                    remoteUpdatedAt = it.updatedAt,
                    clientRequestId = it.clientRequestId,
                    updatedAt = remoteUpdatedAtMillis
                )
            }
            .filter { remote -> remote.noteId !in pendingDeleteNoteIds }

        val mergedByNoteId = linkedMapOf<Int, CachedReadingNoteEntity>()
        remoteEntities.forEach { entity ->
            mergedByNoteId[entity.noteId] = entity
        }

        existingLocal.forEach { local ->
            val localClientRequestId = local.clientRequestId?.takeIf { it.isNotBlank() }
            when {
                local.noteId in pendingDeleteNoteIds -> Unit
                local.noteId in pendingUpdateNoteIds -> {
                    mergedByNoteId[local.noteId] = local
                }
                localClientRequestId != null && localClientRequestId in pendingCreateClientRequestIds -> {
                    val synced = remoteEntities.any { it.clientRequestId == localClientRequestId }
                    if (!synced) {
                        mergedByNoteId[local.noteId] = local
                    }
                }
            }
        }

        val merged = mergedByNoteId.values.sortedByDescending { entity -> entity.updatedAt }
        cachedReadingNoteDao.clearForUser(userId)
        if (merged.isNotEmpty()) {
            cachedReadingNoteDao.upsertAll(merged)
        }
    }

    suspend fun getCachedReadingNotes(): List<ReadingNoteData> {
        val userId = currentUserId()
        if (userId <= 0) return emptyList()
        return cachedReadingNoteDao.getForUser(userId).map {
            ReadingNoteData(
                id = it.noteId,
                userId = userId,
                kitabId = it.kitabId,
                noteContent = it.noteContent,
                pageNumber = it.pageNumber,
                highlightedText = it.highlightedText,
                noteColor = it.noteColor,
                isPrivate = it.isPrivate,
                createdAt = it.createdAt,
                updatedAt = it.remoteUpdatedAt,
                clientRequestId = it.clientRequestId,
                kitab = it.kitabTitle?.let { title ->
                    com.example.al_kutub.model.KitabNoteData(id = it.kitabId, judul = title)
                },
                user = null
            )
        }
    }

    suspend fun cacheLocalPendingReadingNote(
        kitabId: Int,
        kitabTitle: String?,
        noteContent: String,
        pageNumber: Int?,
        highlightedText: String?,
        noteColor: String,
        isPrivate: Boolean,
        clientRequestId: String
    ) {
        val userId = currentUserId()
        if (userId <= 0) return
        val localId = -(System.currentTimeMillis() % Int.MAX_VALUE).toInt()
        val nowIso = java.time.Instant.now().toString()
        cachedReadingNoteDao.upsert(
            CachedReadingNoteEntity(
                userId = userId,
                noteId = localId,
                kitabId = kitabId,
                kitabTitle = kitabTitle,
                noteContent = noteContent,
                pageNumber = pageNumber,
                highlightedText = highlightedText,
                noteColor = noteColor,
                isPrivate = isPrivate,
                createdAt = nowIso,
                remoteUpdatedAt = nowIso,
                clientRequestId = clientRequestId
            )
        )
    }

    suspend fun clearLocalCaches() {
        cachedBookmarkDao.clearAll()
        cachedHistoryDao.clearAll()
        cachedReadingNoteDao.clearAll()
    }

    private suspend fun enqueue(
        domain: SyncDomain,
        operationType: String,
        payload: Any,
        clientRequestId: String? = null
    ) {
        val userId = currentUserId()
        if (userId <= 0) return
        syncOperationDao.insert(
            SyncOperationEntity(
                userId = userId,
                domain = domain.key,
                operationType = operationType,
                payloadJson = gson.toJson(payload),
                clientRequestId = clientRequestId
            )
        )
        refreshSummary()
    }

    private suspend fun buildDomainState(userId: Int, domain: SyncDomain): DomainSyncState {
        return DomainSyncState(
            domain = domain,
            pendingCount = syncOperationDao.countPendingByDomain(userId, domain.key),
            failedCount = syncOperationDao.countFailedByDomain(userId, domain.key),
            lastSyncedAt = syncOperationDao.lastDoneAt(userId, domain.key),
            lastError = syncOperationDao.latestDomainError(userId, domain.key)
        )
    }

    private fun currentUserId(): Int = sessionManager.getUserId()

    private suspend fun markProcessing(operation: SyncOperationEntity) {
        syncOperationDao.update(
            operation.copy(
                status = SyncOperationEntity.STATUS_PROCESSING,
                updatedAt = System.currentTimeMillis()
            )
        )
    }

    private suspend fun executeOperation(operation: SyncOperationEntity, authHeader: String) {
        when (operation.operationType) {
            OP_BOOKMARK_TOGGLE -> {
                val kitabId = parseIntFromPayload(operation.payloadJson, "kitab_id")
                val response = apiService.toggleBookmark(kitabId, authHeader)
                if (!response.isSuccessful || response.body()?.status != "success") {
                    throw buildException("bookmark toggle", response.code(), response.message())
                }
            }

            OP_BOOKMARK_DELETE -> {
                val kitabId = parseIntFromPayload(operation.payloadJson, "kitab_id")
                val response = apiService.deleteBookmark(kitabId, authHeader)
                if (!response.isSuccessful || response.body()?.status != "success") {
                    throw buildException("bookmark delete", response.code(), response.message())
                }
            }

            OP_BOOKMARK_CLEAR_ALL -> {
                val response = apiService.clearAllBookmarks(authHeader)
                if (!response.isSuccessful || response.body()?.status != "success") {
                    throw buildException("bookmark clear all", response.code(), response.message())
                }
            }

            OP_HISTORY_UPSERT -> {
                val payload = parsePayloadMap(operation.payloadJson)
                val response = apiService.addOrUpdateHistory(
                    kitabId = payload["kitab_id"].asInt(),
                    currentPage = payload["current_page"].asIntOrNull(),
                    totalPages = payload["total_pages"].asIntOrNull(),
                    lastPosition = payload["last_position"].asStringOrNull(),
                    readingTimeMinutes = payload["reading_time_minutes"].asIntOrNull(),
                    readingTimeAdded = payload["reading_time_added"].asIntOrNull(),
                    clientUpdatedAt = payload["client_updated_at"].asStringOrNull(),
                    authorization = authHeader
                )
                if (!response.isSuccessful || response.body()?.success != true) {
                    throw buildException("history upsert", response.code(), response.message())
                }
            }

            OP_HISTORY_DELETE -> {
                val historyId = parseIntFromPayload(operation.payloadJson, "history_id")
                val response = apiService.deleteHistory(historyId, authHeader)
                if (!response.isSuccessful || response.body()?.success != true) {
                    throw buildException("history delete", response.code(), response.message())
                }
            }

            OP_HISTORY_CLEAR_ALL -> {
                val response = apiService.clearAllHistory(authHeader)
                if (!response.isSuccessful || response.body()?.success != true) {
                    throw buildException("history clear", response.code(), response.message())
                }
            }

            OP_PAGE_MARKER_UPSERT -> {
                val payload = parsePayloadMap(operation.payloadJson)
                val kitabId = payload["kitab_id"].asInt()
                val pageNumber = payload["page_number"].asInt()
                val label = payload["label"].asStringOrEmpty(default = "Halaman $pageNumber")
                val response = apiService.upsertPageBookmark(
                    kitabId = kitabId,
                    pageNumber = pageNumber,
                    label = label,
                    clientUpdatedAt = payload["client_updated_at"].asStringOrNull(),
                    authorization = authHeader
                )
                if (!response.isSuccessful || response.body()?.success != true) {
                    throw buildException("page marker upsert", response.code(), response.message())
                }

                val existing = pageBookmarkDao.getByPage(operation.userId, kitabId, pageNumber)
                if (existing != null) {
                    val now = System.currentTimeMillis()
                    pageBookmarkDao.update(
                        existing.copy(
                            label = label,
                            updatedAt = maxOf(existing.updatedAt, now),
                            isPendingSync = false,
                            lastSyncedAt = now
                        )
                    )
                }
            }

            OP_PAGE_MARKER_DELETE -> {
                val payload = parsePayloadMap(operation.payloadJson)
                val kitabId = payload["kitab_id"].asInt()
                val pageNumber = payload["page_number"].asInt()
                val response = apiService.deletePageBookmark(
                    kitabId = kitabId,
                    pageNumber = pageNumber,
                    authorization = authHeader
                )
                if (!response.isSuccessful || response.body()?.success != true) {
                    throw buildException("page marker delete", response.code(), response.message())
                }
            }

            OP_NOTE_CREATE -> {
                val request = gson.fromJson(operation.payloadJson, CreateReadingNoteRequest::class.java)
                val response = apiService.createReadingNote(request, authHeader)
                if (!response.isSuccessful || response.body()?.success != true) {
                    throw buildException("note create", response.code(), response.message())
                }
            }

            OP_NOTE_UPDATE -> {
                val payload = parsePayloadMap(operation.payloadJson)
                val noteId = payload["note_id"].asInt()
                val response = apiService.updateReadingNote(
                    noteId = noteId,
                    request = UpdateReadingNoteRequest(
                        noteContent = payload["note_content"].asStringOrEmpty(),
                        pageNumber = payload["page_number"].asIntOrNull(),
                        highlightedText = payload["highlighted_text"].asStringOrNull(),
                        noteColor = payload["note_color"].asStringOrEmpty(default = "#FFFF00"),
                        isPrivate = payload["is_private"].asBoolean(default = true),
                        clientUpdatedAt = payload["client_updated_at"].asStringOrNull()
                    ),
                    authorization = authHeader
                )
                if (!response.isSuccessful || response.body()?.success != true) {
                    throw buildException("note update", response.code(), response.message())
                }
            }

            OP_NOTE_DELETE -> {
                val noteId = parseIntFromPayload(operation.payloadJson, "note_id")
                val response = apiService.deleteReadingNote(noteId, authHeader)
                if (!response.isSuccessful || response.body()?.success != true) {
                    throw buildException("note delete", response.code(), response.message())
                }
            }
        }
    }

    private suspend fun pullRemoteSnapshot(authHeader: String) {
        val userId = currentUserId()
        if (userId <= 0) return

        syncBookmarksFromRemote(authHeader)
        syncHistoriesFromRemote(authHeader, userId)
        syncPageMarkersFromRemote(authHeader, userId)
        syncReadingNotesFromRemote(authHeader)
    }

    private suspend fun syncBookmarksFromRemote(authHeader: String) {
        val response = apiService.getAllBookmarks(authHeader)
        if (response.code() == 401) {
            throw IllegalStateException("AUTH_401: bookmark pull unauthorized")
        }
        if (response.isSuccessful && response.body()?.status == "success") {
            cacheBookmarks(response.body()?.data.orEmpty())
        }
    }

    private suspend fun syncHistoriesFromRemote(authHeader: String, userId: Int) {
        val response = apiService.getHistories(authHeader)
        if (response.code() == 401) {
            throw IllegalStateException("AUTH_401: history pull unauthorized")
        }
        val body = response.body()
        if (!response.isSuccessful || body?.success != true || body.data == null) return

        val items = body.data.raw_histories.orEmpty()
        cacheHistories(items)
        items.forEach { item ->
            applyRemoteHistoryToLocalProgress(userId, item)
        }
    }

    private suspend fun syncPageMarkersFromRemote(authHeader: String, userId: Int) {
        val response = apiService.getPageBookmarks(
            authorization = authHeader,
            kitabId = null
        )
        if (response.code() == 401) {
            throw IllegalStateException("AUTH_401: page marker pull unauthorized")
        }
        val body = response.body()
        if (!response.isSuccessful || body?.success != true) return

        val now = System.currentTimeMillis()
        val remoteItems = body.data.orEmpty()
            .filter { it.kitabId > 0 && it.pageNumber > 0 }
        val remoteKeys = remoteItems.map { it.kitabId to it.pageNumber }.toSet()

        val localItems = pageBookmarkDao.getAllByUser(userId)
        val localByKey = localItems.associateBy { it.kitabId to it.pageNumber }

        remoteItems.forEach { remote ->
            val key = remote.kitabId to remote.pageNumber
            val existing = localByKey[key]
            val remoteCreatedAt = parseIsoToMillis(remote.createdAt) ?: now
            val remoteUpdatedAt = parseIsoToMillis(remote.updatedAt) ?: remoteCreatedAt
            val label = remote.label.trim().ifBlank { "Halaman ${remote.pageNumber}" }

            if (existing == null) {
                pageBookmarkDao.insert(
                    PageBookmarkEntity(
                        userId = userId,
                        kitabId = remote.kitabId,
                        pageNumber = remote.pageNumber,
                        label = label,
                        createdAt = remoteCreatedAt,
                        updatedAt = remoteUpdatedAt,
                        isPendingSync = false,
                        lastSyncedAt = now
                    )
                )
            } else if (!existing.isPendingSync) {
                pageBookmarkDao.update(
                    existing.copy(
                        label = label,
                        updatedAt = maxOf(existing.updatedAt, remoteUpdatedAt),
                        isPendingSync = false,
                        lastSyncedAt = now
                    )
                )
            }
        }

        localItems.asSequence()
            .filter { !it.isPendingSync }
            .filter { (it.kitabId to it.pageNumber) !in remoteKeys }
            .forEach { stale ->
                pageBookmarkDao.deleteById(stale.id, userId)
            }
    }

    private suspend fun syncReadingNotesFromRemote(authHeader: String) {
        val response = apiService.getReadingNotes(
            kitabId = null,
            authorization = authHeader
        )
        if (response.code() == 401) {
            throw IllegalStateException("AUTH_401: reading notes pull unauthorized")
        }
        val body = response.body()
        if (!response.isSuccessful || body?.success != true) return

        val notes = runCatching { body.data.notes }.getOrDefault(emptyList())
        cacheReadingNotes(notes)
    }

    private suspend fun applyRemoteHistoryToLocalProgress(userId: Int, history: HistoryItemData) {
        val kitabId = history.kitab_id
        if (kitabId <= 0) return

        val remotePage = history.current_page.coerceAtLeast(0)
        if (remotePage <= 0) return

        val existing = readingProgressDao.getProgress(userId, kitabId)
        val existingTotalPages = existing?.totalPages ?: 0
        val totalPages = when {
            history.total_pages > 0 -> history.total_pages
            existingTotalPages > 0 -> existingTotalPages
            else -> remotePage
        }.coerceAtLeast(remotePage)

        val remoteLastReadAt = parseIsoToMillis(history.last_read_at) ?: System.currentTimeMillis()
        val shouldApply = existing == null ||
            remoteLastReadAt >= existing.lastReadAt ||
            remotePage > existing.lastPageRead

        if (!shouldApply) return

        val progressPercentage = if (totalPages > 0) {
            (remotePage.toFloat() / totalPages.toFloat() * 100f).coerceIn(0f, 100f)
        } else {
            0f
        }
        val readingTimeMinutes = maxOf(existing?.readingTimeMinutes ?: 0, history.reading_time_minutes)

        val updated = if (existing == null) {
            ReadingProgress(
                userId = userId,
                kitabId = kitabId,
                lastPageRead = remotePage,
                totalPages = totalPages,
                progressPercentage = progressPercentage,
                lastReadPosition = history.last_position?.toLongOrNull() ?: 0L,
                readingTimeMinutes = readingTimeMinutes,
                lastReadAt = remoteLastReadAt,
                isCompleted = progressPercentage >= 100f,
                completedAt = if (progressPercentage >= 100f) remoteLastReadAt else null
            )
        } else {
            existing.copy(
                lastPageRead = remotePage,
                totalPages = totalPages,
                progressPercentage = progressPercentage,
                lastReadPosition = history.last_position?.toLongOrNull() ?: existing.lastReadPosition,
                readingTimeMinutes = readingTimeMinutes,
                lastReadAt = remoteLastReadAt,
                isCompleted = progressPercentage >= 100f,
                completedAt = if (progressPercentage >= 100f) {
                    existing.completedAt ?: remoteLastReadAt
                } else {
                    existing.completedAt
                }
            )
        }

        readingProgressDao.insertProgress(updated)
    }

    private fun buildException(operation: String, code: Int, message: String): IllegalStateException {
        return if (code == 401) {
            IllegalStateException("AUTH_401: $operation unauthorized")
        } else {
            IllegalStateException("$operation failed: $code $message")
        }
    }

    private fun parseIntFromPayload(payloadJson: String, key: String): Int {
        val payload = parsePayloadMap(payloadJson)
        return payload[key].asInt()
    }

    private fun parsePayloadMap(payloadJson: String): Map<String, Any?> {
        val type = object : TypeToken<Map<String, Any?>>() {}.type
        return gson.fromJson(payloadJson, type) ?: emptyMap()
    }

    private fun parsePendingNoteIds(operations: List<SyncOperationEntity>): Set<Int> {
        return operations.mapNotNull { operation ->
            parsePayloadMap(operation.payloadJson)["note_id"].asIntOrNull()?.takeIf { it > 0 }
        }.toSet()
    }

    private fun parseIsoToMillis(value: String?): Long? {
        if (value.isNullOrBlank()) return null
        return runCatching { Instant.parse(value).toEpochMilli() }.getOrNull()
    }

    private fun Any?.asInt(): Int {
        return when (this) {
            is Number -> toInt()
            is String -> toIntOrNull() ?: 0
            else -> 0
        }
    }

    private fun Any?.asIntOrNull(): Int? {
        return when (this) {
            null -> null
            is Number -> toInt()
            is String -> toIntOrNull()
            else -> null
        }
    }

    private fun Any?.asStringOrNull(): String? {
        return when (this) {
            null -> null
            is String -> this
            else -> toString()
        }
    }

    private fun Any?.asStringOrEmpty(default: String = ""): String {
        return asStringOrNull().orEmpty().ifBlank { default }
    }

    private fun Any?.asBoolean(default: Boolean = false): Boolean {
        return when (this) {
            is Boolean -> this
            is Number -> this.toInt() == 1
            is String -> this.equals("true", ignoreCase = true) || this == "1"
            else -> default
        }
    }
}
