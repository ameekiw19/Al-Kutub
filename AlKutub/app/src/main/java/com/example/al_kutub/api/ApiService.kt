package com.example.al_kutub.api

import com.example.al_kutub.model.ApiResponse
import com.example.al_kutub.model.Comment
import com.example.al_kutub.model.CommentRequest
import com.example.al_kutub.model.Kitab
import com.example.al_kutub.model.LoginResponse
import com.example.al_kutub.model.*
import com.example.al_kutub.model.MyRatingResponse
import com.example.al_kutub.model.RateKitabResponse

import retrofit2.Response
import retrofit2.http.*

interface ApiService {
    // ===== AUTHENTICATION =====
    @FormUrlEncoded
    @POST("login")
    suspend fun loginUser(
        @Field("username") username: String,
        @Field("password") password: String
    ): Response<LoginResponse>

    @FormUrlEncoded
    @POST("register")
    suspend fun registerUser(
        @Field("username") username: String,
        @Field("password") password: String,
        @Field("email") email: String,
        @Field("phone") phone: String?,
        @Field("deskripsi") deskripsi: String? = null,
        @Field("role") role: String = "user"
    ): Response<LoginResponse>

    // ===== ACCOUNT MANAGEMENT (NEW) =====

    // Get profil user + statistik + 5 aktivitas terbaru
    @GET("account")
    suspend fun getAccount(
        @Header("Authorization") authorization: String
    ): Response<AccountResponse>

    // Update profile
    @PUT("account")
    suspend fun updateProfile(
        @Header("Authorization") authorization: String,
        @Body request: UpdateProfileRequest
    ): Response<UpdateProfileResponse>

    // Get semua riwayat baca (pagination)
    @GET("account/history")
    suspend fun getAccountHistory(
        @Header("Authorization") authorization: String
    ): Response<AccountHistoryResponse>

    // Get semua bookmark (pagination)
    @GET("account/bookmarks")
    suspend fun getAccountBookmarks(
        @Header("Authorization") authorization: String
    ): Response<AccountBookmarksResponse>

    // Get semua komentar user (pagination)
    @GET("account/comments")
    suspend fun getAccountComments(
        @Header("Authorization") authorization: String
    ): Response<AccountCommentsResponse>

    // Logout (alternative)
    @POST("account/logout")
    suspend fun accountLogout(
        @Header("Authorization") authorization: String
    ): Response<LogoutResponse>

    // ===== KITAB =====
    @GET("kitab")
    suspend fun getAllKitab(): Response<ApiResponse<List<Kitab>>>

    @GET("kitab/recommendations")
    suspend fun getRecommendations(
        @Header("Authorization") authorization: String? = null
    ): Response<ApiResponse<List<Kitab>>>

    @GET("kitab/{id_kitab}")
    suspend fun getKitabDetail(
        @Path("id_kitab") id: Int
    ): Response<ApiResponse<Kitab>>

    @GET("kitab/{id_kitab}/transcript")
    suspend fun getKitabTranscript(
        @Path("id_kitab") id: Int
    ): Response<ApiResponse<KitabTranscriptPayload>>

    @GET("kitab/{id_kitab}/related")
    suspend fun getRelatedKitab(
        @Path("id_kitab") id: Int
    ): Response<ApiResponse<List<Kitab>>>

    @GET("kitab/{id_kitab}/download")
    @Streaming
    suspend fun downloadKitab(
        @Path("id_kitab") id: Int,
        @Header("Authorization") authorization: String,
        @Header("Range") range: String? = null
    ): Response<okhttp3.ResponseBody>

    @POST("kitab/{id_kitab}/view")
    suspend fun incrementView(
        @Path("id_kitab") id: Int,
        @Header("Authorization") authorization: String
    ): Response<ApiResponse<Map<String, String>>>

    @GET("kitab/{id_kitab}/comments")
    suspend fun getComments(
        @Path("id_kitab") idKitab: Int
    ): Response<ApiResponse<List<Comment>>>

    @POST("kitab/{id_kitab}/comment")
    suspend fun submitComment(
        @Path("id_kitab") idKitab: Int,
        @Body commentRequest: CommentRequest,
        @Header("Authorization") authorization: String
    ): Response<ApiResponse<Comment>>

    // ===== SEARCH =====
    @GET("kitab/search")
    suspend fun searchKitab(
        @Query("search") query: String = "",
        @Query("limit") limit: Int = 20,
        @Query("offset") offset: Int = 0,
        @Query("categories") categories: String? = null,
        @Query("authors") authors: String? = null,
        @Query("languages") languages: String? = null,
        @Query("sort_by") sortBy: String = "relevance",
        @Query("sort_order") sortOrder: String = "desc"
    ): Response<SearchResponse>

