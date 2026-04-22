package com.example.al_kutub.data.repository

import com.example.al_kutub.api.ApiService
import com.example.al_kutub.model.UiKitab
import com.example.al_kutub.model.SearchResponse
import com.example.al_kutub.utils.toUiKitab

import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class HomeRepository @Inject constructor(private val api: ApiService) {
    suspend fun fetchUiKitabList(): Result<List<UiKitab>> {
        return try {
            val response = api.getAllKitab()
            if (response.isSuccessful) {
                val kitabList = response.body()?.data?.map { it.toUiKitab() } ?: emptyList()
                Result.success(kitabList)
            } else {
                Result.failure(Exception("Error ${response.code()}: ${response.message()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun fetchRecommendations(token: String? = null): Result<List<UiKitab>> {
        return try {
            val authHeader = token?.let { "Bearer $it" }
            val response = api.getRecommendations(authHeader)
            if (response.isSuccessful) {
                val kitabList = response.body()?.data?.map { it.toUiKitab() } ?: emptyList()
                Result.success(kitabList)
            } else {
                Result.failure(Exception("Error ${response.code()}: ${response.message()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun searchKitab(
        query: String,
        limit: Int = 20,
        kategori: String? = null,
        bahasa: String? = null,
        sortBy: String = "relevance",
        sortOrder: String = "desc"
    ): Result<SearchResponse> {
        return try {
            val response = api.searchKitab(
                query = query,
                limit = limit,
                offset = 0,
                categories = kategori,
                authors = null,
                languages = bahasa,
                sortBy = sortBy,
                sortOrder = sortOrder
            )
            if (response.isSuccessful) {
                val searchResponse = response.body()
                if (searchResponse != null) {
                    Result.success(searchResponse)
                } else {
                    Result.failure(Exception("Search response is null"))
                }
            } else {
                Result.failure(Exception("Error ${response.code()}: ${response.message()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }
}
