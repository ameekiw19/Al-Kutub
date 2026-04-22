package com.example.al_kutub.ui.screens

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.Cancel
import androidx.compose.material.icons.filled.Pause
import androidx.compose.material.icons.filled.PlayArrow
import androidx.compose.material.icons.filled.Refresh
import androidx.compose.material.icons.filled.Sync
import androidx.compose.material.icons.filled.Warning
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Button
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.material3.TopAppBar
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import com.example.al_kutub.model.DomainSyncState
import com.example.al_kutub.model.DownloadTaskUiState
import com.example.al_kutub.model.SyncOperationUiState
import com.example.al_kutub.ui.viewmodel.OfflineSyncViewModel
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun OfflineSyncSettingsScreen(
    onBack: () -> Unit,
    viewModel: OfflineSyncViewModel = hiltViewModel()
) {
    val syncSummary by viewModel.syncSummary.collectAsState()
    val uiState by viewModel.uiState.collectAsState()
    val tasks by viewModel.downloadTasks.collectAsState()
    val operations by viewModel.syncOperations.collectAsState()
    var showForceClearDialog by remember { mutableStateOf(false) }

    LaunchedEffect(uiState.requiresForceConfirm) {
        showForceClearDialog = uiState.requiresForceConfirm
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Offline & Sinkronisasi") },
                navigationIcon = {
                    IconButton(onClick = onBack) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Kembali")
                    }
                },
                actions = {
                    IconButton(onClick = { viewModel.syncNow() }) {
                        Icon(Icons.Default.Sync, contentDescription = "Sinkronkan")
                    }
                }
            )
        }
    ) { padding ->
        LazyColumn(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding),
            contentPadding = PaddingValues(16.dp),
            verticalArrangement = Arrangement.spacedBy(12.dp)
        ) {
            item {
                Text(
                    text = "Status Sinkronisasi",
                    style = MaterialTheme.typography.titleMedium,
                    fontWeight = FontWeight.SemiBold
                )
            }
            item { DomainCard(syncSummary.bookmark) }
            item { DomainCard(syncSummary.history) }
            item { DomainCard(syncSummary.pageMarker) }
            item { DomainCard(syncSummary.notes) }

            item {
                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.spacedBy(12.dp)
                ) {
                    Button(
                        onClick = { viewModel.syncNow() },
                        modifier = Modifier.weight(1f)
                    ) {
                        if (uiState.isSyncing) {
                            CircularProgressIndicator(
                                modifier = Modifier.height(16.dp),
                                strokeWidth = 2.dp
                            )
                        } else {
                            Icon(Icons.Default.Refresh, contentDescription = null)
                        }
                        Spacer(modifier = Modifier.height(0.dp))
                        Text("Sinkronkan sekarang")
                    }
                    OutlinedButton(
                        onClick = { viewModel.clearCache() },
                        modifier = Modifier.weight(1f)
                    ) {
                        Text("Hapus cache")
                    }
                }
            }

            if (!uiState.message.isNullOrBlank()) {
                item {
                    Text(
                        text = uiState.message.orEmpty(),
                        style = MaterialTheme.typography.bodyMedium,
                        color = MaterialTheme.colorScheme.primary
                    )
                }
            }

            item {
                Text(
                    text = "Download Manager",
                    style = MaterialTheme.typography.titleMedium,
                    fontWeight = FontWeight.SemiBold
                )
            }

            if (tasks.isEmpty()) {
                item {
                    Text(
                        text = "Belum ada task download.",
                        style = MaterialTheme.typography.bodyMedium,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                }
            } else {
                items(tasks, key = { it.taskId }) { task ->
                    DownloadTaskCard(
                        task = task,
                        onPause = { viewModel.pauseTask(task.taskId) },
                        onResume = { viewModel.resumeTask(task.taskId) },
                        onRetry = { viewModel.retryTask(task.taskId) },
                        onCancel = { viewModel.cancelTask(task.taskId) }
                    )
                }
            }

            item {
                Text(
                    text = "Operasi Sinkronisasi",
                    style = MaterialTheme.typography.titleMedium,
                    fontWeight = FontWeight.SemiBold
                )
            }

            if (operations.isEmpty()) {
                item {
                    Text(
                        text = "Belum ada operasi sinkronisasi.",
                        style = MaterialTheme.typography.bodyMedium,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                }
            } else {
                items(operations, key = { it.id }) { operation ->
                    SyncOperationCard(operation)
                }
            }
        }
    }

    if (showForceClearDialog) {
        AlertDialog(
            onDismissRequest = {
                showForceClearDialog = false
                viewModel.dismissForceConfirm()
            },
            title = { Text("Force Clear Cache?") },
            text = {
                Text("Masih ada data pending sinkron. Jika force clear, data pending akan dihapus.")
            },
            confirmButton = {
                Button(
                    onClick = {
                        showForceClearDialog = false
                        viewModel.clearCache(force = true)
                    }
                ) {
                    Text("Force Clear")
                }
            },
            dismissButton = {
                TextButton(
                    onClick = {
                        showForceClearDialog = false
                        viewModel.dismissForceConfirm()
                    }
                ) {
                    Text("Batal")
                }
            }
        )
    }
}

