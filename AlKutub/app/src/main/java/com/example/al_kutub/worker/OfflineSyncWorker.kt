package com.example.al_kutub.worker

import android.content.Context
import androidx.work.CoroutineWorker
import androidx.work.WorkerParameters
import com.example.al_kutub.data.repository.DownloadManagerRepository
import com.example.al_kutub.data.repository.OfflineSyncRepository
import dagger.hilt.EntryPoint
import dagger.hilt.InstallIn
import dagger.hilt.android.EntryPointAccessors
import dagger.hilt.components.SingletonComponent

class OfflineSyncWorker(
    appContext: Context,
    workerParams: WorkerParameters
) : CoroutineWorker(appContext, workerParams) {

    @EntryPoint
    @InstallIn(SingletonComponent::class)
    interface OfflineSyncWorkerEntryPoint {
        fun offlineSyncRepository(): OfflineSyncRepository
        fun downloadManagerRepository(): DownloadManagerRepository
    }

    override suspend fun doWork(): Result {
        return try {
            val entryPoint = EntryPointAccessors.fromApplication(
                applicationContext,
                OfflineSyncWorkerEntryPoint::class.java
            )

            val syncResult = entryPoint.offlineSyncRepository().processQueue()
            entryPoint.downloadManagerRepository().processPendingTasks()
            if (syncResult.isSuccess) {
                Result.success()
            } else {
                val authError = syncResult.exceptionOrNull()?.message?.contains("AUTH_401") == true
                if (authError) Result.success() else Result.retry()
            }
        } catch (_: Exception) {
            Result.retry()
        }
    }

    companion object {
        const val UNIQUE_WORK_NAME = "offline_sync_worker"
    }
}
