package com.example.al_kutub.ui.navigation

import android.net.Uri

sealed class AppScreen(val route: String) {
    object Login : AppScreen("login_screen")
    object Register : AppScreen("register_screen")
    object Home : AppScreen("home_screen")
    object Search : AppScreen("search_screen") {
        const val QUERY_ARG = "query"
        const val CATEGORY_ARG = "category"
        const val LANGUAGE_ARG = "language"
        const val SORT_ARG = "sort"

        val routePattern =
            "$route?$QUERY_ARG={$QUERY_ARG}&$CATEGORY_ARG={$CATEGORY_ARG}&$LANGUAGE_ARG={$LANGUAGE_ARG}&$SORT_ARG={$SORT_ARG}"

        fun createRoute(
            query: String? = null,
            category: String? = null,
            language: String? = null,
            sort: String? = null
        ): String {
            val params = listOfNotNull(
                query?.takeIf { it.isNotBlank() }?.let { "$QUERY_ARG=${Uri.encode(it)}" },
                category?.takeIf { it.isNotBlank() }?.let { "$CATEGORY_ARG=${Uri.encode(it)}" },
                language?.takeIf { it.isNotBlank() }?.let { "$LANGUAGE_ARG=${Uri.encode(it)}" },
                sort?.takeIf { it.isNotBlank() }?.let { "$SORT_ARG=${Uri.encode(it)}" }
            )

            return if (params.isEmpty()) {
                route
            } else {
                "$route?${params.joinToString("&")}"
            }
        }
    }
    object Katalog : AppScreen("katalog_screen")
    object KitabDetail : AppScreen("kitab_detail_screen")
    object History : AppScreen("history_screen")
    object Bookmark : AppScreen("bookmark_screen")
    object Account : AppScreen("account_screen")
    object PdfViewer : AppScreen("pdf_viewer_screen")
    object Notifications : AppScreen("notifications_screen")
    object EditProfile : AppScreen("edit_profile_screen")
    object Leaderboard : AppScreen("leaderboard_screen")
    object Achievements : AppScreen("achievements_screen")
}
