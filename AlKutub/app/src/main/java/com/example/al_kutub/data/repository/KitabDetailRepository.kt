package com.example.al_kutub.data.repository

import android.util.Log
import com.example.al_kutub.api.ApiService
import com.example.al_kutub.model.ApiResponse
import com.example.al_kutub.model.BookmarkToggleResponse
import com.example.al_kutub.model.Comment
import com.example.al_kutub.model.CommentRequest
import com.example.al_kutub.model.Kitab
import com.example.al_kutub.model.MyRatingResponse
import com.example.al_kutub.model.RateKitabResponse
import javax.inject.Inject
import javax.inject.Singleton

/**
 * Repository untuk mengelola data kitab detail
 */
@Singleton
class KitabDetailRepository @Inject constructor(
    private val apiService: ApiService
) {
    private val TAG = "KitabDetailRepository"

    /**
     * Get detail kitab by ID
     * GET /api/kitab/{id_kitab}
     */
    suspend fun getKitabDetail(idKitab: Int): Result<ApiResponse<Kitab>> {
        return try {
            Log.d(TAG, "========================================")
            Log.d(TAG, "Requesting kitab detail for ID: $idKitab")
            Log.d(TAG, "========================================")

            val response = apiService.getKitabDetail(idKitab)

            Log.d(TAG, "Response Code: ${response.code()}")
            Log.d(TAG, "Response Message: ${response.message()}")
            Log.d(TAG, "Is Successful: ${response.isSuccessful}")

            if (response.isSuccessful) {
                val body = response.body()

                if (body != null) {
                    Log.d(TAG, "✅ Response Body Found")
                    Log.d(TAG, "Success: ${body.success}")
                    Log.d(TAG, "Message: ${body.message}")
                    Log.d(TAG, "Data: ${body.data}")
                    Log.d(TAG, "========================================")

                    Result.success(body)
                } else {
                    Log.e(TAG, "❌ Response body is NULL")
                    Log.e(TAG, "========================================")
                    Result.failure(Exception("Response body is null"))
                }
            } else {
                // Handle specific HTTP error codes
                val errorBody = response.errorBody()?.string()
                val errorMessage = when (response.code()) {
                    404 -> {
                        Log.e(TAG, "❌ 404 NOT FOUND")
                        Log.e(TAG, "Kitab dengan ID $idKitab tidak ditemukan di server")
                        "Kitab dengan ID $idKitab tidak ditemukan"
                    }
                    500 -> {
                        Log.e(TAG, "❌ 500 SERVER ERROR")
                        Log.e(TAG, "Error Body: $errorBody")
                        "Server mengalami error, silakan coba lagi"
                    }
                    401 -> {
                        Log.e(TAG, "❌ 401 UNAUTHORIZED")
                        "Unauthorized - Token mungkin expired"
                    }
                    403 -> {
                        Log.e(TAG, "❌ 403 FORBIDDEN")
                        "Akses ditolak"
                    }
                    else -> {
                        Log.e(TAG, "❌ HTTP Error ${response.code()}")
                        Log.e(TAG, "Error Body: $errorBody")
                        "Error: ${response.code()} - ${response.message()}"
                    }
                }
                Log.e(TAG, "========================================")
                Result.failure(Exception(errorMessage))
            }
        } catch (e: Exception) {
            Log.e(TAG, "❌❌❌ EXCEPTION CAUGHT ❌❌❌")
            Log.e(TAG, "Exception Type: ${e::class.java.simpleName}")
            Log.e(TAG, "Exception Message: ${e.message}")
            Log.e(TAG, "Stack Trace:", e)
            Log.e(TAG, "========================================")

            // Provide user-friendly error messages
            val userMessage = when (e) {
                is java.net.UnknownHostException -> {
                    "Tidak dapat terhubung ke server. Periksa koneksi internet Anda."
                }
                is java.net.SocketTimeoutException -> {
                    "Koneksi timeout. Server terlalu lama merespons."
                }
                is java.net.ConnectException -> {
                    "Gagal terhubung ke server. Pastikan server sedang berjalan."
                }
                else -> {
                    "Terjadi kesalahan: ${e.message ?: "Unknown error"}"
                }
            }

            Result.failure(Exception(userMessage))
        }
    }

    /**
     * Get related kitab berdasarkan kategori
     * GET /api/kitab/{id_kitab}/related
     */
    suspend fun getRelatedKitab(idKitab: Int): Result<ApiResponse<List<Kitab>>> {
        return try {
            val response = apiService.getRelatedKitab(idKitab)
            if (response.isSuccessful) {
                response.body()?.let { Result.success(it) } ?: Result.failure(Exception("Response body null"))
            } else {
                Result.failure(Exception("Error ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    /**
     * Increment view count for kitab
     * POST /api/kitab/{id_kitab}/view
     * Requires authentication token
     */
    suspend fun incrementView(idKitab: Int, token: String): Result<ApiResponse<Map<String, String>>> {
        return try {
            Log.d(TAG, "Incrementing view for kitab ID: $idKitab")
            Log.d(TAG, "Using token: ${token.take(20)}...")
            
            val response = apiService.incrementView(idKitab, "Bearer $token")
            
            Log.d(TAG, "View increment response code: ${response.code()}")
            Log.d(TAG, "View increment response successful: ${response.isSuccessful}")
            
            if (response.isSuccessful) {
                val body = response.body()
                if (body != null) {
                    Log.d(TAG, "✅ View increment success: ${body.message}")
                    Result.success(body)
                } else {
                    Log.e(TAG, "❌ View increment response body is null")
                    Result.failure(Exception("Response body is null"))
                }
            } else {
                val errorMessage = when (response.code()) {
                    401 -> "Unauthorized - Token mungkin expired"
                    403 -> "Akses ditolak"
                    404 -> "Kitab tidak ditemukan"
                    500 -> "Server error"
                    else -> "Error: ${response.code()}"
                }
                Log.e(TAG, "❌ View increment failed: $errorMessage")
                Result.failure(Exception(errorMessage))
            }
        } catch (e: Exception) {
            Log.e(TAG, "Failed to increment view", e)
            Result.failure(e)
        }
    }

    /**
     * Toggle bookmark (add/remove)
     * POST /api/bookmark/{id_kitab}
     * Requires authentication token
     */
    suspend fun toggleBookmark(idKitab: Int, token: String): Result<BookmarkToggleResponse> {
        return try {
            Log.d(TAG, "Toggling bookmark for kitab ID: $idKitab")
            Log.d(TAG, "Using token: ${token.take(20)}...")
            
            val response = apiService.toggleBookmark(idKitab, "Bearer $token")
            
            Log.d(TAG, "Bookmark toggle response code: ${response.code()}")
            Log.d(TAG, "Bookmark toggle response successful: ${response.isSuccessful}")
            
            if (response.isSuccessful) {
                val body = response.body()
                if (body != null) {
                    Log.d(TAG, "✅ Bookmark toggle success: ${body.message}")
                    Result.success(body)
                } else {
                    Log.e(TAG, "❌ Bookmark toggle response body is null")
                    Result.failure(Exception("Response body is null"))
                }
            } else {
                val errorMessage = when (response.code()) {
                    401 -> "Unauthorized - Token mungkin expired"
                    403 -> "Akses ditolak"
                    404 -> "Kitab tidak ditemukan"
                    500 -> "Server error"
                    else -> "Error: ${response.code()}"
                }
                Log.e(TAG, "❌ Bookmark toggle failed: $errorMessage")
                Result.failure(Exception(errorMessage))
            }
        } catch (e: Exception) {
            Log.e(TAG, "Failed to toggle bookmark", e)
            Result.failure(e)
        }
    }

    /**
     * Check if kitab is bookmarked
     * GET /api/bookmarks/check/{id_kitab}
     */
    suspend fun checkBookmark(idKitab: Int, token: String): Result<com.example.al_kutub.model.BookmarkCheckResponse> {
        return try {
            val response = apiService.checkBookmark(idKitab, "Bearer $token")
            if (response.isSuccessful && response.body() != null) {
                Result.success(response.body()!!)
            } else {
                Result.failure(Exception("Failed to check bookmark: ${response.message()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    /**
     * Get comments for a kitab (Public route - anyone can view)
     * GET /api/kitab/{id_kitab}/comments
     */
    suspend fun getComments(idKitab: Int): Result<ApiResponse<List<Comment>>> {
        return try {
            Log.d(TAG, "========================================")
            Log.d(TAG, "GETTING COMMENTS FOR KITAB")
            Log.d(TAG, "Kitab ID: $idKitab")
            Log.d(TAG, "Route: GET /api/kitab/$idKitab/comments")
            Log.d(TAG, "========================================")
            
            val response = apiService.getComments(idKitab)
            
            Log.d(TAG, "Response Code: ${response.code()}")
            Log.d(TAG, "Response Message: ${response.message()}")
            Log.d(TAG, "Is Successful: ${response.isSuccessful}")
            
            if (response.isSuccessful) {
                val body = response.body()
                
                if (body != null) {
                    Log.d(TAG, "Response Body Found")
                    Log.d(TAG, "Success: ${body.success}")
                    Log.d(TAG, "Message: ${body.message}")
                    Log.d(TAG, "Data Type: ${body.data?.let { it::class.java.simpleName } ?: "null"}")
                    Log.d(TAG, "Data Size: ${body.data?.size ?: 0}")
                    
                    // Check if data is null but success is true
                    if (body.success && body.data == null) {
                        Log.w(TAG, "API returned success=true but data=null")
                        // Return empty list
                        Result.success(body.copy(data = emptyList()))
                    } else {
                        // Log each comment for debugging
                        body.data?.let { comments ->
                            Log.d(TAG, "COMMENTS LIST:")
                            comments.forEachIndexed { index, comment ->
                                Log.d(TAG, "Comment[$index]:")
                                Log.d(TAG, "  - ID: ${comment.id}")
                                Log.d(TAG, "  - Kitab ID: ${comment.idKitab}")
                                Log.d(TAG, "  - User ID: ${comment.idUser}")
                                Log.d(TAG, "  - Username: ${comment.username}")
                                Log.d(TAG, "  - Comment: ${comment.comment}")
                                Log.d(TAG, "  - Created: ${comment.createdAt}")
                                Log.d(TAG, "  - Updated: ${comment.updatedAt}")
                            }
                        }
                        
                        Log.d(TAG, "========================================")
                        Result.success(body)
                    }
                } else {
                    Log.e(TAG, "Response body is NULL")
                    Log.e(TAG, "Raw Response: ${response.raw()}")
                    Log.e(TAG, "Error Body: ${response.errorBody()?.string()}")
                    Log.e(TAG, "========================================")
                    Result.failure(Exception("Response body is null"))
                }
            } else {
                val errorBody = response.errorBody()?.string()
                Log.e(TAG, "HTTP Error ${response.code()}")
                Log.e(TAG, "Error Body: $errorBody")
                
                val errorMessage = when (response.code()) {
                    404 -> "Komentar tidak ditemukan"
                    500 -> "Server error"
                    else -> "Error: ${response.code()} - ${response.message()}"
                }
                Log.e(TAG, "Get comments failed: $errorMessage")
                Log.e(TAG, "========================================")
                Result.failure(Exception(errorMessage))
            }
        } catch (e: Exception) {
            Log.e(TAG, "EXCEPTION CAUGHT")
            Log.e(TAG, "Exception Type: ${e::class.java.simpleName}")
            Log.e(TAG, "Exception Message: ${e.message}")
            Log.e(TAG, "Stack Trace:", e)
            Log.e(TAG, "========================================")
            Result.failure(e)
        }
    }

    /**
     * Submit a new comment (Protected route - requires auth)
     * POST /api/kitab/{id_kitab}/comment
     */
    suspend fun submitComment(idKitab: Int, comment: String, userId: Int, token: String): Result<ApiResponse<Comment>> {
        return try {
            Log.d(TAG, "Submitting comment for kitab ID: $idKitab (Protected route)")
            Log.d(TAG, "User ID: $userId")
            Log.d(TAG, "Comment: $comment")
            Log.d(TAG, "Using token: ${token.take(20)}...")
            
            val commentRequest = CommentRequest(
                idKitab = idKitab,
                userId = userId,
                isiComment = comment
            )
            
            val response = apiService.submitComment(idKitab, commentRequest, "Bearer $token")
            
            Log.d(TAG, "Submit comment response code: ${response.code()}")
            Log.d(TAG, "Submit comment response successful: ${response.isSuccessful}")
            
            if (response.isSuccessful) {
                val body = response.body()
                if (body != null) {
                    Log.d(TAG, "✅ Submit comment success: ${body.message}")
                    Result.success(body)
                } else {
                    Log.e(TAG, "❌ Submit comment response body is null")
                    Result.failure(Exception("Response body is null"))
                }
            } else {
                val errorMessage = when (response.code()) {
                    401 -> "Unauthorized - Token mungkin expired"
                    403 -> "Akses ditolak"
                    404 -> "Kitab tidak ditemukan"
                    422 -> "Validation error - komentar tidak valid"
                    500 -> "Server error"
                    else -> "Error: ${response.code()}"
                }
                Log.e(TAG, "❌ Submit comment failed: $errorMessage")
                Result.failure(Exception(errorMessage))
            }
        } catch (e: Exception) {
            Log.e(TAG, "Failed to submit comment", e)
            Result.failure(e)
        }
    }

    /**
     * Delete a comment (Protected route - requires auth)
     * DELETE /api/comment/{id}
     */
    suspend fun deleteComment(commentId: Int, token: String): Result<ApiResponse<Map<String, String>>> {
        return try {
            Log.d(TAG, "Deleting comment ID: $commentId (Protected route)")
            Log.d(TAG, "Using token: ${token.take(20)}...")
            
            val response = apiService.deleteComment(commentId, "Bearer $token")
            
            Log.d(TAG, "Delete comment response code: ${response.code()}")
            Log.d(TAG, "Delete comment response successful: ${response.isSuccessful}")
            
            if (response.isSuccessful) {
                val body = response.body()
                if (body != null) {
                    Log.d(TAG, "✅ Delete comment success: ${body.message}")
                    Result.success(body)
                } else {
                    Log.e(TAG, "❌ Delete comment response body is null")
                    Result.failure(Exception("Response body is null"))
                }
            } else {
                val errorMessage = when (response.code()) {
                    401 -> "Unauthorized - Token mungkin expired"
                    403 -> "Akses ditolak"
                    404 -> "Komentar tidak ditemukan"
                    500 -> "Server error"
                    else -> "Error: ${response.code()}"
                }
                Log.e(TAG, "❌ Delete comment failed: $errorMessage")
                Result.failure(Exception(errorMessage))
            }
        } catch (e: Exception) {
            Log.e(TAG, "Failed to delete comment", e)
            Result.failure(e)
        }
    }
    /**
     * Download Kitab PDF
     * GET /api/kitab/{id_kitab}/download
     */
    suspend fun downloadKitab(idKitab: Int, token: String): Result<okhttp3.ResponseBody> {
        return try {
            Log.d(TAG, "Downloading PDF for kitab ID: $idKitab")
            Log.d(TAG, "Using token: ${token.take(20)}...")

            val response = apiService.downloadKitab(idKitab, "Bearer $token")

            Log.d(TAG, "Download response code: ${response.code()}")
            Log.d(TAG, "Download response successful: ${response.isSuccessful}")

            if (response.isSuccessful) {
                val body = response.body()
                if (body != null) {
                    Log.d(TAG, "✅ Download success, content length: ${body.contentLength()}")
                    Result.success(body)
                } else {
                    Log.e(TAG, "❌ Download response body is null")
                    Result.failure(Exception("Response body is null"))
                }
            } else {
                val errorMessage = when (response.code()) {
                    401 -> "Unauthorized - Token mungkin expired"
                    404 -> "File tidak ditemukan di server"
                    500 -> "Server error"
                    else -> "Error: ${response.code()}"
                }
                Log.e(TAG, "❌ Download failed: $errorMessage")
                Result.failure(Exception(errorMessage))
            }
        } catch (e: Exception) {
            Log.e(TAG, "Failed to download kitab", e)
            Result.failure(e)
        }
    }

    /**
     * Rate a kitab
     * POST /api/kitab/{id_kitab}/rate
     */
    suspend fun rateKitab(idKitab: Int, rating: Int, token: String): Result<RateKitabResponse> {
        return try {
            val response = apiService.rateKitab(idKitab, rating, "Bearer $token")
            if (response.isSuccessful && response.body() != null) {
                Result.success(response.body()!!)
            } else {
                val errorMsg = response.errorBody()?.string() ?: response.message()
                Result.failure(Exception(errorMsg))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    /**
     * Get user's rating for a kitab
     * GET /api/kitab/{id_kitab}/my-rating
     */
    suspend fun getMyRating(idKitab: Int, token: String): Result<MyRatingResponse> {
        return try {
            val response = apiService.getMyRating(idKitab, "Bearer $token")
            if (response.isSuccessful && response.body() != null) {
                Result.success(response.body()!!)
            } else {
                val errorMsg = response.errorBody()?.string() ?: response.message()
                Result.failure(Exception(errorMsg))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }
}