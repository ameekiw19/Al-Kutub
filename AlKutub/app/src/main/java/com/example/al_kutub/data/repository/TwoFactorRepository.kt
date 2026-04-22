package com.example.al_kutub.data.repository

import com.example.al_kutub.api.ApiService
import com.example.al_kutub.model.LoginResponse
import com.example.al_kutub.model.BaseResponse
import retrofit2.Response
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class TwoFactorRepository @Inject constructor(
    private val apiService: ApiService
) {

    /**
     * Verify 2FA code during login
     */
    suspend fun verify2FALogin(
        userId: Int,
        code: String,
        tempToken: String
    ): Response<LoginResponse> {
        return apiService.verify2FALogin(
            userId = userId,
            code = code,
            tempToken = tempToken
        )
    }

    /**
     * Get 2FA status
     */
    suspend fun get2FAStatus(
        authorization: String
    ): Response<com.example.al_kutub.model.TwoFactorStatusResponse> {
        return apiService.get2FAStatus(authorization)
    }

    /**
     * Setup 2FA
     */
    suspend fun setup2FA(
        authorization: String
    ): Response<com.example.al_kutub.model.TwoFactorSetupResponse> {
        return apiService.setup2FA(authorization)
    }

    /**
     * Enable 2FA
     */
    suspend fun enable2FA(
        authorization: String,
        code: String
    ): Response<com.example.al_kutub.model.TwoFactorEnableResponse> {
        return apiService.enable2FA(authorization, code)
    }

    /**
     * Disable 2FA
     */
    suspend fun disable2FA(
        authorization: String,
        password: String,
        code: String
    ): Response<BaseResponse> {
        return apiService.disable2FA(authorization, password, code)
    }

    /**
     * Verify 2FA code
     */
    suspend fun verify2FA(
        authorization: String,
        code: String
    ): Response<BaseResponse> {
        return apiService.verify2FA(authorization, code)
    }

    /**
     * Get backup codes
     */
    suspend fun getBackupCodes(
        authorization: String
    ): Response<com.example.al_kutub.model.BackupCodesResponse> {
        return apiService.getBackupCodes(authorization)
    }

    /**
     * Regenerate backup codes
     */
    suspend fun regenerateBackupCodes(
        authorization: String,
        password: String
    ): Response<com.example.al_kutub.model.BackupCodesResponse> {
        return apiService.regenerateBackupCodes(authorization, password)
    }

    /**
     * Verify backup code
     */
    suspend fun verifyBackupCode(
        authorization: String,
        code: String
    ): Response<BaseResponse> {
        return apiService.verifyBackupCode(authorization, code)
    }
}
