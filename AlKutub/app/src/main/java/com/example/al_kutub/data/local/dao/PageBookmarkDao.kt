package com.example.al_kutub.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import androidx.room.Update
import com.example.al_kutub.data.local.entity.PageBookmarkEntity
import kotlinx.coroutines.flow.Flow

@Dao
interface PageBookmarkDao {
    @Query(
        """
        SELECT * FROM page_bookmarks
        WHERE userId = :userId
        ORDER BY kitabId ASC, pageNumber ASC
        """
    )
    suspend fun getAllByUser(userId: Int): List<PageBookmarkEntity>

    @Query(
        """
        SELECT * FROM page_bookmarks
        WHERE userId = :userId AND kitabId = :kitabId
        ORDER BY pageNumber ASC
        """
    )
    fun observeByKitab(userId: Int, kitabId: Int): Flow<List<PageBookmarkEntity>>

    @Query(
        """
        SELECT * FROM page_bookmarks
        WHERE userId = :userId AND kitabId = :kitabId AND pageNumber = :pageNumber
        LIMIT 1
        """
    )
    suspend fun getByPage(userId: Int, kitabId: Int, pageNumber: Int): PageBookmarkEntity?

    @Query(
        """
        SELECT * FROM page_bookmarks
        WHERE id = :id AND userId = :userId
        LIMIT 1
        """
    )
    suspend fun getById(id: Long, userId: Int): PageBookmarkEntity?

    @Insert(onConflict = OnConflictStrategy.ABORT)
    suspend fun insert(entity: PageBookmarkEntity)

    @Update
    suspend fun update(entity: PageBookmarkEntity)

    @Query("DELETE FROM page_bookmarks WHERE id = :id AND userId = :userId")
    suspend fun deleteById(id: Long, userId: Int)

    @Query(
        """
        DELETE FROM page_bookmarks
        WHERE userId = :userId AND kitabId = :kitabId AND pageNumber = :pageNumber
        """
    )
    suspend fun deleteByPage(userId: Int, kitabId: Int, pageNumber: Int)

    @Query("DELETE FROM page_bookmarks WHERE userId = :userId AND kitabId = :kitabId")
    suspend fun deleteByKitab(userId: Int, kitabId: Int)
}