    // ===== SEARCH SUGGESTIONS =====
    @GET("search/suggestions")
    suspend fun getSearchSuggestions(
        @Query("query") query: String,
        @Query("limit") limit: Int = 10
    ): Response<SearchSuggestionsResponse>

    // ===== SEARCH HISTORY =====
    @GET("search/history")
    suspend fun getSearchHistory(
        @Header("Authorization") authorization: String
    ): Response<SearchHistoryResponse>

    @POST("search/history")
    suspend fun saveSearchHistory(
        @Header("Authorization") authorization: String,
        @Body request: SaveSearchHistoryRequest
    ): Response<SearchHistorySaveResponse>

    @DELETE("search/history/{id}")
    suspend fun deleteSearchHistory(
        @Path("id") historyId: Int,
        @Header("Authorization") authorization: String
    ): Response<ApiResponse<Map<String, String>>>

    @DELETE("search/history")
    suspend fun clearSearchHistory(
        @Header("Authorization") authorization: String
    ): Response<ApiResponse<Map<String, Int>>>

    @DELETE("comment/{id}")
    suspend fun deleteComment(
        @Path("id") commentId: Int,
        @Header("Authorization") authorization: String
    ): Response<ApiResponse<Map<String, String>>>

    // ===== KATALOG =====
    @GET("katalog")
    suspend fun getKatalog(
        @Query("per_page") perPage: Int = 50,
        @Query("sort_by") sortBy: String = "latest",
        @Query("sort_order") sortOrder: String = "desc"
    ): Response<KatalogResponse>

    @GET("katalog/filter")
    suspend fun filterKatalog(
        @Query("kategori") kategori: String? = null,
        @Query("bahasa") bahasa: String? = null,
        @Query("search") search: String? = null,
        @Query("sort_by") sortBy: String = "latest",
        @Query("sort_order") sortOrder: String = "desc",
        @Query("per_page") perPage: Int = 50
    ): Response<KatalogResponse>

    @GET("katalog")
    suspend fun Katalog(): Response<ApiResponse<List<Kitab>>>

    // ===== AUTH RECOVERY =====
    @FormUrlEncoded
    @POST("password/forgot")
    suspend fun forgotPassword(
        @Field("email") email: String
    ): Response<VerificationStatusResponse>

    @FormUrlEncoded
    @POST("email/verification/resend")
    suspend fun resendEmailVerification(
        @Field("verification_token") verificationToken: String
    ): Response<VerificationStatusResponse>

    @FormUrlEncoded
    @POST("email/verification/status")
    suspend fun checkEmailVerificationStatus(
        @Field("verification_token") verificationToken: String
    ): Response<VerificationStatusResponse>

    // ===== HISTORY =====
    @GET("history")
    suspend fun getHistories(
        @Header("Authorization") authorization: String
    ): Response<ApiResponse<HistoryData>>

    @GET("history/{id}")
    suspend fun getHistoryDetail(
        @Path("id") historyId: Int,
        @Header("Authorization") authorization: String
    ): Response<HistoryDetailResponse>

    @FormUrlEncoded
    @POST("history")
    suspend fun addOrUpdateHistory(
        @Field("kitab_id") kitabId: Int,
        @Field("current_page") currentPage: Int? = null,
        @Field("total_pages") totalPages: Int? = null,
        @Field("last_position") lastPosition: String? = null,
        @Field("reading_time_minutes") readingTimeMinutes: Int? = null,
        @Field("reading_time_added") readingTimeAdded: Int? = null,
        @Field("client_updated_at") clientUpdatedAt: String? = null,
        @Header("Authorization") authorization: String
    ): Response<HistoryDetailResponse>

    @DELETE("history/{id}")
    suspend fun deleteHistory(
        @Path("id") historyId: Int,
        @Header("Authorization") authorization: String
    ): Response<BaseResponse>

    @DELETE("historyclearall")
    suspend fun clearAllHistory(
        @Header("Authorization") authorization: String
    ): Response<ClearHistoryResponse>

    @GET("history/stats/summary")
    suspend fun getHistoryStatistics(
        @Header("Authorization") authorization: String
    ): Response<HistoryStatsResponse>

    // ===== BOOKMARK =====
    @GET("bookmarks")
    suspend fun getAllBookmarks(
        @Header("Authorization") authorization: String
    ): Response<BookmarkResponse>

    @POST("bookmarks/{id_kitab}/toggle")
    suspend fun toggleBookmark(
        @Path("id_kitab") idKitab: Int,
        @Header("Authorization") authorization: String
    ): Response<BookmarkToggleResponse>

