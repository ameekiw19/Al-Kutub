package com.example.al_kutub.data.repository

import com.example.al_kutub.api.ApiService
import com.example.al_kutub.model.VerificationStatusData
import retrofit2.HttpException
import java.io.IOException
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class AuthRecoveryRepository @Inject constructor(
    private val apiService: ApiService
) {
    suspend fun requestPasswordReset(email: String): Result<String> {
        return try {
            val response = apiService.forgotPassword(email.trim())
            if (response.isSuccessful && response.body() != null) {
                Result.success(response.body()!!.message)
            } else {
                Result.failure(Exception("Permintaan reset password gagal."))
            }
        } catch (e: HttpException) {
            Result.failure(Exception("Permintaan reset password gagal."))
        } catch (e: IOException) {
            Result.failure(Exception("Tidak dapat terhubung ke server."))
        } catch (e: Exception) {
            Result.failure(Exception(e.message ?: "Terjadi kesalahan."))
        }
    }

    suspend fun resendVerification(verificationToken: String): Result<String> {
        return try {
            val response = apiService.resendEmailVerification(verificationToken)
            if (response.isSuccessful && response.body() != null) {
                Result.success(response.body()!!.message)
            } else {
                Result.failure(Exception("Gagal mengirim ulang verifikasi email."))
            }
        } catch (e: HttpException) {
            Result.failure(Exception("Gagal mengirim ulang verifikasi email."))
        } catch (e: IOException) {
            Result.failure(Exception("Tidak dapat terhubung ke server."))
        } catch (e: Exception) {
            Result.failure(Exception(e.message ?: "Terjadi kesalahan."))
        }
    }

    suspend fun checkVerificationStatus(verificationToken: String): Result<VerificationStatusData> {
        return try {
            val response = apiService.checkEmailVerificationStatus(verificationToken)
            if (response.isSuccessful && response.body() != null) {
                val body = response.body()!!
                val data = body.data ?: VerificationStatusData(verified = false)
                Result.success(data)
            } else {
                Result.failure(Exception("Status verifikasi tidak dapat diperiksa."))
            }
        } catch (e: HttpException) {
            Result.failure(Exception("Status verifikasi tidak dapat diperiksa."))
        } catch (e: IOException) {
            Result.failure(Exception("Tidak dapat terhubung ke server."))
        } catch (e: Exception) {
            Result.failure(Exception(e.message ?: "Terjadi kesalahan."))
        }
    }
}
