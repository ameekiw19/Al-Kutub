package com.example.al_kutub.data.local.dao

import androidx.room.Dao
import androidx.room.Delete
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import androidx.room.Update
import com.example.al_kutub.data.local.entity.SyncOperationEntity
import kotlinx.coroutines.flow.Flow

@Dao
interface SyncOperationDao {
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insert(operation: SyncOperationEntity): Long

    @Update
    suspend fun update(operation: SyncOperationEntity)

    @Delete
    suspend fun delete(operation: SyncOperationEntity)

    @Query("SELECT * FROM sync_operations WHERE status IN ('pending', 'failed') ORDER BY createdAt ASC")
    suspend fun getProcessableOperations(): List<SyncOperationEntity>

    @Query(
        "SELECT * FROM sync_operations " +
            "WHERE userId = :userId AND domain = :domain AND operationType = :operationType " +
            "AND status IN ('pending', 'failed') ORDER BY createdAt ASC"
    )
    suspend fun getActiveByDomainAndType(
        userId: Int,
        domain: String,
        operationType: String
    ): List<SyncOperationEntity>

    @Query("SELECT * FROM sync_operations WHERE id = :id LIMIT 1")
    suspend fun getById(id: Long): SyncOperationEntity?

    @Query("DELETE FROM sync_operations WHERE status = 'done'")
    suspend fun deleteDoneOperations()

    @Query("DELETE FROM sync_operations")
    suspend fun clearAll()

    @Query("SELECT * FROM sync_operations WHERE userId = :userId ORDER BY createdAt DESC")
    fun observeUserOperations(userId: Int): Flow<List<SyncOperationEntity>>

    @Query("SELECT COUNT(*) FROM sync_operations WHERE userId = :userId AND domain = :domain AND status = 'pending'")
    fun observePendingCount(userId: Int, domain: String): Flow<Int>

    @Query("SELECT COUNT(*) FROM sync_operations WHERE userId = :userId AND domain = :domain AND status = 'failed'")
    fun observeFailedCount(userId: Int, domain: String): Flow<Int>

    @Query("SELECT COUNT(*) FROM sync_operations WHERE userId = :userId AND status IN ('pending','failed')")
    suspend fun countActiveOperations(userId: Int): Int

    @Query("SELECT COUNT(*) FROM sync_operations WHERE userId = :userId AND domain = :domain AND status = 'pending'")
    suspend fun countPendingByDomain(userId: Int, domain: String): Int

    @Query("SELECT COUNT(*) FROM sync_operations WHERE userId = :userId AND domain = :domain AND status = 'failed'")
    suspend fun countFailedByDomain(userId: Int, domain: String): Int

    @Query("SELECT MAX(updatedAt) FROM sync_operations WHERE userId = :userId AND domain = :domain AND status = 'done'")
    suspend fun lastDoneAt(userId: Int, domain: String): Long?

    @Query("SELECT lastError FROM sync_operations WHERE userId = :userId AND domain = :domain AND status = 'failed' ORDER BY updatedAt DESC LIMIT 1")
    suspend fun latestDomainError(userId: Int, domain: String): String?
}