@Composable
private fun SyncOperationCard(operation: SyncOperationUiState) {
    val statusColor = when (operation.status) {
        "failed" -> MaterialTheme.colorScheme.error
        "processing" -> MaterialTheme.colorScheme.tertiary
        "pending" -> MaterialTheme.colorScheme.secondary
        else -> MaterialTheme.colorScheme.primary
    }
    val nextRetryText = operation.nextRetryAt?.let { "Retry: ${it.toReadableTime()}" }

    Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(14.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surfaceContainerLow)
    ) {
        Column(modifier = Modifier.padding(14.dp), verticalArrangement = Arrangement.spacedBy(6.dp)) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = operation.domain.title,
                    fontWeight = FontWeight.SemiBold
                )
                Text(
                    text = operation.status.uppercase(),
                    color = statusColor,
                    style = MaterialTheme.typography.labelMedium
                )
            }

            Text(
                text = "Operasi: ${operation.operationType}",
                style = MaterialTheme.typography.bodySmall
            )
            Text(
                text = "Retry: ${operation.retryCount}",
                style = MaterialTheme.typography.bodySmall
            )
            nextRetryText?.let {
                Text(
                    text = it,
                    style = MaterialTheme.typography.bodySmall
                )
            }
            if (!operation.lastError.isNullOrBlank()) {
                Row(
                    horizontalArrangement = Arrangement.spacedBy(6.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Icon(
                        imageVector = Icons.Default.Warning,
                        contentDescription = null,
                        tint = MaterialTheme.colorScheme.error
                    )
                    Text(
                        text = operation.lastError.orEmpty(),
                        color = MaterialTheme.colorScheme.error,
                        style = MaterialTheme.typography.bodySmall
                    )
                }
            }
        }
    }
}

private fun Long.toReadableTime(): String {
    val formatter = SimpleDateFormat("dd MMM HH:mm:ss", Locale("id", "ID"))
    return formatter.format(Date(this))
}

@Composable
private fun DomainCard(state: DomainSyncState) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(14.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surfaceContainerLow)
    ) {
        Column(modifier = Modifier.padding(14.dp)) {
            Text(text = state.domain.title, fontWeight = FontWeight.SemiBold)
            Spacer(modifier = Modifier.height(4.dp))
            Text(text = "Pending: ${state.pendingCount}")
            Text(text = "Gagal: ${state.failedCount}")
            state.lastSyncedAt?.let {
                Text(
                    text = "Terakhir sinkron: ${it.toReadableTime()}",
                    style = MaterialTheme.typography.bodySmall
                )
            }
            if (!state.lastError.isNullOrBlank()) {
                Text(
                    text = state.lastError.orEmpty(),
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.error
                )
            }
        }
    }
}

@Composable
private fun DownloadTaskCard(
    task: DownloadTaskUiState,
    onPause: () -> Unit,
    onResume: () -> Unit,
    onRetry: () -> Unit,
    onCancel: () -> Unit
) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(14.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surfaceContainerLow)
    ) {
        Column(modifier = Modifier.padding(14.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
            Text(task.title, fontWeight = FontWeight.SemiBold)
            Text("${task.progressPercent}% • ${task.status}")
            if (!task.errorMessage.isNullOrBlank()) {
                Text(
                    text = task.errorMessage.orEmpty(),
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.error
                )
            }
            Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                IconButton(onClick = onPause) {
                    Icon(Icons.Default.Pause, contentDescription = "Pause")
                }
                IconButton(onClick = onResume) {
                    Icon(Icons.Default.PlayArrow, contentDescription = "Resume")
                }
                IconButton(onClick = onRetry) {
                    Icon(Icons.Default.Refresh, contentDescription = "Retry")
                }
                IconButton(onClick = onCancel) {
                    Icon(Icons.Default.Cancel, contentDescription = "Cancel")
                }
            }
        }
    }
}
