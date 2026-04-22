package com.example.al_kutub.ui.components

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Sync
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.example.al_kutub.model.DomainSyncState

@Composable
fun SyncStatusChip(state: DomainSyncState, modifier: Modifier = Modifier) {
    val label = when {
        state.failedCount > 0 -> "Gagal ${state.failedCount}"
        state.pendingCount > 0 -> "Pending ${state.pendingCount}"
        else -> "Tersinkron"
    }
    val backgroundColor = when {
        state.failedCount > 0 -> MaterialTheme.colorScheme.errorContainer
        state.pendingCount > 0 -> MaterialTheme.colorScheme.secondaryContainer
        else -> MaterialTheme.colorScheme.primaryContainer
    }
    val contentColor = when {
        state.failedCount > 0 -> MaterialTheme.colorScheme.onErrorContainer
        state.pendingCount > 0 -> MaterialTheme.colorScheme.onSecondaryContainer
        else -> MaterialTheme.colorScheme.onPrimaryContainer
    }

    Row(
        modifier = modifier
            .background(backgroundColor, RoundedCornerShape(999.dp))
            .padding(horizontal = 10.dp, vertical = 6.dp),
        horizontalArrangement = Arrangement.spacedBy(6.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Icon(
            imageVector = Icons.Default.Sync,
            contentDescription = null,
            tint = contentColor
        )
        Text(
            text = label,
            color = contentColor,
            style = MaterialTheme.typography.labelMedium
        )
    }
}

