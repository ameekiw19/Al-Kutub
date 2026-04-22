package com.example.al_kutub.data.repository

import com.example.al_kutub.api.ApiService
import com.example.al_kutub.model.LoginResponse
import com.example.al_kutub.utils.ApiErrorMapper
import retrofit2.HttpException
import java.io.IOException
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class LoginRepository @Inject constructor(private val apiService: ApiService) {

    suspend fun loginUser(username: String, password: String): Result<LoginResponse> {
        return try {
            val response = apiService.loginUser(username, password)
            if (response.isSuccessful) {
                response.body()?.let { loginResponse ->
                    Result.success(loginResponse)
                } ?: Result.failure(Exception("Response body is null"))
            } else {
                Result.failure(Exception(ApiErrorMapper.map(response.code(), response.message())))
            }
        } catch (e: HttpException) {
            Result.failure(Exception(ApiErrorMapper.map(e.code(), e.message())))
        } catch (e: IOException) {
            Result.failure(Exception("Kesalahan koneksi: Pastikan Anda terhubung ke internet."))
        } catch (e: Exception) {
            Result.failure(Exception("Terjadi kesalahan: ${e.localizedMessage}"))
        }
    }
}
