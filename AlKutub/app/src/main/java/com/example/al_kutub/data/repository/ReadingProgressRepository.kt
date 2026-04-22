package com.example.al_kutub.data.repository

import com.example.al_kutub.data.dao.ReadingProgressDao
import com.example.al_kutub.model.ReadingProgress
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.flow.map
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class ReadingProgressRepository @Inject constructor(
    private val readingProgressDao: ReadingProgressDao
) {
    
    suspend fun getProgress(userId: Int, kitabId: Int): ReadingProgress? {
        return readingProgressDao.getProgress(userId, kitabId)
    }
    
    fun getAllProgress(userId: Int): Flow<List<ReadingProgress>> {
        return readingProgressDao.getAllProgress(userId)
    }
    
    fun getCurrentlyReading(userId: Int): Flow<List<ReadingProgress>> {
        return readingProgressDao.getAllProgress(userId)
            .map { progress -> progress.filter { !it.isCompleted } }
    }
    
    fun getCompletedBooks(userId: Int): Flow<List<ReadingProgress>> {
        return readingProgressDao.getAllProgress(userId)
            .map { progress -> progress.filter { it.isCompleted } }
    }
    
    suspend fun getCurrentlyReadingCount(userId: Int): Int {
        val allProgress = readingProgressDao.getAllProgress(userId).first()
        return allProgress.count { !it.isCompleted }
    }
    
    suspend fun getCompletedBooksCount(userId: Int): Int {
        val allProgress = readingProgressDao.getAllProgress(userId).first()
        return allProgress.count { it.isCompleted }
    }
    
    suspend fun getTotalReadingTime(userId: Int): Int {
        val allProgress = readingProgressDao.getAllProgress(userId).first()
        return allProgress.sumOf { it.readingTimeMinutes }
    }
    
    suspend fun saveProgress(progress: ReadingProgress) {
        readingProgressDao.insertProgress(progress)
    }
    
    suspend fun updateProgress(progress: ReadingProgress) {
        readingProgressDao.updateProgress(progress)
    }
    
    suspend fun updateReadingProgress(
        userId: Int,
        kitabId: Int,
        currentPage: Int,
        totalPages: Int,
        position: Long = 0L
    ) {
        val existingProgress = getProgress(userId, kitabId)
        
        if (existingProgress != null) {
            existingProgress.updateProgress(currentPage, totalPages)
            existingProgress.lastReadPosition = position
            updateProgress(existingProgress)
        } else {
            val newProgress = ReadingProgress(
                userId = userId,
                kitabId = kitabId,
                lastPageRead = currentPage,
                totalPages = totalPages,
                lastReadPosition = position
            )
            newProgress.updateProgress(currentPage, totalPages)
            saveProgress(newProgress)
        }
    }
    
    suspend fun addReadingTime(userId: Int, kitabId: Int, minutes: Int) {
        val progress = getProgress(userId, kitabId)
        progress?.let {
            it.readingTimeMinutes += minutes
            updateProgress(it)
        }
    }
    
    suspend fun deleteProgress(userId: Int, kitabId: Int) {
        readingProgressDao.deleteProgress(userId, kitabId)
    }

    suspend fun clearUserProgress(userId: Int) {
        readingProgressDao.deleteAllForUser(userId)
    }
    
    fun getTopProgressBooks(userId: Int, limit: Int = 5): Flow<List<ReadingProgress>> {
        return readingProgressDao.getAllProgress(userId)
            .map { progress -> progress.sortedByDescending { it.progressPercentage }.take(limit) }
    }
    
    suspend fun markAsCompleted(userId: Int, kitabId: Int) {
        val progress = getProgress(userId, kitabId)
        progress?.let {
            it.isCompleted = true
            it.completedAt = System.currentTimeMillis()
            it.progressPercentage = 100f
            updateProgress(it)
        }
    }
}
