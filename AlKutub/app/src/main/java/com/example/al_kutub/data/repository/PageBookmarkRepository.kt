package com.example.al_kutub.data.repository

import android.util.Log
import com.example.al_kutub.api.ApiService
import com.example.al_kutub.data.local.dao.PageBookmarkDao
import com.example.al_kutub.data.local.entity.PageBookmarkEntity
import com.example.al_kutub.utils.SessionManager
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.flowOf
import java.time.Instant
import javax.inject.Inject
import javax.inject.Singleton

sealed class AddOrTouchBookmarkResult {
    data class Added(val id: Long? = null) : AddOrTouchBookmarkResult()
    data object AlreadyExists : AddOrTouchBookmarkResult()
}

@Singleton
class PageBookmarkRepository @Inject constructor(
    private val apiService: ApiService,
    private val pageBookmarkDao: PageBookmarkDao,
    private val sessionManager: SessionManager,
    private val offlineSyncRepository: OfflineSyncRepository
) {
    companion object {
        private const val TAG = "PageBookmarkRepository"
    }

    fun isLoggedIn(): Boolean {
        return sessionManager.isLoggedIn() && sessionManager.getUserId() > 0
    }

    fun observePageBookmarks(kitabId: Int): Flow<List<PageBookmarkEntity>> {
        val userId = currentUserIdOrNull() ?: return flowOf(emptyList())
        if (kitabId <= 0) return flowOf(emptyList())
        return pageBookmarkDao.observeByKitab(userId, kitabId)
    }

    suspend fun addOrTouchPageBookmark(
        kitabId: Int,
        pageNumber: Int,
        defaultLabel: String
    ): Result<AddOrTouchBookmarkResult> {
        val userId = currentUserIdOrNull()
            ?: return Result.failure(IllegalStateException("Silakan login untuk menambah marker."))

        if (kitabId <= 0 || pageNumber <= 0) {
            return Result.failure(IllegalArgumentException("Kitab atau halaman tidak valid."))
        }

        val now = System.currentTimeMillis()
        val label = defaultLabel.trim().ifBlank { "Halaman $pageNumber" }

        return try {
            val existing = pageBookmarkDao.getByPage(userId, kitabId, pageNumber)
            if (existing != null) {
                pageBookmarkDao.update(
                    existing.copy(
                        updatedAt = now,
                        isPendingSync = true
                    )
                )
                offlineSyncRepository.enqueuePageMarkerUpsert(
                    kitabId = kitabId,
                    pageNumber = pageNumber,
                    label = existing.label,
                    clientUpdatedAt = Instant.ofEpochMilli(now).toString()
                )
                Result.success(AddOrTouchBookmarkResult.AlreadyExists)
            } else {
                pageBookmarkDao.insert(
                    PageBookmarkEntity(
                        userId = userId,
                        kitabId = kitabId,
                        pageNumber = pageNumber,
                        label = label,
                        createdAt = now,
                        updatedAt = now,
                        isPendingSync = true,
                        lastSyncedAt = null
                    )
                )
                offlineSyncRepository.enqueuePageMarkerUpsert(
                    kitabId = kitabId,
                    pageNumber = pageNumber,
                    label = label,
                    clientUpdatedAt = Instant.ofEpochMilli(now).toString()
                )
                Result.success(AddOrTouchBookmarkResult.Added())
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun renamePageBookmark(id: Long, newLabel: String): Result<Unit> {
        val userId = currentUserIdOrNull()
            ?: return Result.failure(IllegalStateException("Silakan login untuk mengubah marker."))

        return try {
            val existing = pageBookmarkDao.getById(id, userId)
                ?: return Result.failure(NoSuchElementException("Marker tidak ditemukan."))

            val normalizedLabel = newLabel.trim().ifBlank { "Halaman ${existing.pageNumber}" }
            val now = System.currentTimeMillis()
            pageBookmarkDao.update(
                existing.copy(
                    label = normalizedLabel,
                    updatedAt = now,
                    isPendingSync = true
                )
            )
            offlineSyncRepository.enqueuePageMarkerUpsert(
                kitabId = existing.kitabId,
                pageNumber = existing.pageNumber,
                label = normalizedLabel,
                clientUpdatedAt = Instant.ofEpochMilli(now).toString()
            )
            Result.success(Unit)
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun deletePageBookmark(id: Long): Result<Unit> {
        val userId = currentUserIdOrNull()
            ?: return Result.failure(IllegalStateException("Silakan login untuk menghapus marker."))

        return try {
            val entity = pageBookmarkDao.getById(id, userId)
            pageBookmarkDao.deleteById(id, userId)
            if (entity != null) {
                offlineSyncRepository.enqueuePageMarkerDelete(
                    kitabId = entity.kitabId,
                    pageNumber = entity.pageNumber
                )
            }
            Result.success(Unit)
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun syncFromRemote(): Result<Unit> {
        val userId = currentUserIdOrNull()
            ?: return Result.failure(IllegalStateException("Silakan login untuk sinkron marker halaman."))
        val token = sessionManager.getToken()
            ?: return Result.failure(IllegalStateException("Sesi tidak ditemukan."))

        return try {
            val response = apiService.getPageBookmarks(
                authorization = "Bearer $token",
                kitabId = null
            )
            val body = response.body()
            if (!response.isSuccessful || body?.success != true) {
                return Result.failure(
                    IllegalStateException("Gagal sinkron marker: ${response.code()} ${response.message()}")
                )
            }

            val now = System.currentTimeMillis()
            val remoteItems = body.data.orEmpty()
                .filter { it.kitabId > 0 && it.pageNumber > 0 }
            val remoteKeys = remoteItems.map { it.kitabId to it.pageNumber }.toSet()

            val localItems = pageBookmarkDao.getAllByUser(userId)
            val localByKey = localItems.associateBy { it.kitabId to it.pageNumber }

            remoteItems.forEach { remote ->
                val key = remote.kitabId to remote.pageNumber
                val existing = localByKey[key]
                val remoteCreatedAt = parseEpochMillis(remote.createdAt) ?: now
                val remoteUpdatedAt = parseEpochMillis(remote.updatedAt) ?: remoteCreatedAt
                val normalizedLabel = remote.label.trim().ifBlank { "Halaman ${remote.pageNumber}" }

                if (existing == null) {
                    pageBookmarkDao.insert(
                        PageBookmarkEntity(
                            userId = userId,
                            kitabId = remote.kitabId,
                            pageNumber = remote.pageNumber,
                            label = normalizedLabel,
                            createdAt = remoteCreatedAt,
                            updatedAt = remoteUpdatedAt,
                            isPendingSync = false,
                            lastSyncedAt = now
                        )
                    )
                } else if (!existing.isPendingSync) {
                    pageBookmarkDao.update(
                        existing.copy(
                            label = normalizedLabel,
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

            Result.success(Unit)
        } catch (e: Exception) {
            Log.w(TAG, "syncFromRemote failed: ${e.message}")
            Result.failure(e)
        }
    }

    private fun currentUserIdOrNull(): Int? {
        if (!sessionManager.isLoggedIn()) return null
        val userId = sessionManager.getUserId()
        return if (userId > 0) userId else null
    }

    private fun parseEpochMillis(value: String?): Long? {
        if (value.isNullOrBlank()) return null
        return runCatching {
            Instant.parse(value).toEpochMilli()
        }.getOrNull()
    }
}
