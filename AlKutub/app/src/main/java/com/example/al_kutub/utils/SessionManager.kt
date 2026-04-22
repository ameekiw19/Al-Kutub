package com.example.al_kutub.utils

import android.content.Context
import android.content.SharedPreferences
import android.util.Log
import com.example.al_kutub.model.NotificationPreferences
import com.google.gson.Gson
import com.google.gson.reflect.TypeToken
import dagger.hilt.android.qualifiers.ApplicationContext
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class SessionManager @Inject constructor(@ApplicationContext private val context: Context) {
    private val prefs: SharedPreferences = context.getSharedPreferences("AL_KUTUB_PREFS", Context.MODE_PRIVATE)
    private val gson = Gson()

    companion object {
        private const val PREF_NAME = "AL_KUTUB_PREFS"
        private const val KEY_TOKEN = "auth_token"
        private const val KEY_REFRESH_TOKEN = "refresh_token"
        private const val KEY_USER_ID = "user_id"
        private const val KEY_USERNAME = "username"
        private const val KEY_NOTIFICATION_PREFERENCES = "notification_preferences"
        private const val KEY_SEARCH_HISTORY = "search_history"
        private const val KEY_USER_PREFERENCES = "user_preferences"
        private const val TAG = "SessionManager"

        @Volatile
        private var INSTANCE: SessionManager? = null

        fun getInstance(context: Context): SessionManager {
            return INSTANCE ?: synchronized(this) {
                INSTANCE ?: SessionManager(context).also { INSTANCE = it }
            }
        }
    }

    // ✅ PERBAIKAN: Simpan token
    fun saveToken(token: String) {
        prefs.edit().putString(KEY_TOKEN, token).apply()
        Log.d(TAG, "✅ Token saved successfully")
    }

    // ✅ PERBAIKAN: Ambil token
    fun getToken(): String? {
        val token = prefs.getString(KEY_TOKEN, null)

        // Validasi token tidak kosong dan bukan string "null"
        val validToken = if (token.isNullOrEmpty() || token == "null") {
            // Jangan log sebagai error - null token normal saat user belum login
            null
        } else {
            Log.d(TAG, "✅ Valid token found")
            token
        }

        return validToken
    }

    fun saveUserId(userId: Int) {
        prefs.edit().putInt(KEY_USER_ID, userId).apply()
        Log.d(TAG, "User ID saved: $userId")
    }

    fun getUserId(): Int {
        return prefs.getInt(KEY_USER_ID, 0)
    }

    fun saveUsername(username: String) {
        prefs.edit().putString(KEY_USERNAME, username).apply()
        Log.d(TAG, "Username saved: $username")
    }

    fun getUsername(): String? {
        return prefs.getString(KEY_USERNAME, null)
    }

    // ✅ PERBAIKAN: Clear token dengan benar
    fun clearToken() {
        prefs.edit().remove(KEY_TOKEN).apply()  // ✅ Gunakan remove(), bukan putString(null)
        Log.d(TAG, "✅ Token cleared successfully")
    }

    fun saveRefreshToken(refreshToken: String) {
        prefs.edit().putString(KEY_REFRESH_TOKEN, refreshToken).apply()
    }

    fun getRefreshToken(): String? {
        return prefs.getString(KEY_REFRESH_TOKEN, null)
    }

    fun clearRefreshToken() {
        prefs.edit().remove(KEY_REFRESH_TOKEN).apply()
    }

    // ✅ PERBAIKAN: Logout - clear semua data
    fun logout() {
        prefs.edit().clear().apply()
        Log.d(TAG, "✅ All session data cleared")
    }

    // Backward-compatible alias used by sync/auth flows
    fun clearAuthSession() {
        logout()
    }

    // ✅ PERBAIKAN: Check login status
    fun isLoggedIn(): Boolean {
        val token = getToken()
        val loggedIn = !token.isNullOrEmpty()
        Log.d(TAG, "Is logged in: $loggedIn")
        return loggedIn
    }

    // Reading Progress Methods
    fun saveReadingProgress(userId: Int, progressJson: String) {
        prefs.edit()
            .putString("reading_progress_$userId", progressJson)
            .apply()
    }
    
    fun getReadingProgress(userId: Int): String? {
        return prefs.getString("reading_progress_$userId", null)
    }
    
    fun clearReadingProgress(userId: Int) {
        prefs.edit()
            .remove("reading_progress_$userId")
            .apply()
    }

    // Debug function
    fun debugState() {
        Log.d(TAG, "=== SESSION DEBUG ===")
        Log.d(TAG, "Token present: ${!getToken().isNullOrEmpty()}")
        Log.d(TAG, "User ID: ${getUserId()}")
        Log.d(TAG, "Username: ${getUsername()}")
        Log.d(TAG, "Is logged in: ${isLoggedIn()}")
        Log.d(TAG, "====================")
    }
    
    // ===== NOTIFICATION PREFERENCES =====
    
    fun saveNotificationPreferences(preferences: NotificationPreferences) {
        val json = gson.toJson(preferences)
        prefs.edit().putString(KEY_NOTIFICATION_PREFERENCES, json).apply()
        Log.d(TAG, "✅ Notification preferences saved")
    }
    
    fun getNotificationPreferences(): NotificationPreferences {
        val json = prefs.getString(KEY_NOTIFICATION_PREFERENCES, null)
        return if (json != null) {
            try {
                gson.fromJson(json, NotificationPreferences::class.java)
            } catch (e: Exception) {
                Log.e(TAG, "Error parsing notification preferences", e)
                NotificationPreferences() // Return default
            }
        } else {
            NotificationPreferences() // Return default
        }
    }
    
    // ===== SEARCH HISTORY =====
    
    fun saveSearchHistory(history: List<String>) {
        val json = gson.toJson(history)
        prefs.edit().putString(KEY_SEARCH_HISTORY, json).apply()
        Log.d(TAG, "✅ Search history saved (${history.size} items)")
    }
    
    fun getSearchHistory(): List<String> {
        val json = prefs.getString(KEY_SEARCH_HISTORY, null)
        return if (json != null) {
            try {
                val type = object : TypeToken<List<String>>() {}.type
                gson.fromJson(json, type) ?: emptyList()
            } catch (e: Exception) {
                Log.e(TAG, "Error parsing search history", e)
                emptyList()
            }
        } else {
            emptyList()
        }
    }
    
    fun addToSearchHistory(query: String) {
        val currentHistory = getSearchHistory().toMutableList()
        currentHistory.remove(query) // Remove if exists
        currentHistory.add(0, query) // Add to beginning
        
        // Keep only last 20 searches
        val newHistory = currentHistory.take(20)
        saveSearchHistory(newHistory)
    }
    
    fun clearSearchHistory() {
        prefs.edit().remove(KEY_SEARCH_HISTORY).apply()
        Log.d(TAG, "✅ Search history cleared")
    }
    
    // ===== USER PREFERENCES =====
    
    fun saveUserPreference(key: String, value: Any) {
        val currentPrefs = getUserPreferences().toMutableMap()
        currentPrefs[key] = value.toString()
        val json = gson.toJson(currentPrefs)
        prefs.edit().putString(KEY_USER_PREFERENCES, json).apply()
    }
    
    fun getUserPreference(key: String, defaultValue: String = ""): String {
        val prefs = getUserPreferences()
        return prefs[key] ?: defaultValue
    }
    
    fun getUserPreferences(): Map<String, String> {
        val json = prefs.getString(KEY_USER_PREFERENCES, null)
        return if (json != null) {
            try {
                val type = object : TypeToken<Map<String, String>>() {}.type
                gson.fromJson(json, type) ?: emptyMap()
            } catch (e: Exception) {
                Log.e(TAG, "Error parsing user preferences", e)
                emptyMap()
            }
        } else {
            emptyMap()
        }
    }
    
    fun clearUserPreferences() {
        prefs.edit().remove(KEY_USER_PREFERENCES).apply()
        Log.d(TAG, "✅ User preferences cleared")
    }
}
