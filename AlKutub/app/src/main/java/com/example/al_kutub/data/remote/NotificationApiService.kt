package com.example.al_kutub.data.remote

import com.example.al_kutub.model.AppNotification
import com.example.al_kutub.model.Kitab
import retrofit2.Response
import retrofit2.http.*

interface NotificationApiService {
    
    @GET("notifications")
    suspend fun getNotifications(
        @Header("Authorization") authorization: String,
        @Query("limit") limit: Int = 20,
        @Query("page") page: Int = 1
    ): Response<ApiResponse<List<AppNotification>>>
    
    @GET("notifications/latest")
    suspend fun getLatestNotifications(
        @Header("Authorization") authorization: String? = null,
        @Query("limit") limit: Int = 10,
        @Query("since") since: String? = null
    ): Response<ApiResponse<List<AppNotification>>>
    
    @GET("notifications/new-kitabs")
    suspend fun getNewKitabs(
        @Header("Authorization") authorization: String? = null,
        @Query("limit") limit: Int = 10,
        @Query("since") since: String? = null
    ): Response<ApiResponse<List<Kitab>>>
    
    @GET("notifications/unread-count")
    suspend fun getUnreadCount(
        @Header("Authorization") authorization: String
    ): Response<ApiResponse<Map<String, Int>>>

    @POST("notifications/{id}/read")
    suspend fun markAsRead(
        @Path("id") id: Int,
        @Header("Authorization") authorization: String
    ): Response<ApiResponse<Map<String, Any>>>

    @POST("notifications/read-all")
    suspend fun markAllAsRead(
        @Header("Authorization") authorization: String
    ): Response<ApiResponse<Map<String, Any>>>
}

data class ApiResponse<T>(
    val success: Boolean,
    val data: T? = null,
    val message: String? = null,
    val pagination: PaginationInfo? = null
)

data class PaginationInfo(
    val current_page: Int,
    val last_page: Int,
    val per_page: Int,
    val total: Int
)
