package com.example.al_kutub.data.local

import androidx.room.migration.Migration
import androidx.sqlite.db.SupportSQLiteDatabase

val MIGRATION_1_2 = object : Migration(1, 2) {
    override fun migrate(database: SupportSQLiteDatabase) {
        try {
            // Create reading_progress table
            database.execSQL(
                """
                CREATE TABLE IF NOT EXISTS reading_progress (
                    id INTEGER PRIMARY KEY NOT NULL,
                    user_id INTEGER NOT NULL,
                    kitab_id INTEGER NOT NULL,
                    last_page_read INTEGER NOT NULL DEFAULT 0,
                    total_pages INTEGER NOT NULL DEFAULT 0,
                    progress_percentage REAL NOT NULL DEFAULT 0.0,
                    last_read_position INTEGER NOT NULL DEFAULT 0,
                    reading_time_minutes INTEGER NOT NULL DEFAULT 0,
                    last_read_at INTEGER NOT NULL,
                    is_completed INTEGER NOT NULL DEFAULT 0,
                    completed_at INTEGER,
                    bookmark_notes TEXT,
                    UNIQUE(user_id, kitab_id)
                )
                """.trimIndent()
            )
            
            // Create indexes for better performance
            database.execSQL("CREATE INDEX IF NOT EXISTS index_reading_progress_user_id ON reading_progress (user_id)")
            database.execSQL("CREATE INDEX IF NOT EXISTS index_reading_progress_kitab_id ON reading_progress (kitab_id)")
            database.execSQL("CREATE INDEX IF NOT EXISTS index_reading_progress_last_read_at ON reading_progress (last_read_at)")
            
        } catch (e: Exception) {
            // If migration fails, drop and recreate table
            database.execSQL("DROP TABLE IF EXISTS reading_progress")
            
            // Recreate table
            database.execSQL(
                """
                CREATE TABLE reading_progress (
                    id INTEGER PRIMARY KEY NOT NULL,
                    user_id INTEGER NOT NULL,
                    kitab_id INTEGER NOT NULL,
                    last_page_read INTEGER NOT NULL DEFAULT 0,
                    total_pages INTEGER NOT NULL DEFAULT 0,
                    progress_percentage REAL NOT NULL DEFAULT 0.0,
                    last_read_position INTEGER NOT NULL DEFAULT 0,
                    reading_time_minutes INTEGER NOT NULL DEFAULT 0,
                    last_read_at INTEGER NOT NULL,
                    is_completed INTEGER NOT NULL DEFAULT 0,
                    completed_at INTEGER,
                    bookmark_notes TEXT,
                    UNIQUE(user_id, kitab_id)
                )
                """.trimIndent()
            )
            
            // Create indexes
            database.execSQL("CREATE INDEX index_reading_progress_user_id ON reading_progress (user_id)")
            database.execSQL("CREATE INDEX index_reading_progress_kitab_id ON reading_progress (kitab_id)")
            database.execSQL("CREATE INDEX index_reading_progress_last_read_at ON reading_progress (last_read_at)")
        }
    }
}

