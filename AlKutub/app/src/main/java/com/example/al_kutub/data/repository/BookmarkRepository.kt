package com.example.al_kutub.repository

import android.util.Log
import com.example.al_kutub.api.ApiService
import com.example.al_kutub.data.repository.OfflineSyncRepository
import com.example.al_kutub.model.*
import com.example.al_kutub.utils.SessionManager
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import javax.inject.Inject
import javax.inject.Singleton

private const val TAG = "BookmarkRepository"

@Singleton
class BookmarkRepository @Inject constructor(
    private val apiService: ApiService,
    private val sessionManager: SessionManager,
    private val offlineSyncRepository: OfflineSyncRepository
) {
    private fun getAuthToken(): String {
        val token = sessionManager.getToken()

        return if (!token.isNullOrBlank()) {
            "Bearer $token"
        } else {
            Log.e(TAG, "❌ NO TOKEN FOUND - User might not be logged in")
            ""
        }
    }



    /**
     * Get semua bookmark user
     */
    suspend fun getAllBookmarks(): Result<BookmarkResponse> = withContext(Dispatchers.IO) {
        try {
            Log.d(TAG, "📚 Fetching all bookmarks...")
            val response = apiService.getAllBookmarks(getAuthToken())

            if (response.isSuccessful && response.body() != null) {
                Log.d(TAG, "✅ Successfully fetched ${response.body()?.total} bookmarks")
                response.body()?.data?.let { offlineSyncRepository.cacheBookmarks(it) }
                Result.success(response.body()!!)
            } else {
                Log.e(TAG, "❌ Failed to fetch bookmarks: ${response.code()}")
                val cached = offlineSyncRepository.getCachedBookmarks()
                if (cached.isNotEmpty()) {
                    Result.success(
                        BookmarkResponse(
                            status = "success",
                            message = "Mode offline: menampilkan cache bookmark",
                            total = cached.size,
                            data = cached
                        )
                    )
                } else {
                    Result.failure(Exception("Failed to fetch bookmarks: ${response.message()}"))
                }
            }
        } catch (e: Exception) {
            Log.e(TAG, "❌ Error fetching bookmarks", e)
            val cached = offlineSyncRepository.getCachedBookmarks()
            if (cached.isNotEmpty()) {
                Result.success(
                    BookmarkResponse(
                        status = "success",
                        message = "Mode offline: menampilkan cache bookmark",
                        total = cached.size,
                        data = cached
                    )
                )
            } else {
                Result.failure(e)
            }
        }
    }

    /**
     * Toggle bookmark (tambah atau hapus)
     */
    suspend fun toggleBookmark(idKitab: Int): Result<BookmarkToggleResponse> = withContext(Dispatchers.IO) {
        try {
            Log.d(TAG, "🔄 Toggling bookmark for kitab ID: $idKitab")
            val response = apiService.toggleBookmark(idKitab, getAuthToken())

            if (response.isSuccessful && response.body() != null) {
                val action = response.body()?.action ?: "unknown"
                Log.d(TAG, "✅ Bookmark toggled: $action")
                Result.success(response.body()!!)
            } else {
                Log.e(TAG, "❌ Failed to toggle bookmark: ${response.code()}")
                Result.failure(Exception("Failed to toggle bookmark: ${response.message()}"))
            }
        } catch (e: Exception) {
            Log.e(TAG, "❌ Error toggling bookmark", e)
            offlineSyncRepository.enqueueBookmarkToggle(idKitab)
            Result.success(
                BookmarkToggleResponse(
                    status = "success",
                    message = "Perubahan bookmark disimpan offline dan akan disinkronkan.",
                    action = null,
                    isBookmarked = false
                )
            )
        }
    }

    /**
     * Add bookmark
     */
    suspend fun addBookmark(idKitab: Int): Result<BookmarkToggleResponse> = withContext(Dispatchers.IO) {
        try {
            Log.d(TAG, "➕ Adding bookmark for kitab ID: $idKitab")
            val response = apiService.addBookmark(idKitab, getAuthToken())

            if (response.isSuccessful && response.body() != null) {
                Log.d(TAG, "✅ Bookmark added successfully")
                Result.success(response.body()!!)
            } else {
                Log.e(TAG, "❌ Failed to add bookmark: ${response.code()}")
                Result.failure(Exception("Failed to add bookmark: ${response.message()}"))
            }
        } catch (e: Exception) {
            Log.e(TAG, "❌ Error adding bookmark", e)
            offlineSyncRepository.enqueueBookmarkToggle(idKitab)
            Result.success(
                BookmarkToggleResponse(
                    status = "success",
                    message = "Bookmark disimpan offline dan akan disinkronkan.",
                    action = "added",
                    isBookmarked = true
                )
            )
        }
    }

    /**
     * Check apakah kitab sudah di-bookmark
     */
    suspend fun checkBookmark(idKitab: Int): Result<BookmarkCheckResponse> = withContext(Dispatchers.IO) {
        try {
            Log.d(TAG, "🔍 Checking bookmark status for kitab ID: $idKitab")
            val response = apiService.checkBookmark(idKitab, getAuthToken())

            if (response.isSuccessful && response.body() != null) {
                val isBookmarked = response.body()?.isBookmarked ?: false
                Log.d(TAG, "✅ Bookmark status: $isBookmarked")
                Result.success(response.body()!!)
            } else {
                Log.e(TAG, "❌ Failed to check bookmark: ${response.code()}")
                Result.failure(Exception("Failed to check bookmark: ${response.message()}"))
            }
        } catch (e: Exception) {
            Log.e(TAG, "❌ Error checking bookmark", e)
            Result.failure(e)
        }
    }

    /**
     * Delete satu bookmark
     */
    suspend fun deleteBookmark(idKitab: Int): Result<BookmarkDeleteResponse> = withContext(Dispatchers.IO) {
        try {
            Log.d(TAG, "🗑️ Deleting bookmark for kitab ID: $idKitab")
            val response = apiService.deleteBookmark(idKitab, getAuthToken())

            if (response.isSuccessful && response.body() != null) {
                Log.d(TAG, "✅ Bookmark deleted successfully")
                Result.success(response.body()!!)
            } else {
                Log.e(TAG, "❌ Failed to delete bookmark: ${response.code()}")
                Result.failure(Exception("Failed to delete bookmark: ${response.message()}"))
            }
        } catch (e: Exception) {
            Log.e(TAG, "❌ Error deleting bookmark", e)
            offlineSyncRepository.enqueueBookmarkDelete(idKitab)
            Result.success(
                BookmarkDeleteResponse(
                    status = "success",
                    message = "Hapus bookmark disimpan offline dan akan disinkronkan."
                )
            )
        }
    }

    /**
     * Clear all bookmarks
     */
    suspend fun clearAllBookmarks(): Result<BookmarkDeleteResponse> = withContext(Dispatchers.IO) {
        try {
            Log.d(TAG, "🗑️ Clearing all bookmarks...")
            val response = apiService.clearAllBookmarks(getAuthToken())

            if (response.isSuccessful && response.body() != null) {
                val deletedCount = response.body()?.deletedCount ?: 0
                Log.d(TAG, "✅ Cleared $deletedCount bookmarks")
                Result.success(response.body()!!)
            } else {
                Log.e(TAG, "❌ Failed to clear bookmarks: ${response.code()}")
                Result.failure(Exception("Failed to clear bookmarks: ${response.message()}"))
            }
        } catch (e: Exception) {
            Log.e(TAG, "❌ Error clearing bookmarks", e)
            offlineSyncRepository.enqueueBookmarkClearAll()
            Result.success(
                BookmarkDeleteResponse(
                    status = "success",
                    message = "Aksi hapus semua bookmark disimpan offline dan akan disinkronkan."
                )
            )
        }
    }

    /**
     * Get bookmark statistics
     */
    suspend fun getBookmarkStats(): Result<BookmarkStatsResponse> = withContext(Dispatchers.IO) {
        try {
            Log.d(TAG, "📊 Fetching bookmark statistics...")
            val response = apiService.getBookmarkStats(getAuthToken())

            if (response.isSuccessful && response.body() != null) {
                Log.d(TAG, "✅ Successfully fetched bookmark stats")
                Result.success(response.body()!!)
            } else {
                Log.e(TAG, "❌ Failed to fetch stats: ${response.code()}")
                Result.failure(Exception("Failed to fetch stats: ${response.message()}"))
            }
        } catch (e: Exception) {
            Log.e(TAG, "❌ Error fetching stats", e)
            Result.failure(e)
        }
    }
}
