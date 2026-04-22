package com.example.al_kutub.data.repository

import com.example.al_kutub.data.local.entity.SyncOperationEntity
import com.google.gson.Gson
import org.junit.Assert.assertEquals
import org.junit.Assert.assertNotNull
import org.junit.Assert.assertNull
import org.junit.Test

class HistorySyncQueueDeduperTest {
    private val gson = Gson()

    @Test
    fun extractKitabId_returnsValueFromPayload() {
        val payload = mapOf("kitab_id" to 42, "current_page" to 12)
        val result = HistorySyncQueueDeduper.extractKitabId(gson.toJson(payload), gson)
        assertEquals(42, result)
    }

    @Test
    fun findMatch_returnsLatestMatchingOperation() {
        val oldOp = SyncOperationEntity(
            id = 1,
            userId = 7,
            domain = "history",
            operationType = OfflineSyncRepository.OP_HISTORY_UPSERT,
            payloadJson = gson.toJson(mapOf("kitab_id" to 100, "current_page" to 3))
        )
        val latestOp = SyncOperationEntity(
            id = 2,
            userId = 7,
            domain = "history",
            operationType = OfflineSyncRepository.OP_HISTORY_UPSERT,
            payloadJson = gson.toJson(mapOf("kitab_id" to 100, "current_page" to 6))
        )
        val otherOp = SyncOperationEntity(
            id = 3,
            userId = 7,
            domain = "history",
            operationType = OfflineSyncRepository.OP_HISTORY_UPSERT,
            payloadJson = gson.toJson(mapOf("kitab_id" to 101, "current_page" to 2))
        )

        val match = HistorySyncQueueDeduper.findMatch(
            operations = listOf(oldOp, latestOp, otherOp),
            kitabId = 100,
            gson = gson
        )

        assertNotNull(match)
        assertEquals(2L, match?.id)
    }

    @Test
    fun findMatch_returnsNullWhenNoMatch() {
        val op = SyncOperationEntity(
            id = 1,
            userId = 7,
            domain = "history",
            operationType = OfflineSyncRepository.OP_HISTORY_UPSERT,
            payloadJson = gson.toJson(mapOf("kitab_id" to 88))
        )
        val match = HistorySyncQueueDeduper.findMatch(
            operations = listOf(op),
            kitabId = 77,
            gson = gson
        )
        assertNull(match)
    }
}