val MIGRATION_8_9 = object : Migration(8, 9) {
    override fun migrate(database: SupportSQLiteDatabase) {
        database.execSQL(
            """
            CREATE TABLE IF NOT EXISTS sync_operations (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                userId INTEGER NOT NULL,
                domain TEXT NOT NULL,
                operationType TEXT NOT NULL,
                payloadJson TEXT NOT NULL,
                status TEXT NOT NULL,
                retryCount INTEGER NOT NULL DEFAULT 0,
                lastError TEXT,
                clientRequestId TEXT,
                createdAt INTEGER NOT NULL,
                updatedAt INTEGER NOT NULL,
                lastAttemptAt INTEGER
            )
            """.trimIndent()
        )
        database.execSQL("CREATE INDEX IF NOT EXISTS index_sync_operations_status_createdAt ON sync_operations (status, createdAt)")
        database.execSQL("CREATE INDEX IF NOT EXISTS index_sync_operations_userId_domain ON sync_operations (userId, domain)")

        database.execSQL(
            """
            CREATE TABLE IF NOT EXISTS cached_bookmarks (
                userId INTEGER NOT NULL,
                kitabId INTEGER NOT NULL,
                bookmarkId INTEGER,
                createdAt TEXT NOT NULL,
                judul TEXT NOT NULL,
                penulis TEXT NOT NULL,
                cover TEXT,
                kategori TEXT,
                updatedAt INTEGER NOT NULL,
                PRIMARY KEY(userId, kitabId)
            )
            """.trimIndent()
        )
        database.execSQL("CREATE INDEX IF NOT EXISTS index_cached_bookmarks_userId_updatedAt ON cached_bookmarks (userId, updatedAt)")

        database.execSQL(
            """
            CREATE TABLE IF NOT EXISTS cached_histories (
                userId INTEGER NOT NULL,
                historyId INTEGER NOT NULL,
                kitabId INTEGER NOT NULL,
                judul TEXT NOT NULL,
                penulis TEXT NOT NULL,
                cover TEXT,
                kategori TEXT,
                timeAgo TEXT NOT NULL,
                lastReadAt TEXT NOT NULL,
                currentPage INTEGER NOT NULL,
                totalPages INTEGER NOT NULL,
                readingTimeMinutes INTEGER NOT NULL,
                updatedAt INTEGER NOT NULL,
                PRIMARY KEY(userId, historyId)
            )
            """.trimIndent()
        )
        database.execSQL("CREATE INDEX IF NOT EXISTS index_cached_histories_userId_updatedAt ON cached_histories (userId, updatedAt)")

        database.execSQL(
            """
            CREATE TABLE IF NOT EXISTS cached_reading_notes (
                userId INTEGER NOT NULL,
                noteId INTEGER NOT NULL,
                kitabId INTEGER NOT NULL,
                kitabTitle TEXT,
                noteContent TEXT NOT NULL,
                pageNumber INTEGER,
                highlightedText TEXT,
                noteColor TEXT NOT NULL,
                isPrivate INTEGER NOT NULL,
                createdAt TEXT NOT NULL,
                remoteUpdatedAt TEXT NOT NULL,
                clientRequestId TEXT,
                updatedAt INTEGER NOT NULL,
                PRIMARY KEY(userId, noteId)
            )
            """.trimIndent()
        )
        database.execSQL("CREATE INDEX IF NOT EXISTS index_cached_reading_notes_userId_updatedAt ON cached_reading_notes (userId, updatedAt)")

        database.execSQL(
            """
            CREATE TABLE IF NOT EXISTS download_tasks (
                taskId INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                userId INTEGER NOT NULL,
                kitabId INTEGER NOT NULL,
                title TEXT NOT NULL,
                fileName TEXT NOT NULL,
                targetPath TEXT NOT NULL,
                tempPath TEXT NOT NULL,
                downloadedBytes INTEGER NOT NULL DEFAULT 0,
                totalBytes INTEGER NOT NULL DEFAULT 0,
                status TEXT NOT NULL,
                progressPercent INTEGER NOT NULL DEFAULT 0,
                errorMessage TEXT,
                etag TEXT,
                lastModified TEXT,
                createdAt INTEGER NOT NULL,
                updatedAt INTEGER NOT NULL
            )
            """.trimIndent()
        )
        database.execSQL("CREATE UNIQUE INDEX IF NOT EXISTS index_download_tasks_userId_kitabId ON download_tasks (userId, kitabId)")
        database.execSQL("CREATE INDEX IF NOT EXISTS index_download_tasks_status_updatedAt ON download_tasks (status, updatedAt)")
    }
}

val MIGRATION_9_10 = object : Migration(9, 10) {
    override fun migrate(database: SupportSQLiteDatabase) {
        database.execSQL(
            """
            CREATE TABLE IF NOT EXISTS page_bookmarks (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                userId INTEGER NOT NULL,
                kitabId INTEGER NOT NULL,
                pageNumber INTEGER NOT NULL,
                label TEXT NOT NULL,
                createdAt INTEGER NOT NULL,
                updatedAt INTEGER NOT NULL,
                isPendingSync INTEGER NOT NULL DEFAULT 1,
                lastSyncedAt INTEGER
            )
            """.trimIndent()
        )
        database.execSQL(
            """
            CREATE UNIQUE INDEX IF NOT EXISTS index_page_bookmarks_userId_kitabId_pageNumber
            ON page_bookmarks (userId, kitabId, pageNumber)
            """.trimIndent()
        )
        database.execSQL(
            """
            CREATE INDEX IF NOT EXISTS index_page_bookmarks_userId_kitabId
            ON page_bookmarks (userId, kitabId)
            """.trimIndent()
        )
    }
}
