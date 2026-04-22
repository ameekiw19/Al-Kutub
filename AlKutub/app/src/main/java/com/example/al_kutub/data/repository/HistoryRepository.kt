package com.example.al_kutub.data.repository

import android.util.Log
import com.example.al_kutub.api.ApiService
import com.example.al_kutub.model.*
import com.example.al_kutub.utils.SessionManager
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import retrofit2.Response
import java.time.Instant
import java.util.concurrent.TimeUnit
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class HistoryRepository @Inject constructor(
    private val apiService: ApiService,
    private val sessionManager: SessionManager,
    private val readingProgressRepository: ReadingProgressRepository,
    private val offlineSyncRepository: OfflineSyncRepository
) {
    private val TAG = "HistoryRepository"
    private var rateLimitedUntilMillis: Long = 0L

    private val _historiesState = MutableStateFlow<HistoriesState>(HistoriesState.Loading)
    val historiesState: StateFlow<HistoriesState> = _historiesState.asStateFlow()

    private val _deleteState = MutableStateFlow<DeleteState>(DeleteState.Idle)
    val deleteState: StateFlow<DeleteState> = _deleteState.asStateFlow()

    /**
     * Load all histories with grouping by date
     */
    suspend fun loadHistories() {
        try {
            Log.d(TAG, "=== LOADING HISTORIES ===")
            _historiesState.value = HistoriesState.Loading

            // Check if user is logged in
            if (!sessionManager.isLoggedIn()) {
                Log.e(TAG, "❌ User not logged in")
                _historiesState.value = HistoriesState.Error("Silakan login terlebih dahulu")
                return
            }

            val token = sessionManager.getToken()
            if (token.isNullOrBlank()) {
                Log.e(TAG, "❌ No token/session id available")
                _historiesState.value = HistoriesState.Error("Silakan login terlebih dahulu")
                return
            }

            if (isRateLimitedActive()) {
                Log.w(TAG, "⏳ Skip getHistories sementara karena rate limit aktif")
                emitCachedHistoriesOrError(
                    fallbackMessage = "Permintaan terlalu sering. Menampilkan riwayat lokal."
                )
                return
            }

            Log.d(TAG, "✅ User is logged in")
            Log.d(TAG, "Calling API getHistories...")

            val response = apiService.getHistories("Bearer $token")

            Log.d(TAG, "API Response code: ${response.code()}")
            Log.d(TAG, "API Response successful: ${response.isSuccessful}")

            if (response.isSuccessful) {
                val body = response.body()
                Log.d(TAG, "Response body: $body")

                if (body != null && body.success && body.data != null) {
                    Log.d(TAG, "✅ Histories loaded successfully")
                    Log.d(TAG, "Total histories: ${body.data.total}")
                    Log.d(TAG, "Grouped histories count: ${body.data.histories.size}")
                    offlineSyncRepository.cacheHistories(body.data.raw_histories)
                    syncRemoteHistoriesToLocalProgress(body.data.raw_histories)
                    
                    // Log IDs for debugging
                    body.data.raw_histories.forEach { history ->
                        Log.d(TAG, "History item: id=${history.id}, kitab_id=${history.kitab_id}, judul=${history.kitab?.judul}")
                    }
                    
                    _historiesState.value = HistoriesState.Success(body.data)
                } else {
                    Log.e(TAG, "❌ API returned success=false")
                    Log.e(TAG, "Message: ${body?.message}")
                    _historiesState.value = HistoriesState.Error(body?.message ?: "Gagal memuat riwayat")
                }
            } else {
                if (response.code() == 429) {
                    applyRateLimit(response)
                    Log.w(TAG, "⏳ API getHistories kena rate limit (429), fallback cache")
                    emitCachedHistoriesOrError(
                        fallbackMessage = "Permintaan terlalu sering. Menampilkan riwayat lokal."
                    )
                    return
                }

                Log.e(TAG, "❌ API call failed")
                Log.e(TAG, "Response code: ${response.code()}")
                Log.e(TAG, "Response message: ${response.message()}")
                Log.e(TAG, "Error body: ${response.errorBody()?.string()}")

                val errorMessage = when (response.code()) {
                    401 -> "Sesi Anda telah berakhir. Silakan login kembali"
                    403 -> "Akses ditolak"
                    404 -> "Endpoint tidak ditemukan"
                    500 -> "Terjadi kesalahan pada server"
                    else -> "Gagal memuat riwayat: ${response.message()}"
                }

            _historiesState.value = HistoriesState.Error(errorMessage)
        }
    } catch (e: Exception) {
        Log.e(TAG, "❌ Exception loading histories", e)
            Log.e(TAG, "Exception type: ${e.javaClass.simpleName}")
            Log.e(TAG, "Exception message: ${e.message}")
            e.printStackTrace()
            val cached = offlineSyncRepository.getCachedHistories()
            if (cached.isNotEmpty()) {
                _historiesState.value = HistoriesState.Success(
                    HistoryData(
                        total = cached.size,
                        histories = cached.groupBy { it.time_ago.ifBlank { "Offline" } },
                        raw_histories = cached
                    )
                )
            } else {
                _historiesState.value = HistoriesState.Error("Terjadi kesalahan: ${e.message}")
            }
        } finally {
            Log.d(TAG, "=== LOAD HISTORIES COMPLETE ===")
        }
    }

    /**
     * Add or update history when user opens a kitab
     */
    /**
     * Add or update history when user opens a kitab
     */
    suspend fun addOrUpdateHistory(
        kitabId: Int,
        currentPage: Int? = null,
        totalPages: Int? = null,
        lastPosition: String? = null,
        readingTimeMinutes: Int? = null,
        readingTimeAdded: Int? = null,
        clientUpdatedAt: String? = null
    ): Result<HistoryDetailResponse> {
        return try {
            Log.d(TAG, "=== ADD/UPDATE HISTORY ===")
            Log.d(TAG, "Kitab ID: $kitabId, Page: $currentPage/${totalPages ?: "?"}, AddedTime: $readingTimeAdded")

            if (!sessionManager.isLoggedIn()) {
                Log.e(TAG, "❌ User not logged in")
                return Result.failure(Exception("Silakan login terlebih dahulu"))
            }

            val token = sessionManager.getToken()
            if (token.isNullOrBlank()) {
                Log.e(TAG, "❌ No token/session id available")
                return Result.failure(Exception("Silakan login terlebih dahulu"))
            }

            val resolvedClientUpdatedAt = clientUpdatedAt ?: Instant.now().toString()

            if (isRateLimitedActive()) {
                offlineSyncRepository.enqueueHistoryUpsert(
                    kitabId = kitabId,
                    currentPage = currentPage,
                    totalPages = totalPages,
                    lastPosition = lastPosition,
                    readingTimeMinutes = readingTimeMinutes,
                    readingTimeAdded = readingTimeAdded,
                    clientUpdatedAt = resolvedClientUpdatedAt
                )
                return Result.success(
                    HistoryDetailResponse(
                        success = true,
                        message = "Permintaan terlalu sering. Riwayat disimpan lokal dan akan sinkron otomatis.",
                        data = null
                    )
                )
            }

            Log.d(TAG, "✅ User logged in, calling API...")
            val response = apiService.addOrUpdateHistory(
                kitabId = kitabId,
                currentPage = currentPage,
                totalPages = totalPages,
                lastPosition = lastPosition,
                readingTimeMinutes = readingTimeMinutes,
                readingTimeAdded = readingTimeAdded,
                clientUpdatedAt = resolvedClientUpdatedAt,
                authorization = "Bearer $token"
            )

            if (response.isSuccessful) {
                val body = response.body()
                if (body != null && body.success) {
                    Log.d(TAG, "✅ History added/updated successfully")
                    Result.success(body)
                } else {
                    Log.e(TAG, "❌ API returned success=false")
                    Result.failure(Exception(body?.message ?: "Gagal menyimpan riwayat"))
                }
            } else {
                if (response.code() == 429) {
                    applyRateLimit(response)
                    offlineSyncRepository.enqueueHistoryUpsert(
                        kitabId = kitabId,
                        currentPage = currentPage,
                        totalPages = totalPages,
                        lastPosition = lastPosition,
                        readingTimeMinutes = readingTimeMinutes,
                        readingTimeAdded = readingTimeAdded,
                        clientUpdatedAt = resolvedClientUpdatedAt
                    )
                    Log.w(TAG, "⏳ Rate limit 429 pada addOrUpdateHistory, fallback ke queue offline")
                    return Result.success(
                        HistoryDetailResponse(
                            success = true,
                            message = "Permintaan terlalu sering. Riwayat disimpan lokal dan akan sinkron otomatis.",
                            data = null
                        )
                    )
                }

                Log.e(TAG, "❌ API call failed: ${response.code()}")
                Result.failure(Exception("Gagal menyimpan riwayat: ${response.message()}"))
            }
        } catch (e: Exception) {
            Log.e(TAG, "❌ Exception adding/updating history", e)
            offlineSyncRepository.enqueueHistoryUpsert(
                kitabId = kitabId,
                currentPage = currentPage,
                totalPages = totalPages,
                lastPosition = lastPosition,
                readingTimeMinutes = readingTimeMinutes,
                readingTimeAdded = readingTimeAdded,
                clientUpdatedAt = clientUpdatedAt ?: Instant.now().toString()
            )
            Result.success(
                HistoryDetailResponse(
                    success = true,
                    message = "Riwayat disimpan lokal dan akan sinkron otomatis.",
                    data = null
                )
            )
        }
    }

    /**
     * Delete single history item
     */
    suspend fun deleteHistory(historyId: Int): Result<BaseResponse> {
        return try {
            Log.d(TAG, "=== DELETE HISTORY ===")
            Log.d(TAG, "History ID: $historyId")
            
            // Validate ID
            if (historyId <= 0) {
                Log.e(TAG, "❌ Invalid history ID: $historyId (must be > 0)")
                _deleteState.value = DeleteState.Error("ID tidak valid")
                return Result.failure(Exception("ID tidak valid: $historyId"))
            }
            
            _deleteState.value = DeleteState.Loading(historyId)

            if (!sessionManager.isLoggedIn()) {
                Log.e(TAG, "❌ User not logged in")
                _deleteState.value = DeleteState.Error("Silakan login terlebih dahulu")
                return Result.failure(Exception("Silakan login terlebih dahulu"))
            }

            val token = sessionManager.getToken()
            if (token.isNullOrBlank()) {
                Log.e(TAG, "❌ No token/session id available")
                _deleteState.value = DeleteState.Error("Silakan login terlebih dahulu")
                return Result.failure(Exception("Silakan login terlebih dahulu"))
            }

            if (isRateLimitedActive()) {
                offlineSyncRepository.enqueueHistoryDelete(historyId)
                _deleteState.value = DeleteState.Success(historyId)
                return Result.success(BaseResponse(true, "Permintaan terlalu sering. Hapus riwayat disimpan lokal dan akan sinkron otomatis."))
            }

            Log.d(TAG, "✅ User logged in, calling API...")
            val response = apiService.deleteHistory(historyId, "Bearer $token")

            if (response.isSuccessful) {
                val body = response.body()
                if (body != null && body.success) {
                    Log.d(TAG, "✅ History deleted successfully")
                    _deleteState.value = DeleteState.Success(historyId)
                    Result.success(body)
                } else {
                    Log.e(TAG, "❌ API returned success=false")
                    _deleteState.value = DeleteState.Error(body?.message ?: "Gagal menghapus riwayat")
                    Result.failure(Exception(body?.message ?: "Gagal menghapus riwayat"))
                }
            } else if (response.code() == 404) {
                // 404 means the item is already gone, so we consider it a success
                Log.w(TAG, "⚠️ API returned 404 (Not Found), treating as success (already deleted)")
                _deleteState.value = DeleteState.Success(historyId)
                Result.success(BaseResponse(true, "Riwayat berhasil dihapus"))
            } else {
                if (response.code() == 429) {
                    applyRateLimit(response)
                    offlineSyncRepository.enqueueHistoryDelete(historyId)
                    _deleteState.value = DeleteState.Success(historyId)
                    return Result.success(BaseResponse(true, "Permintaan terlalu sering. Hapus riwayat disimpan lokal dan akan sinkron otomatis."))
                }

                Log.e(TAG, "❌ API call failed: ${response.code()}")
                _deleteState.value = DeleteState.Error("Gagal menghapus riwayat: ${response.message()}")
                Result.failure(Exception("Gagal menghapus riwayat: ${response.message()}"))
            }
        } catch (e: Exception) {
            Log.e(TAG, "❌ Exception deleting history", e)
            offlineSyncRepository.enqueueHistoryDelete(historyId)
            _deleteState.value = DeleteState.Success(historyId)
            Result.success(BaseResponse(true, "Hapus riwayat disimpan lokal dan akan sinkron otomatis."))
        }
    }

    /**
     * Clear all history
     */
    suspend fun clearAllHistory(): Result<ClearHistoryResponse> {
        return try {
            Log.d(TAG, "=== CLEAR ALL HISTORY ===")

            if (!sessionManager.isLoggedIn()) {
                Log.e(TAG, "❌ User not logged in")
                return Result.failure(Exception("Silakan login terlebih dahulu"))
            }

            val token = sessionManager.getToken()
            if (token.isNullOrBlank()) {
                Log.e(TAG, "❌ No token/session id available")
                return Result.failure(Exception("Silakan login terlebih dahulu"))
            }

            if (isRateLimitedActive()) {
                offlineSyncRepository.enqueueHistoryClearAll()
                return Result.success(
                    ClearHistoryResponse(
                        success = true,
                        message = "Permintaan terlalu sering. Hapus semua riwayat disimpan lokal dan akan sinkron otomatis.",
                        data = ClearHistoryData(0)
                    )
                )
            }

            Log.d(TAG, "✅ User logged in, calling API...")
            val response = apiService.clearAllHistory("Bearer $token")

            if (response.isSuccessful) {
                val body = response.body()
                if (body != null && body.success) {
                    Log.d(TAG, "✅ All history cleared successfully")
                    Log.d(TAG, "Deleted count: ${body.data?.deleted_count ?: 0}")
                    Result.success(body)
                } else {
                    Log.e(TAG, "❌ API returned success=false")
                    Result.failure(Exception(body?.message ?: "Gagal menghapus semua riwayat"))
                }
            } else {
                if (response.code() == 429) {
                    applyRateLimit(response)
                    offlineSyncRepository.enqueueHistoryClearAll()
                    return Result.success(
                        ClearHistoryResponse(
                            success = true,
                            message = "Permintaan terlalu sering. Hapus semua riwayat disimpan lokal dan akan sinkron otomatis.",
                            data = ClearHistoryData(0)
                        )
                    )
                }

                Log.e(TAG, "❌ API call failed: ${response.code()}")
                Result.failure(Exception("Gagal menghapus semua riwayat: ${response.message()}"))
            }
        } catch (e: Exception) {
            Log.e(TAG, "❌ Exception clearing all history", e)
            offlineSyncRepository.enqueueHistoryClearAll()
            Result.success(
                ClearHistoryResponse(
                    success = true,
                    message = "Hapus semua riwayat disimpan lokal dan akan sinkron otomatis.",
                    data = ClearHistoryData(0)
                )
            )
        }
    }

    /**
     * Get history statistics
     */
    suspend fun getHistoryStatistics(): Result<HistoryStatsResponse> {
        return try {
            Log.d(TAG, "=== GET HISTORY STATISTICS ===")

            if (!sessionManager.isLoggedIn()) {
                Log.e(TAG, "❌ User not logged in")
                return Result.failure(Exception("Silakan login terlebih dahulu"))
            }

            val token = sessionManager.getToken()
            if (token.isNullOrBlank()) {
                Log.e(TAG, "❌ No token/session id available")
                return Result.failure(Exception("Silakan login terlebih dahulu"))
            }

            if (isRateLimitedActive()) {
                return Result.failure(Exception("Server sedang membatasi permintaan. Coba beberapa saat lagi."))
            }

            Log.d(TAG, "✅ User logged in, calling API...")
            val response = apiService.getHistoryStatistics("Bearer $token")

            if (response.isSuccessful) {
                val body = response.body()
                if (body != null && body.success) {
                    Log.d(TAG, "✅ History statistics loaded successfully")
                    Result.success(body)
                } else {
                    Log.e(TAG, "❌ API returned success=false")
                    Result.failure(Exception(body?.message ?: "Gagal memuat statistik"))
                }
            } else {
                if (response.code() == 429) {
                    applyRateLimit(response)
                    return Result.failure(Exception("Terlalu banyak permintaan. Coba beberapa saat lagi."))
                }

                Log.e(TAG, "❌ API call failed: ${response.code()}")
                Result.failure(Exception("Gagal memuat statistik: ${response.message()}"))
            }
        } catch (e: Exception) {
            Log.e(TAG, "❌ Exception loading statistics", e)
            Result.failure(e)
        }
    }

    /**
     * Reset delete state
     */
    fun resetDeleteState() {
        _deleteState.value = DeleteState.Idle
    }

    private suspend fun syncRemoteHistoriesToLocalProgress(items: List<HistoryItemData>) {
        val userId = sessionManager.getUserId()
        if (userId <= 0) return

        items.forEach { historyItem ->
            val kitabId = historyItem.kitab_id
            if (kitabId <= 0) return@forEach

            val local = readingProgressRepository.getProgress(userId, kitabId)
            val resolvedPage = HistoryProgressConflictResolver.resolvePage(
                localPage = local?.lastPageRead,
                remotePage = historyItem.current_page
            )
            val resolvedTotalPages = HistoryProgressConflictResolver.resolveTotalPages(
                localTotal = local?.totalPages,
                remoteTotal = historyItem.total_pages,
                resolvedPage = resolvedPage
            )

            readingProgressRepository.updateReadingProgress(
                userId = userId,
                kitabId = kitabId,
                currentPage = resolvedPage,
                totalPages = resolvedTotalPages
            )
        }
    }

    private fun isRateLimitedActive(): Boolean {
        return System.currentTimeMillis() < rateLimitedUntilMillis
    }

    private fun applyRateLimit(response: Response<*>) {
        val retryAfterSeconds = response.headers()["Retry-After"]?.toLongOrNull()?.coerceAtLeast(1L)
        val waitMillis = TimeUnit.SECONDS.toMillis(retryAfterSeconds ?: 30L)
        val newUntil = System.currentTimeMillis() + waitMillis
        if (newUntil > rateLimitedUntilMillis) {
            rateLimitedUntilMillis = newUntil
        }
    }

    private suspend fun emitCachedHistoriesOrError(fallbackMessage: String) {
        val cached = offlineSyncRepository.getCachedHistories()
        if (cached.isNotEmpty()) {
            _historiesState.value = HistoriesState.Success(
                HistoryData(
                    total = cached.size,
                    histories = cached.groupBy { it.time_ago.ifBlank { "Offline" } },
                    raw_histories = cached
                )
            )
        } else {
            _historiesState.value = HistoriesState.Error(fallbackMessage)
        }
    }
}

// ============== State Classes ==============

sealed class HistoriesState {
    object Loading : HistoriesState()
    data class Success(val data: HistoryData) : HistoriesState()
    data class Error(val message: String) : HistoriesState()
}

sealed class DeleteState {
    object Idle : DeleteState()
    data class Loading(val historyId: Int) : DeleteState()
    data class Success(val historyId: Int) : DeleteState()
    data class Error(val message: String) : DeleteState()
}