    @GET("bookmarks/check/{id_kitab}")
    suspend fun checkBookmark(
        @Path("id_kitab") idKitab: Int,
        @Header("Authorization") authorization: String
    ): Response<BookmarkCheckResponse>

    @FormUrlEncoded
    @POST("bookmarks")
    suspend fun addBookmark(
        @Field("id_kitab") idKitab: Int,
        @Header("Authorization") authorization: String
    ): Response<BookmarkToggleResponse>


    @DELETE("bookmarks/{id_kitab}")
    suspend fun deleteBookmark(
        @Path("id_kitab") idKitab: Int,
        @Header("Authorization") authorization: String
    ): Response<BookmarkDeleteResponse>

    @DELETE("bookmarks/clear-all")
    suspend fun clearAllBookmarks(
        @Header("Authorization") authorization: String
    ): Response<BookmarkDeleteResponse>

    @GET("bookmarks/stats")
    suspend fun getBookmarkStats(
        @Header("Authorization") authorization: String
    ): Response<BookmarkStatsResponse>

    // ===== PAGE BOOKMARKS =====
    @GET("page-bookmarks")
    suspend fun getPageBookmarks(
        @Header("Authorization") authorization: String,
        @Query("kitab_id") kitabId: Int? = null
    ): Response<ApiResponse<List<PageBookmarkRemoteData>>>

    @FormUrlEncoded
    @POST("page-bookmarks")
    suspend fun upsertPageBookmark(
        @Field("kitab_id") kitabId: Int,
        @Field("page_number") pageNumber: Int,
        @Field("label") label: String,
        @Field("client_updated_at") clientUpdatedAt: String? = null,
        @Header("Authorization") authorization: String
    ): Response<ApiResponse<PageBookmarkRemoteData>>

    @DELETE("page-bookmarks/{kitab_id}/{page_number}")
    suspend fun deletePageBookmark(
        @Path("kitab_id") kitabId: Int,
        @Path("page_number") pageNumber: Int,
        @Header("Authorization") authorization: String
    ): Response<ApiResponse<Map<String, Int>>>
    @GET("notifications/latest")
    suspend fun getLatestNotification(
        @Header("Authorization") authorization: String? = null
    ): Response<NotificationResponse>

    // ===== FCM TOKEN API =====
    @POST("fcm/token")
    suspend fun saveFcmToken(
        @Header("Authorization") authorization: String,
        @Body request: FcmTokenRequest
    ): Response<FcmTokenResponse>

    @DELETE("fcm/token")
    suspend fun removeFcmToken(
        @Header("Authorization") authorization: String,
        @Body request: FcmTokenRemoveRequest
    ): Response<FcmTokenResponse>

    @POST("fcm/test")
    suspend fun testFcmNotification(
        @Header("Authorization") authorization: String,
        @Body request: FcmTestRequest
    ): Response<FcmTestResponse>
    @FormUrlEncoded
    @POST("kitab/{id_kitab}/rate")
    suspend fun rateKitab(
        @Path("id_kitab") idKitab: Int,
        @Field("rating") rating: Int,
        @Header("Authorization") authorization: String
    ): Response<RateKitabResponse>

    @GET("kitab/{id_kitab}/my-rating")
    suspend fun getMyRating(
        @Path("id_kitab") idKitab: Int,
        @Header("Authorization") authorization: String
    ): Response<MyRatingResponse>

    // ===== READING NOTES =====
    @GET("reading-notes")
    suspend fun getReadingNotes(
        @Query("kitab_id") kitabId: Int? = null,
        @Header("Authorization") authorization: String
    ): Response<ReadingNotesResponse>

    @POST("reading-notes")
    suspend fun createReadingNote(
        @Body request: CreateReadingNoteRequest,
        @Header("Authorization") authorization: String
    ): Response<ReadingNoteBaseResponse>

    @GET("reading-notes/{noteId}")
    suspend fun getReadingNote(
        @Path("noteId") noteId: Int,
        @Header("Authorization") authorization: String
    ): Response<ReadingNoteBaseResponse>

    @PUT("reading-notes/{noteId}")
    suspend fun updateReadingNote(
        @Path("noteId") noteId: Int,
        @Body request: UpdateReadingNoteRequest,
        @Header("Authorization") authorization: String
    ): Response<ReadingNoteBaseResponse>

    @DELETE("reading-notes/{noteId}")
    suspend fun deleteReadingNote(
        @Path("noteId") noteId: Int,
        @Header("Authorization") authorization: String
    ): Response<ReadingNoteBaseResponse>

