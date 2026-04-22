package com.example.al_kutub

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.Scaffold
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.navigation.compose.currentBackStackEntryAsState
import androidx.navigation.compose.rememberNavController
import com.example.al_kutub.ui.components.AlKutubSplashScreen
import com.example.al_kutub.ui.components.BottomNav
import com.example.al_kutub.ui.components.BottomTab
import com.example.al_kutub.ui.navigation.AppScreen
import com.example.al_kutub.ui.navigation.Screen
import com.example.al_kutub.ui.theme.AlKutubTheme
import com.example.al_kutub.utils.ThemeManager
import com.example.al_kutub.model.ThemeMode
import dagger.hilt.android.AndroidEntryPoint
import android.Manifest
import android.content.Intent
import android.os.Build
import android.content.pm.PackageManager
import androidx.activity.result.contract.ActivityResultContracts
import androidx.core.content.ContextCompat
import kotlinx.coroutines.delay
import javax.inject.Inject

@AndroidEntryPoint
class MainActivity : ComponentActivity() {

    @Inject
    lateinit var themeManager: ThemeManager

    private val requestPermissionLauncher = registerForActivityResult(
        ActivityResultContracts.RequestPermission()
    ) { isGranted: Boolean ->
        if (isGranted) {
            android.util.Log.d("MainActivity", "Notification permission granted")
        } else {
            android.util.Log.w("MainActivity", "Notification permission denied")
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        
        // Apply saved theme
        val savedTheme = themeManager.getSavedTheme()
        themeManager.applyTheme(savedTheme)
        
        // Handle notification intent
        handleNotificationIntent(intent)
        
        // Request Notification Permission for Android 13+
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            if (ContextCompat.checkSelfPermission(this, Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED) {
                requestPermissionLauncher.launch(Manifest.permission.POST_NOTIFICATIONS)
            }
        }

        enableEdgeToEdge()
        setContent {
            // Collect theme preference from DataStore
            val themeMode by themeManager.themeMode.collectAsState(initial = savedTheme)
            val isSystemDark = isSystemInDarkTheme()
            
            // Determine dark theme based on mode
            val isDarkTheme = when (themeMode) {
                ThemeMode.LIGHT -> false
                ThemeMode.DARK -> true
                ThemeMode.AUTO -> isSystemDark
            }

            AlKutubTheme(darkTheme = isDarkTheme) {
                var showSplash by remember { mutableStateOf(true) }

                LaunchedEffect(Unit) {
                    delay(2500)
                    showSplash = false
                }

                if (showSplash) {
                    AlKutubSplashScreen()
                } else {
                    val navController = rememberNavController()
                    val prefs = getSharedPreferences("AL_KUTUB_PREFS", MODE_PRIVATE)

                    LaunchedEffect(Unit) {
                        val pendingKitabId = prefs.getInt("pending_kitab_id", -1)
                        val openKitabDetail = prefs.getBoolean("open_kitab_detail", false)
                        val openNotifications = intent?.getBooleanExtra("open_notifications", false) ?: false

                        when {
                            openKitabDetail && pendingKitabId > 0 -> {
                                navController.navigate("${AppScreen.KitabDetail.route}/$pendingKitabId")
                                prefs.edit()
                                    .remove("pending_kitab_id")
                                    .remove("open_kitab_detail")
                                    .apply()
                            }
                            openNotifications -> {
                                navController.navigate(AppScreen.Notifications.route)
                            }
                        }
                    }

                    val navBackStackEntry by navController.currentBackStackEntryAsState()
                    val currentRoute = navBackStackEntry?.destination?.route

                    val isKatalogRoute = currentRoute?.startsWith(AppScreen.Katalog.route) == true
                    val isKitabDetailRoute = currentRoute?.startsWith(AppScreen.KitabDetail.route) == true

                    // Determine active tab based on current route
                    val activeTab = when (currentRoute) {
                        AppScreen.Home.route -> BottomTab.HOME
                        AppScreen.Bookmark.route -> BottomTab.BOOKMARK
                        AppScreen.History.route -> BottomTab.RIWAYAT
                        AppScreen.Account.route -> BottomTab.AKUN
                        else -> when {
                            isKatalogRoute || isKitabDetailRoute -> BottomTab.KATALOG
                            else -> BottomTab.HOME
                        }
                    }

                    // Show BottomNav on main screens (Home, Katalog, Bookmark, History, Account)
                    val showBottomBar = currentRoute == AppScreen.Home.route ||
                        isKatalogRoute ||
                        currentRoute == AppScreen.Bookmark.route ||
                        currentRoute == AppScreen.History.route ||
                        currentRoute == AppScreen.Account.route

                    Scaffold(
                        modifier = Modifier,
                        bottomBar = {
                            if (showBottomBar) {
                                BottomNav(
                                    activeTab = activeTab,
                                    onTabChange = { tab ->
                                        when (tab) {
                                            BottomTab.HOME -> {
                                                navController.navigate(AppScreen.Home.route) {
                                                    popUpTo(AppScreen.Home.route) { inclusive = true }
                                                    launchSingleTop = true
                                                }
                                            }
                                            BottomTab.KATALOG -> {
                                                navController.navigate(AppScreen.Katalog.route) {
                                                    popUpTo(AppScreen.Home.route) { saveState = true }
                                                    launchSingleTop = true
                                                    restoreState = true
                                                }
                                            }
                                            BottomTab.BOOKMARK -> {
                                                navController.navigate(AppScreen.Bookmark.route) {
                                                    popUpTo(AppScreen.Home.route) { saveState = true }
                                                    launchSingleTop = true
                                                    restoreState = true
                                                }
                                            }
                                            BottomTab.RIWAYAT -> {
                                                navController.navigate(AppScreen.History.route) {
                                                    popUpTo(AppScreen.Home.route) { saveState = true }
                                                    launchSingleTop = true
                                                    restoreState = true
                                                }
                                            }
                                            BottomTab.AKUN -> {
                                                navController.navigate(AppScreen.Account.route) {
                                                    popUpTo(AppScreen.Home.route) { saveState = true }
                                                    launchSingleTop = true
                                                    restoreState = true
                                                }
                                            }
                                            // TODO: Implement navigation for other tabs
                                            else -> {
                                                // Handle other tabs when implemented
                                            }
                                        }
                                    }
                                )
                            }
                        }
                    ) { innerPadding ->
                        Screen(
                            navController = navController,
                            innerPadding = innerPadding,
                            themeManager = themeManager
                        )
                    }
                }
            }
        }
    }
    
    private fun handleNotificationIntent(intent: Intent?) {
        // Cek apakah app dibuka dari notification
        val kitabId = intent?.getIntExtra("kitab_id", -1) ?: -1
        val openKitabDetail = intent?.getBooleanExtra("open_kitab_detail", false) ?: false
        
        // Simpan ke SharedPreferences untuk diakses nanti
        if (kitabId != -1 && openKitabDetail) {
            val prefs = getSharedPreferences("AL_KUTUB_PREFS", MODE_PRIVATE)
            prefs.edit()
                .putInt("pending_kitab_id", kitabId)
                .putBoolean("open_kitab_detail", true)
                .apply()
            android.util.Log.d("MainActivity", "Notification intent processed: Kitab ID $kitabId")
        }
    }
}
