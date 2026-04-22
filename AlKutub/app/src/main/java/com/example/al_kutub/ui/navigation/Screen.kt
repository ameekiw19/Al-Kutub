package com.example.al_kutub.ui.navigation

import android.app.Activity
import android.content.Intent
import android.util.Log
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.ui.platform.LocalContext
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.padding
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.navigation.NavHostController
import androidx.navigation.NavType
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.navArgument
import com.example.al_kutub.ui.screens.AccountScreen
import com.example.al_kutub.ui.screens.AchievementsGalleryScreen
import com.example.al_kutub.ui.screens.BookmarkScreen
import com.example.al_kutub.ui.screens.KatalogScreen
import com.example.al_kutub.ui.screens.KitabDetailScreen
import com.example.al_kutub.ui.screens.HomeScreen
import com.example.al_kutub.ui.screens.LeaderboardScreen
import com.example.al_kutub.ui.screens.LoginScreen
import com.example.al_kutub.ui.screens.RegisterScreen
import com.example.al_kutub.ui.screens.HistoryScreen
import com.example.al_kutub.ui.screens.NotificationsScreen
import com.example.al_kutub.ui.screens.PdfViewerScreen
import com.example.al_kutub.ui.screens.EditProfileScreen
import com.example.al_kutub.ui.screens.ReadingGoalsScreen
import com.example.al_kutub.ui.screens.ReadingStatisticsScreen
import com.example.al_kutub.ui.screens.SearchScreen
import com.example.al_kutub.ui.screens.TwoFactorVerificationActivity
import com.example.al_kutub.ui.viewmodel.LoginViewModel
import com.example.al_kutub.utils.ThemeManager

private const val TAG = "NavigationScreen"

