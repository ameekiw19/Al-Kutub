package com.example.al_kutub.utils

import android.content.Context
import android.content.SharedPreferences
import androidx.appcompat.app.AppCompatDelegate
import androidx.compose.runtime.Composable
import androidx.compose.runtime.remember
import androidx.compose.ui.platform.LocalContext
import androidx.datastore.core.DataStore
import androidx.datastore.preferences.core.Preferences
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.stringPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
import com.example.al_kutub.model.ThemeMode
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.map
import javax.inject.Inject
import javax.inject.Singleton

// Extension property for creating DataStore instance
private val Context.dataStore: DataStore<Preferences> by preferencesDataStore(name = "settings")

@Singleton
class ThemeManager @Inject constructor(
    @ApplicationContext private val context: Context
) {
    companion object {
        private val THEME_MODE = stringPreferencesKey("theme_mode")
    }

    private val prefs: SharedPreferences = context.getSharedPreferences("theme_prefs", Context.MODE_PRIVATE)

    /**
     * Flow that emits the current theme preference
     */
    val themeMode: Flow<ThemeMode> = context.dataStore.data.map { preferences ->
        val themeValue = preferences[THEME_MODE] ?: ThemeMode.LIGHT.value
        ThemeMode.fromString(themeValue)
    }

    /**
     * Get saved theme preference
     */
    fun getSavedTheme(): ThemeMode {
        val themeValue = prefs.getString("theme_mode", ThemeMode.LIGHT.value)
        return ThemeMode.fromString(themeValue ?: ThemeMode.LIGHT.value)
    }

    /**
     * Save theme preference locally
     */
    suspend fun saveTheme(theme: ThemeMode) {
        context.dataStore.edit { preferences ->
            preferences[THEME_MODE] = theme.value
        }
        prefs.edit()
            .putString("theme_mode", theme.value)
            .apply()
    }

    /**
     * Apply theme to the app
     */
    fun applyTheme(theme: ThemeMode) {
        val mode = when (theme) {
            ThemeMode.LIGHT -> AppCompatDelegate.MODE_NIGHT_NO
            ThemeMode.DARK -> AppCompatDelegate.MODE_NIGHT_YES
            ThemeMode.AUTO -> AppCompatDelegate.MODE_NIGHT_FOLLOW_SYSTEM
        }
        AppCompatDelegate.setDefaultNightMode(mode)
    }

    /**
     * Check if dark mode is currently active
     */
    fun isDarkMode(): Boolean {
        return when (AppCompatDelegate.getDefaultNightMode()) {
            AppCompatDelegate.MODE_NIGHT_YES -> true
            AppCompatDelegate.MODE_NIGHT_NO -> false
            AppCompatDelegate.MODE_NIGHT_FOLLOW_SYSTEM -> {
                // Follow system setting
                android.content.res.Configuration.UI_MODE_NIGHT_MASK == 
                    android.content.res.Configuration.UI_MODE_NIGHT_YES
            }
            else -> false
        }
    }

    /**
     * Get current theme mode
     */
    fun getCurrentTheme(): ThemeMode {
        return when (AppCompatDelegate.getDefaultNightMode()) {
            AppCompatDelegate.MODE_NIGHT_NO -> ThemeMode.LIGHT
            AppCompatDelegate.MODE_NIGHT_YES -> ThemeMode.DARK
            AppCompatDelegate.MODE_NIGHT_FOLLOW_SYSTEM -> ThemeMode.AUTO
            else -> getSavedTheme()
        }
    }

    /**
     * Toggle between light and dark mode
     */
    suspend fun toggleTheme() {
        val currentTheme = getCurrentTheme()
        val newTheme = when (currentTheme) {
            ThemeMode.LIGHT -> ThemeMode.DARK
            ThemeMode.DARK -> ThemeMode.LIGHT
            ThemeMode.AUTO -> if (isDarkMode()) ThemeMode.LIGHT else ThemeMode.DARK
        }
        applyTheme(newTheme)
        saveTheme(newTheme)
    }
}

/**
 * Composable to get current theme
 */
@Composable
fun rememberThemeManager(): ThemeManager {
    val context = LocalContext.current
    return remember { ThemeManager(context) }
}
