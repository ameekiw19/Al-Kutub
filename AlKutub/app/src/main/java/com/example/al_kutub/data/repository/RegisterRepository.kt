package com.example.al_kutub.data.repository

import android.util.Log
import com.example.al_kutub.api.ApiService
import com.example.al_kutub.model.LoginResponse
import retrofit2.HttpException
import java.io.IOException
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class RegisterRepository @Inject constructor(
    private val apiService: ApiService
) {

    private val TAG = "RegisterRepository"

    suspend fun registerUser(
        username: String,
        password: String,
        email: String,
        phone: String? = null,
        deskripsi: String? = null,
        role: String = "user"
    ): Result<LoginResponse> {
        return try {
            Log.d(TAG, "=== CALLING REGISTER API ===")
            Log.d(TAG, "Username: $username")
            Log.d(TAG, "Email: $email")
            Log.d(TAG, "Phone: $phone")

            val response = apiService.registerUser(
                username = username,
                password = password,
                email = email,
                phone = phone,
                deskripsi = deskripsi,
                role = role
            )

            Log.d(TAG, "API Response code: ${response.code()}")
            Log.d(TAG, "API Response successful: ${response.isSuccessful}")

            if (response.isSuccessful) {
                response.body()?.let { registerResponse ->
                    Log.d(TAG, "✅ Register successful")
                    Log.d(TAG, "Response: $registerResponse")
                    Result.success(registerResponse)
                } ?: run {
                    Log.e(TAG, "❌ Response body is null")
                    Result.failure(Exception("Response body is null"))
                }
            } else {
                val errorBody = response.errorBody()?.string()
                Log.e(TAG, "❌ Register failed: ${response.message()}")
                Log.e(TAG, "Error body: $errorBody")

                val errorMessage = when (response.code()) {
                    400 -> "Data tidak valid. Periksa kembali input Anda"
                    409 -> "Username atau email sudah terdaftar"
                    422 -> "Format data tidak sesuai"
                    500 -> "Terjadi kesalahan pada server"
                    else -> "Registrasi gagal: ${response.message()}"
                }

                Result.failure(Exception(errorMessage))
            }
        } catch (e: HttpException) {
            Log.e(TAG, "❌ HTTP Exception", e)
            Result.failure(Exception("Registrasi gagal: ${e.message}"))
        } catch (e: IOException) {
            Log.e(TAG, "❌ IO Exception", e)
            Result.failure(Exception("Kesalahan koneksi: Pastikan Anda terhubung ke internet"))
        } catch (e: Exception) {
            Log.e(TAG, "❌ Exception", e)
            Result.failure(Exception("Terjadi kesalahan: ${e.localizedMessage}"))
        }
    }
}