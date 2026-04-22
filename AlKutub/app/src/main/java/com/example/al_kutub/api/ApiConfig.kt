package com.example.al_kutub.api

import android.net.Uri
import com.example.al_kutub.data.remote.NotificationApiService
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.net.URI
import java.util.concurrent.TimeUnit

object ApiConfig {
    // TODO: GANTI URL INI DENGAN URL NGROK ANDA (https://xxxx.ngrok-free.app/api/v1/)
    private const val BASE_URL = "http://10.0.2.2:8000/api/v1/" // UNTUK EMULATOR LOKAL - pakai v1 untuk versioning
    // private const val BASE_URL = "YOUR_NGROK_URL_HERE/api/v1/" // GANTI INI DENGAN URL NGROK ANDA
    private const val BASE_WEB_URL = "http://10.0.2.2:8000/" // Base untuk share link kitab (web route)
    fun getKitabShareUrl(kitabId: Int): String = "${BASE_WEB_URL}kitab/view/$kitabId"

    fun getWebAssetUrl(path: String?): String {
        if (path.isNullOrBlank()) return ""
        val normalizedPath = path.trim().replace("\\", "/")

        if (normalizedPath.startsWith("http", ignoreCase = true)) {
            return rewriteLocalAbsoluteUrl(normalizedPath)
        }

        val cleanPath = normalizedPath
            .removePrefix("./")
            .removePrefix("/")
            .removePrefix("public/")

        if (cleanPath.isBlank()) return ""

        val encodedPath = encodePath(cleanPath)
        return "${BASE_WEB_URL}$encodedPath"
    }

    fun getCoverUrl(path: String?): String {
        if (path.isNullOrBlank()) return ""
        val normalizedPath = path.trim().replace("\\", "/")

        if (normalizedPath.startsWith("http", ignoreCase = true)) {
            return rewriteLocalAbsoluteUrl(normalizedPath)
        }

        val cleanPath = normalizedPath
            .removePrefix("./")
            .removePrefix("/")
            .removePrefix("public/")

        val encodedPath = encodePath(cleanPath)

        return when {
            cleanPath.startsWith("cover/") -> "${BASE_WEB_URL}$encodedPath"
            cleanPath.startsWith("storage/") -> "${BASE_WEB_URL}$encodedPath"
            cleanPath.contains("/") -> getWebAssetUrl(cleanPath)
            else -> "${BASE_WEB_URL}cover/${Uri.encode(cleanPath)}"
        }
    }

    private fun rewriteLocalAbsoluteUrl(url: String): String {
        return try {
            val uri = URI(url)
            val host = uri.host?.lowercase().orEmpty()
            val encodedPath = encodePath(uri.path.orEmpty().removePrefix("/"))
            if (host == "localhost" || host == "127.0.0.1" || host == "::1") {
                "${BASE_WEB_URL}$encodedPath"
            } else {
                URI(
                    uri.scheme,
                    uri.userInfo,
                    uri.host,
                    uri.port,
                    "/$encodedPath",
                    uri.query,
                    uri.fragment
                ).toASCIIString()
            }
        } catch (_: Exception) {
            url
        }
    }

    private fun encodePath(path: String): String {
        if (path.isBlank()) return path
        return path
            .split("/")
            .filter { it.isNotEmpty() }
            .joinToString("/") { segment -> Uri.encode(segment) }
    }

    fun getApiService(): ApiService {
        val loggingInterceptor = HttpLoggingInterceptor().apply {
            level = HttpLoggingInterceptor.Level.BODY
        }
        
        val client = OkHttpClient.Builder()
            .addInterceptor(loggingInterceptor)
            .connectTimeout(30, TimeUnit.SECONDS) // Connection timeout
            .readTimeout(30, TimeUnit.SECONDS)    // Read timeout
            .writeTimeout(30, TimeUnit.SECONDS)   // Write timeout
            .retryOnConnectionFailure(true)       // Retry on connection failure
            .build()
            
        val retrofit = Retrofit.Builder()
            .baseUrl(BASE_URL)
            .addConverterFactory(GsonConverterFactory.create())
            .client(client)
            .build()
        return retrofit.create(ApiService::class.java)
    }

    // Backward-compatible overload for older call sites that still pass SessionManager.
    fun getApiService(
        @Suppress("UNUSED_PARAMETER") sessionManager: com.example.al_kutub.utils.SessionManager
    ): ApiService = getApiService()
    
    fun getNotificationApiService(): NotificationApiService {
        val loggingInterceptor = HttpLoggingInterceptor().apply {
            level = HttpLoggingInterceptor.Level.BODY
        }
        
        val client = OkHttpClient.Builder()
            .addInterceptor(loggingInterceptor)
            .connectTimeout(30, TimeUnit.SECONDS) // Connection timeout
            .readTimeout(30, TimeUnit.SECONDS)    // Read timeout
            .writeTimeout(30, TimeUnit.SECONDS)   // Write timeout
            .retryOnConnectionFailure(true)       // Retry on connection failure
            .build()
            
        val retrofit = Retrofit.Builder()
            .baseUrl(BASE_URL)
            .addConverterFactory(GsonConverterFactory.create())
            .client(client)
            .build()
        return retrofit.create(NotificationApiService::class.java)
    }

    // Backward-compatible overload for older call sites that still pass SessionManager.
    fun getNotificationApiService(
        @Suppress("UNUSED_PARAMETER") sessionManager: com.example.al_kutub.utils.SessionManager
    ): NotificationApiService = getNotificationApiService()
}
