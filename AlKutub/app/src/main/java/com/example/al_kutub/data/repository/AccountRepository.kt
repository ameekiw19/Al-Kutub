package com.example.al_kutub.repository

import com.example.al_kutub.api.ApiService
import com.example.al_kutub.model.*
import com.example.al_kutub.utils.SessionManager
import retrofit2.Response
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class AccountRepository @Inject constructor(
    private val apiService: ApiService,
    private val sessionManager: SessionManager
) {
    private fun getAuthToken(): String {
        val token = sessionManager.getToken()
        return if (!token.isNullOrBlank()) {
            "Bearer $token"
        } else {
            ""
        }
    }

    /**
     * Get account data (profile + statistik + 5 aktivitas terbaru)
     */
    suspend fun getAccount(): Response<AccountResponse> {
        return apiService.getAccount(getAuthToken())
    }

    /**
     * Update profile user
     */
    suspend fun updateProfile(request: UpdateProfileRequest): Response<UpdateProfileResponse> {
        return apiService.updateProfile(getAuthToken(), request)
    }

    /**
     * Get semua riwayat baca
     */
    suspend fun getAccountHistory(): Response<AccountHistoryResponse> {
        return apiService.getAccountHistory(getAuthToken())
    }

    /**
     * Get semua bookmark
     */
    suspend fun getAccountBookmarks(): Response<AccountBookmarksResponse> {
        return apiService.getAccountBookmarks(getAuthToken())
    }

    /**
     * Get semua komentar user
     */
    suspend fun getAccountComments(): Response<AccountCommentsResponse> {
        return apiService.getAccountComments(getAuthToken())
    }

    /**
     * Logout user
     */
    suspend fun logout(): Response<LogoutResponse> {
        return apiService.accountLogout(getAuthToken())
    }
}