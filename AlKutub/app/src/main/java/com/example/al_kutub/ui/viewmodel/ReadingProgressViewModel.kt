package com.example.al_kutub.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.al_kutub.data.repository.HistoryRepository
import com.example.al_kutub.data.repository.ReadingProgressSyncPolicy
import com.example.al_kutub.data.repository.ReadingProgressRepository
import com.example.al_kutub.utils.SessionManager
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.launch
import java.time.Instant
import javax.inject.Inject

@HiltViewModel
class ReadingProgressViewModel @Inject constructor(
    private val repository: ReadingProgressRepository,
    private val historyRepository: HistoryRepository,
    private val sessionManager: SessionManager
) : ViewModel() {
    private val remoteSyncSnapshots = mutableMapOf<Int, ReadingProgressSyncPolicy.Snapshot>()

    fun updateReadingProgress(
        kitabId: Int,
        currentPage: Int,
        totalPages: Int,
        forceRemoteSync: Boolean = false
    ) {
        val userId = sessionManager.getUserId()
        if (userId <= 0) return

        val normalizedPage = currentPage.coerceAtLeast(1)
        val normalizedTotalPages = totalPages.coerceAtLeast(normalizedPage)

        viewModelScope.launch {
            repository.updateReadingProgress(
                userId = userId,
                kitabId = kitabId,
                currentPage = normalizedPage,
                totalPages = normalizedTotalPages
            )

            if (shouldSyncRemote(kitabId, normalizedPage, normalizedTotalPages, forceRemoteSync)) {
                historyRepository.addOrUpdateHistory(
                    kitabId = kitabId,
                    currentPage = normalizedPage,
                    totalPages = normalizedTotalPages,
                    clientUpdatedAt = Instant.now().toString()
                )
                remoteSyncSnapshots[kitabId] = ReadingProgressSyncPolicy.buildSnapshot(
                    page = normalizedPage,
                    totalPages = normalizedTotalPages
                )
            }
        }
    }

    fun flushProgress(kitabId: Int, currentPage: Int, totalPages: Int) {
        updateReadingProgress(
            kitabId = kitabId,
            currentPage = currentPage,
            totalPages = totalPages,
            forceRemoteSync = true
        )
    }

    fun addReadingTime(kitabId: Int, minutes: Int) {
        val userId = sessionManager.getUserId()
        if (userId <= 0 || minutes <= 0) return

        viewModelScope.launch {
            repository.addReadingTime(userId, kitabId, minutes)
        }
    }

    private fun shouldSyncRemote(
        kitabId: Int,
        page: Int,
        totalPages: Int,
        forceRemoteSync: Boolean
    ): Boolean {
        return ReadingProgressSyncPolicy.shouldSyncRemote(
            lastSnapshot = remoteSyncSnapshots[kitabId],
            page = page,
            totalPages = totalPages,
            force = forceRemoteSync
        )
    }
}