@Composable
fun Screen(
    navController: NavHostController, 
    innerPadding: PaddingValues = PaddingValues(),
    themeManager: ThemeManager? = null
) {
    NavHost(
        navController = navController, 
        startDestination = AppScreen.Login.route,
        modifier = Modifier.padding(innerPadding)
    ) {

        // ===== LOGIN SCREEN =====
        composable(AppScreen.Login.route) {
            val loginViewModel: LoginViewModel = hiltViewModel()
            val context = LocalContext.current
            val twoFALauncher = rememberLauncherForActivityResult(
                contract = ActivityResultContracts.StartActivityForResult()
            ) { result ->
                if (result.resultCode == Activity.RESULT_OK) {
                    navController.navigate(AppScreen.Home.route) {
                        popUpTo(AppScreen.Login.route) { inclusive = true }
                    }
                }
            }

            LoginScreen(
                viewModel = loginViewModel,
                onNavigateToRegister = {
                    navController.navigate(AppScreen.Register.route)
                },
                onLoginSuccess = {
                    navController.navigate(AppScreen.Home.route) {
                        popUpTo(AppScreen.Login.route) { inclusive = true }
                    }
                },
                onNavigateTo2FA = { userId, tempToken, username ->
                    val intent = Intent(context, TwoFactorVerificationActivity::class.java).apply {
                        putExtra("USER_ID", userId)
                        putExtra("TEMP_TOKEN", tempToken)
                        putExtra("USERNAME", username)
                    }
                    twoFALauncher.launch(intent)
                }
            )
        }

        // ===== REGISTER SCREEN =====
        composable(AppScreen.Register.route) {
            RegisterScreen(
                onNavigateToLogin = {
                    navController.navigate(AppScreen.Login.route) {
                        popUpTo(AppScreen.Register.route) { inclusive = true }
                    }
                },
                onRegisterSuccess = {
                    navController.navigate(AppScreen.Home.route) {
                        popUpTo(AppScreen.Register.route) { inclusive = true }
                    }
                }
            )
        }

        // ===== HOME SCREEN =====
        composable(AppScreen.Home.route) {
            HomeScreen(navController = navController)
        }

        // ===== NOTIFICATIONS SCREEN =====
        composable(AppScreen.Notifications.route) {
            NotificationsScreen(
                onOpenKitab = { kitabId ->
                    navController.navigate("${AppScreen.KitabDetail.route}/$kitabId")
                }
            )
        }

        // ===== SEARCH SCREEN =====
        composable(
            route = AppScreen.Search.routePattern,
            arguments = listOf(
                navArgument(AppScreen.Search.QUERY_ARG) {
                    type = NavType.StringType
                    nullable = true
                    defaultValue = null
                },
                navArgument(AppScreen.Search.CATEGORY_ARG) {
                    type = NavType.StringType
                    nullable = true
                    defaultValue = null
                },
                navArgument(AppScreen.Search.LANGUAGE_ARG) {
                    type = NavType.StringType
                    nullable = true
                    defaultValue = null
                },
                navArgument(AppScreen.Search.SORT_ARG) {
                    type = NavType.StringType
                    nullable = true
                    defaultValue = null
                }
            )
        ) { backStackEntry ->
            val searchViewModel: com.example.al_kutub.ui.viewmodel.AdvancedSearchViewModel = hiltViewModel()
            val initialQuery = backStackEntry.arguments?.getString(AppScreen.Search.QUERY_ARG)
            val initialCategory = backStackEntry.arguments?.getString(AppScreen.Search.CATEGORY_ARG)
            val initialLanguage = backStackEntry.arguments?.getString(AppScreen.Search.LANGUAGE_ARG)
            val initialSort = backStackEntry.arguments?.getString(AppScreen.Search.SORT_ARG)

            SearchScreen(
                viewModel = searchViewModel,
                initialQuery = initialQuery,
                initialCategory = initialCategory,
                initialLanguage = initialLanguage,
                initialSort = initialSort,
                onNavigateBack = {
                    navController.popBackStack()
                },
                onKitabClick = { kitab ->
                    Log.d(TAG, "════════════════════════════════════════")
                    Log.d(TAG, "🚀 NAVIGATION TRIGGERED FROM SEARCH")
                    Log.d(TAG, "Kitab ID: ${kitab.idKitab}")
                    Log.d(TAG, "Kitab Judul: ${kitab.judul}")

                    val route = "${AppScreen.KitabDetail.route}/${kitab.idKitab}"
                    Log.d(TAG, "Navigation Route: $route")
                    Log.d(TAG, "════════════════════════════════════════")

                    navController.navigate(route)
                }
            )
        }

        // ===== KATALOG SCREEN =====
        composable(
            route = "${AppScreen.Katalog.route}?category={category}",
            arguments = listOf(
                navArgument("category") {
                    type = NavType.StringType
                    nullable = true
                    defaultValue = null
                }
            )
        ) { backStackEntry ->
            val initialCategory = backStackEntry.arguments?.getString("category")

            KatalogScreen(
                initialCategory = initialCategory,
                onOpenSearch = { category, language, sort ->
                    navController.navigate(
                        AppScreen.Search.createRoute(
                            category = category,
                            language = language,
                            sort = sort
                        )
                    )
                },
                onKitabClick = { kitabId ->
                    val route = "${AppScreen.KitabDetail.route}/$kitabId"
                    navController.navigate(route)
                }
            )
        }

        // ===== KITAB DETAIL SCREEN =====
        composable(
            route = "${AppScreen.KitabDetail.route}/{kitabId}",
            arguments = listOf(
                navArgument("kitabId") {
                    type = NavType.IntType
                    defaultValue = 0
                }
            )
        ) { backStackEntry ->
            val kitabId = backStackEntry.arguments?.getInt("kitabId") ?: 0

            Log.d(TAG, "════════════════════════════════════════")
            Log.d(TAG, "📖 KITAB DETAIL SCREEN COMPOSABLE")
            Log.d(TAG, "Received kitabId: $kitabId")
            Log.d(TAG, "Full route: ${backStackEntry.destination.route}")
            Log.d(TAG, "All arguments: ${backStackEntry.arguments}")

            if (kitabId == 0) {
                Log.e(TAG, "⚠️ WARNING: kitabId is 0! This might be a problem!")
            }
            Log.d(TAG, "════════════════════════════════════════")

            KitabDetailScreen(
                kitabId = kitabId,
                onBack = {
                    Log.d(TAG, "Back button pressed from KitabDetail")
                    navController.popBackStack()
                },
                onNavigateToPdf = { id, data ->
                    val encodedData = java.net.URLEncoder.encode(data, "UTF-8")
                    navController.navigate("${AppScreen.PdfViewer.route}/$id/$encodedData")
                },
                onNavigateToKitabDetail = { relatedKitabId ->
                    navController.navigate("${AppScreen.KitabDetail.route}/$relatedKitabId")
                }
            )
        }

        // ===== HISTORY SCREEN =====
        composable(AppScreen.History.route) {
            Log.d(TAG, "════════════════════════════════════════")
            Log.d(TAG, "📚 HISTORY SCREEN COMPOSABLE")
            Log.d(TAG, "Full route: ${AppScreen.History.route}")
            Log.d(TAG, "════════════════════════════════════════")

            HistoryScreen(navController = navController)
        }

        // ===== READING GOALS SCREEN (NEW/UPDATED) =====
        composable("reading_goals_screen") {
            ReadingGoalsScreen(navController = navController)
        }

        // ===== BOOKMARK SCREEN =====
        composable(AppScreen.Bookmark.route) {
            Log.d(TAG, "════════════════════════════════════════")
            Log.d(TAG, "🔖 BOOKMARK SCREEN COMPOSABLE")
            Log.d(TAG, "Full route: ${AppScreen.Bookmark.route}")
            Log.d(TAG, "════════════════════════════════════════")

            BookmarkScreen(
                onKitabClick = { kitabId ->
                    Log.d(TAG, "🚀 NAVIGATION TRIGGERED FROM BOOKMARK")
                    Log.d(TAG, "Kitab ID: $kitabId")

                    val route = "${AppScreen.KitabDetail.route}/$kitabId"
                    Log.d(TAG, "Navigation Route: $route")
                    Log.d(TAG, "════════════════════════════════════════")

                    navController.navigate(route)
                },
                onNavigateToCatalog = {
                    Log.d(TAG, "Navigate to Catalog from Bookmark")
                    navController.navigate(AppScreen.Katalog.route)
                }
            )
        }

        // ===== ACCOUNT SCREEN =====
        composable(AppScreen.Account.route) {
            Log.d(TAG, "════════════════════════════════════════")
            Log.d(TAG, "👤 ACCOUNT SCREEN COMPOSABLE")
            Log.d(TAG, "Full route: ${AppScreen.Account.route}")
            Log.d(TAG, "════════════════════════════════════════")

            AccountScreen(
                themeManager = themeManager!!,
                onNavigateToLogin = {
                    Log.d(TAG, "Logout - navigating to Login")
                    navController.navigate(AppScreen.Login.route) {
                        popUpTo(0) { inclusive = true  }
                    }
                },
                onNavigateToRegister = {
                    navController.navigate(AppScreen.Register.route)
                },
                onNavigateToEditProfile = {
                    navController.navigate(AppScreen.EditProfile.route)
                },
                onNavigateToBookmarks = {
                    navController.navigate(AppScreen.Bookmark.route)
                },
                onNavigateToHistory = {
                    navController.navigate(AppScreen.History.route)
                },
                onNavigateToKitabDetail = { kitabId ->
                    navController.navigate("${AppScreen.KitabDetail.route}/$kitabId")
                }
            )
        }

        // ===== READING STATISTICS SCREEN (NEW) =====
        composable("reading_statistics_screen") {
            ReadingStatisticsScreen(navController = navController)
        }

        // ===== EDIT PROFILE SCREEN =====
        composable(AppScreen.EditProfile.route) {
            EditProfileScreen(
                onBack = { navController.popBackStack() },
                onSuccess = { navController.popBackStack() }
            )
        }

        // ===== PDF VIEWER SCREEN =====
        composable(
            route = "${AppScreen.PdfViewer.route}/{kitabId}/{data}",
            arguments = listOf(
                navArgument("kitabId") { type = NavType.IntType },
                navArgument("data") { type = NavType.StringType }
            )
        ) { backStackEntry ->
            val kitabId = backStackEntry.arguments?.getInt("kitabId") ?: 0
            val data = backStackEntry.arguments?.getString("data") ?: ""
            val decodedData = java.net.URLDecoder.decode(data, "UTF-8")
            
            val parts = decodedData.split("|")
            val filePath = parts[0]
            val initialPage = if (parts.size > 1) parts[1].toInt() else 1

            PdfViewerScreen(
                kitabId = kitabId,
                filePath = filePath,
                initialPage = initialPage,
                onBack = { navController.popBackStack() }
            )
        }

        // ===== LEADERBOARD SCREEN =====
        composable(AppScreen.Leaderboard.route) {
            LeaderboardScreen(onBack = { navController.popBackStack() })
        }

        // ===== ACHIEVEMENTS SCREEN =====
        composable(AppScreen.Achievements.route) {
            AchievementsGalleryScreen(onBack = { navController.popBackStack() })
        }
    }
}
