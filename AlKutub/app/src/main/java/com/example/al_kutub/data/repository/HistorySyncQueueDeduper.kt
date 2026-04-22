package com.example.al_kutub.data.repository

import com.example.al_kutub.data.local.entity.SyncOperationEntity
import com.google.gson.Gson
import com.google.gson.reflect.TypeToken

internal object HistorySyncQueueDeduper {
    fun extractKitabId(payloadJson: String, gson: Gson): Int? {
        val type = object : TypeToken<Map<String, Any?>>() {}.type
        val payload: Map<String, Any?> = gson.fromJson(payloadJson, type) ?: return null
        return when (val raw = payload["kitab_id"]) {
            is Number -> raw.toInt()
            is String -> raw.toIntOrNull()
            else -> null
        }?.takeIf { it > 0 }
    }

    fun findMatch(
        operations: List<SyncOperationEntity>,
        kitabId: Int,
        gson: Gson
    ): SyncOperationEntity? {
        return operations.lastOrNull { extractKitabId(it.payloadJson, gson) == kitabId }
    }
}
