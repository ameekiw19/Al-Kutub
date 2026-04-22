package com.example.al_kutub.data.repository

import com.example.al_kutub.api.ApiService
import com.example.al_kutub.model.KatalogResponse
import javax.inject.Inject
import javax.inject.Singleton

/**
 * Repository untuk mengelola data katalog kitab
 * Menggunakan pattern Repository untuk memisahkan logika data dari UI
 */
@Singleton
class KatalogRepository @Inject constructor(
    private val apiService: ApiService
) {

    suspend fun getKatalog(
        sortBy: String = "latest",
        sortOrder: String = "desc",
        perPage: Int = 50
    ): Result<KatalogResponse> {
        return try {
            val response = apiService.getKatalog(
                perPage = perPage,
                sortBy = sortBy,
                sortOrder = sortOrder
            )
            if (response.isSuccessful && response.body() != null) {
                Result.success(response.body()!!)
            } else {
                Result.failure(Exception("Failed to get katalog: ${response.message()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun filterKatalog(
        kategori: String? = null,
        bahasa: String? = null,
        search: String? = null,
        sortBy: String = "latest",
        sortOrder: String = "desc",
        perPage: Int = 50
    ): Result<KatalogResponse> {
        return try {
            val response = apiService.filterKatalog(
                kategori = kategori,
                bahasa = bahasa,
                search = search,
                sortBy = sortBy,
                sortOrder = sortOrder,
                perPage = perPage
            )
            if (response.isSuccessful && response.body() != null) {
                Result.success(response.body()!!)
            } else {
                Result.failure(Exception("Failed to filter katalog: ${response.message()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

//    /**
//     * Toggle bookmark (add/remove)
//     * POST /api/bookmark/{id_kitab}
//     * Requires authentication token
//     */
//    suspend fun toggleBookmark(
//        idKitab: Int,
//        token: String
//    ): Result<BookmarkResponse> {
//        return try {
//            val response = apiService.toggleBookmark(idKitab, "Bearer $token")
//            if (response.isSuccessful && response.body() != null) {
//                Result.success(response.body()!!)
//            } else {
//                Result.failure(Exception("Failed to toggle bookmark: ${response.message()}"))
//            }
//        } catch (e: Exception) {
//            Result.failure(e)
//        }
//    }
//
//    /**
//     * Increment view count
//     * POST /api/kitab/{id_kitab}/view
//     * Requires authentication token
//     */
//    suspend fun incrementView(
//        idKitab: Int,
//        token: String
//    ): Result<ViewResponse> {
//        return try {
//            val response = apiService.incrementView(idKitab, "Bearer $token")
//            if (response.isSuccessful && response.body() != null) {
//                Result.success(response.body()!!)
//            } else {
//                Result.failure(Exception("Failed to increment view: ${response.message()}"))
//            }
//        } catch (e: Exception) {
//            Result.failure(e)
//        }
//    }
//
//    /**
//     * Get katalog with Flow for reactive updates
//     * Useful untuk LiveData/StateFlow di ViewModel
//     */
//    fun getKatalogFlow(): Flow<Result<KatalogResponse>> = flow {
//        try {
//            val response = apiService.getKatalog()
//            if (response.isSuccessful && response.body() != null) {
//                emit(Result.success(response.body()!!))
//            } else {
//                emit(Result.failure(Exception("Failed to get katalog: ${response.message()}")))
//            }
//        } catch (e: Exception) {
//            emit(Result.failure(e))
//        }
//    }
//
//    /**
//     * Filter kitab with Flow
//     */
//    fun filterKitabFlow(
//        kategori: String? = null,
//        bahasa: String? = null
//    ): Flow<Result<FilterResponse>> = flow {
//        try {
//            val response = apiService.filterKitab(kategori, bahasa)
//            if (response.isSuccessful && response.body() != null) {
//                emit(Result.success(response.body()!!))
//            } else {
//                emit(Result.failure(Exception("Failed to filter kitab: ${response.message()}")))
//            }
//        } catch (e: Exception) {
//            emit(Result.failure(e))
//        }
//    }
//
//    /**
//     * Check if kitab is bookmarked
//     * Helper function untuk cek status bookmark
//     */
//    fun isBookmarked(idKitab: Int, bookmarkedIds: List<Int>): Boolean {
//        return bookmarkedIds.contains(idKitab)
//    }
//
//    /**
//     * Get filtered kitab list by category
//     * Helper function untuk filter lokal
//     */
//    fun getKitabByCategory(kitabList: List<Kitab>, category: String): List<Kitab> {
//        return if (category.equals("Semua", ignoreCase = true)) {
//            kitabList
//        } else {
//            kitabList.filter { it.category.equals(category, ignoreCase = true) }
//        }
//    }
//
//    /**
//     * Get filtered kitab list by language
//     * Helper function untuk filter lokal
//     */
//    fun getKitabByLanguage(kitabList: List<Kitab>, language: String): List<Kitab> {
//        return kitabList.filter { it.language.equals(language, ignoreCase = true) }
//    }
//
//    /**
//     * Search kitab by title or author
//     * Helper function untuk search
//     */
//    fun searchKitab(kitabList: List<Kitab>, query: String): List<Kitab> {
//        return if (query.isBlank()) {
//            kitabList
//        } else {
//            kitabList.filter {
//                it.title.contains(query, ignoreCase = true) ||
//                        it.author.contains(query, ignoreCase = true)
//            }
//        }
//    }
}
