package com.example.al_kutub.data.repository

import android.util.Log
import com.example.al_kutub.api.ApiService
import com.example.al_kutub.model.ApiResponse
import com.example.al_kutub.model.Kitab
import com.example.al_kutub.model.UiKitab
import com.example.al_kutub.utils.toUiKitab
import com.google.gson.Gson
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class KitabRepository @Inject constructor(
    private val apiService: ApiService
) {
    private val TAG = "KitabRepository"
    private val gson = Gson()

    suspend fun getAllKitab(): Result<ApiResponse<List<UiKitab>>> {
        return try {
            Log.d(TAG, "════════════════════════════════════════")
            Log.d(TAG, "🔍 EXTREME DEBUG MODE - Fetching Kitab")
            Log.d(TAG, "════════════════════════════════════════")

            val response = apiService.getAllKitab()

            Log.d(TAG, "📡 RESPONSE INFO:")
            Log.d(TAG, "   Code: ${response.code()}")
            Log.d(TAG, "   Message: ${response.message()}")
            Log.d(TAG, "   Is Successful: ${response.isSuccessful}")
            Log.d(TAG, "   Headers: ${response.headers()}")

            if (response.isSuccessful) {
                val body = response.body()

                if (body != null) {
                    Log.d(TAG, "✅ RESPONSE BODY FOUND")
                    Log.d(TAG, "   Success: ${body.success}")
                    Log.d(TAG, "   Message: ${body.message}")

                    // 🔍 LOG RAW JSON
                    val rawJson = gson.toJson(body)
                    Log.d(TAG, "📄 RAW JSON RESPONSE:")
                    Log.d(TAG, rawJson)

                    val rawKitabList = body.data
                    Log.d(TAG, "")
                    Log.d(TAG, "📊 DATA ANALYSIS:")
                    Log.d(TAG, "   Data is null? ${rawKitabList == null}")
                    Log.d(TAG, "   Data size: ${rawKitabList?.size ?: 0}")
                    Log.d(TAG, "   Data type: ${rawKitabList?.javaClass?.simpleName}")

                    // 🔍 DETAIL SETIAP KITAB
                    rawKitabList?.forEachIndexed { index, kitab ->
                        Log.d(TAG, "")
                        Log.d(TAG, "📖 RAW KITAB [$index]:")
                        Log.d(TAG, "   toString(): $kitab")
                        Log.d(TAG, "   idKitab: ${kitab.idKitab}")
                        Log.d(TAG, "   judul: ${kitab.judul}")
                        Log.d(TAG, "   penulis: ${kitab.penulis}")
                        Log.d(TAG, "   kategori: ${kitab.kategori}")

                        // 🚨 CRITICAL CHECK
                        if (kitab.idKitab == 0) {
                            Log.e(TAG, "   ❌❌❌ WARNING: idKitab is 0! ❌❌❌")
                            Log.e(TAG, "   This means the ID field was NOT parsed correctly!")
                            Log.e(TAG, "   Check if backend sends 'idKitab' or 'id_kitab' or 'id'")

                            // Log sebagai JSON untuk inspect
                            val kitabJson = gson.toJson(kitab)
                            Log.e(TAG, "   Kitab as JSON: $kitabJson")
                        }
                    }

                    // Convert to UiKitab
                    val uiKitabList = rawKitabList?.map {
                        val ui = it.toUiKitab()
                        Log.d(TAG, "")
                        Log.d(TAG, "🔄 CONVERTED UI KITAB:")
                        Log.d(TAG, "   idKitab: ${ui.idKitab}")
                        Log.d(TAG, "   judul: ${ui.judul}")
                        ui
                    } ?: emptyList()

                    Log.d(TAG, "")
                    Log.d(TAG, "✅ CONVERSION COMPLETE")
                    Log.d(TAG, "   Total items: ${uiKitabList.size}")
                    Log.d(TAG, "════════════════════════════════════════")

                    Result.success(
                        ApiResponse(
                            success = body.success,
                            message = body.message,
                            data = uiKitabList
                        )
                    )
                } else {
                    Log.e(TAG, "❌ Response body is NULL")
                    Log.e(TAG, "════════════════════════════════════════")
                    Result.failure(Exception("Response body is null"))
                }
            } else {
                val errorBody = response.errorBody()?.string()
                val errorMsg = "HTTP ${response.code()}: ${response.message()}"

                Log.e(TAG, "❌ RESPONSE NOT SUCCESSFUL")
                Log.e(TAG, "   Error: $errorMsg")
                Log.e(TAG, "   Error Body: $errorBody")
                Log.e(TAG, "════════════════════════════════════════")

                Result.failure(Exception(errorMsg))
            }
        } catch (e: Exception) {
            Log.e(TAG, "❌❌❌ EXCEPTION CAUGHT ❌❌❌")
            Log.e(TAG, "Exception Type: ${e::class.java.simpleName}")
            Log.e(TAG, "Exception Message: ${e.message}")
            Log.e(TAG, "Stack Trace:")
            e.printStackTrace()
            Log.e(TAG, "════════════════════════════════════════")

            val userMessage = when (e) {
                is java.net.UnknownHostException -> "Tidak dapat terhubung ke server"
                is java.net.SocketTimeoutException -> "Koneksi timeout"
                is java.net.ConnectException -> "Gagal terhubung ke server"
                else -> "Error: ${e.message}"
            }

            Result.failure(Exception(userMessage))
        }
    }
}