    @GET("reading-notes/stats")
    suspend fun getReadingNotesStats(
        @Header("Authorization") authorization: String
    ): Response<ReadingNotesStatsResponse>

    // ===== READING GOALS & STREAK =====
    @GET("reading-goals")
    suspend fun getReadingGoals(
        @Header("Authorization") authorization: String
    ): Response<ReadingGoalsResponse>

    @GET("reading-streak")
    suspend fun getReadingStreak(
        @Header("Authorization") authorization: String
    ): Response<ReadingStreakResponse>

    // ===== NOTIFICATION SETTINGS =====
    @GET("notifications/settings")
    suspend fun getNotificationSettings(
        @Header("Authorization") authorization: String
    ): Response<NotificationSettingsResponse>

    @PUT("notifications/settings")
    suspend fun updateNotificationSettings(
        @Body request: NotificationSettingsRequest,
        @Header("Authorization") authorization: String
    ): Response<NotificationSettingsResponse>

    // ===== THEME =====
    @GET("theme")
    suspend fun getTheme(
        @Header("Authorization") authorization: String
    ): Response<ThemeResponse>

    @POST("theme")
    suspend fun updateTheme(
        @Body request: ThemeRequest,
        @Header("Authorization") authorization: String
    ): Response<ThemeResponse>

    @POST("theme/toggle")
    suspend fun toggleTheme(
        @Header("Authorization") authorization: String
    ): Response<ThemeResponse>

    // ===== GAMIFICATION (LEADERBOARD & ACHIEVEMENTS) =====
    @GET("reading-streak/leaderboard")
    suspend fun getLeaderboard(
        @Header("Authorization") authorization: String,
        @Query("limit") limit: Int = 10
    ): Response<com.example.al_kutub.model.LeaderboardResponse>

    @GET("reading-goals/achievements")
    suspend fun getAchievements(
        @Header("Authorization") authorization: String
    ): Response<com.example.al_kutub.model.AchievementResponse>

    // ===== TWO-FACTOR AUTHENTICATION (2FA) =====
    @FormUrlEncoded
    @POST("login/verify-2fa")
    suspend fun verify2FALogin(
        @Field("user_id") userId: Int,
        @Field("code") code: String,
        @Field("temp_token") tempToken: String
    ): Response<LoginResponse>

    @GET("2fa/status")
    suspend fun get2FAStatus(
        @Header("Authorization") authorization: String
    ): Response<TwoFactorStatusResponse>

    @POST("2fa/setup")
    suspend fun setup2FA(
        @Header("Authorization") authorization: String
    ): Response<TwoFactorSetupResponse>

    @FormUrlEncoded
    @POST("2fa/enable")
    suspend fun enable2FA(
        @Header("Authorization") authorization: String,
        @Field("code") code: String
    ): Response<TwoFactorEnableResponse>

    @FormUrlEncoded
    @POST("2fa/disable")
    suspend fun disable2FA(
        @Header("Authorization") authorization: String,
        @Field("password") password: String,
        @Field("code") code: String
    ): Response<BaseResponse>

    @FormUrlEncoded
    @POST("2fa/verify")
    suspend fun verify2FA(
        @Header("Authorization") authorization: String,
        @Field("code") code: String
    ): Response<BaseResponse>

    @GET("2fa/backup-codes")
    suspend fun getBackupCodes(
        @Header("Authorization") authorization: String
    ): Response<BackupCodesResponse>

    @FormUrlEncoded
    @POST("2fa/regenerate-backup-codes")
    suspend fun regenerateBackupCodes(
        @Header("Authorization") authorization: String,
        @Field("password") password: String
    ): Response<BackupCodesResponse>

    @FormUrlEncoded
    @POST("2fa/verify-backup-code")
    suspend fun verifyBackupCode(
        @Header("Authorization") authorization: String,
        @Field("code") code: String
    ): Response<BaseResponse>

    // ===== AUDIT LOGGING =====
    @GET("audit")
    suspend fun getAuditLogs(
        @Header("Authorization") authorization: String,
        @Query("limit") limit: Int? = null,
        @Query("action") action: String? = null,
        @Query("date_from") dateFrom: String? = null,
        @Query("date_to") dateTo: String? = null
    ): Response<AuditLogsResponse>

    @GET("audit/security")
    suspend fun getSecurityLogs(
        @Header("Authorization") authorization: String,
        @Query("limit") limit: Int? = null,
        @Query("date_from") dateFrom: String? = null,
        @Query("date_to") dateTo: String? = null
    ): Response<AuditLogsResponse>

    @GET("audit/stats")
    suspend fun getAuditStats(
        @Header("Authorization") authorization: String
    ): Response<AuditStatsResponse>
}
