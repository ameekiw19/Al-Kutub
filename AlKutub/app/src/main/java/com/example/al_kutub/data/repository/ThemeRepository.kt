package com.example.al_kutub.data.repository

import com.example.al_kutub.api.ApiService
import com.example.al_kutub.model.*
import retrofit2.Response
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class ThemeRepository @Inject constructor(
    private val apiService: ApiService
) {

    /**
     * Get user's theme preference
     */
    suspend fun getTheme(authorization: String): Response<ThemeResponse> {
        return apiService.getTheme(
            authorization = authorization
        )
    }

    /**
     * Update user's theme preference
     */
    suspend fun updateTheme(
        authorization: String,
        theme: ThemeMode
    ): Response<ThemeResponse> {
        return apiService.updateTheme(
            request = ThemeRequest(theme.value),
            authorization = authorization
        )
    }

    /**
     * Toggle theme (light/dark)
     */
    suspend fun toggleTheme(authorization: String): Response<ThemeResponse> {
        return apiService.toggleTheme(
            authorization = authorization
        )
    }
}
