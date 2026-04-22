package com.example.al_kutub.ui.screens

import android.app.TimePickerDialog
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.MenuBook
import androidx.compose.material.icons.automirrored.filled.VolumeUp
import androidx.compose.material.icons.filled.CloudOff
import androidx.compose.material.icons.filled.CloudQueue
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.hilt.navigation.compose.hiltViewModel
import com.example.al_kutub.model.NotificationPreferences
import com.example.al_kutub.ui.theme.*
import com.example.al_kutub.ui.viewmodel.NotificationSettingsViewModel
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun NotificationSettingsScreen(
    onBack: () -> Unit,
    viewModel: NotificationSettingsViewModel = hiltViewModel()
) {
    val uiState by viewModel.uiState.collectAsState()
    val preferences by viewModel.preferences.collectAsState()

    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
    ) {
        // Header
        TopAppBar(
            title = { Text("Pengaturan Notifikasi") },
            navigationIcon = {
                IconButton(onClick = onBack) {
                    Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Kembali")
                }
            }
        )

        if (uiState.isLoading) {
            Box(
                modifier = Modifier.fillMaxSize(),
                contentAlignment = Alignment.Center
            ) {
                CircularProgressIndicator()
            }
        } else {
            LazyColumn(
                modifier = Modifier.fillMaxSize(),
                contentPadding = PaddingValues(16.dp),
                verticalArrangement = Arrangement.spacedBy(16.dp)
            ) {
                item {
                    SyncStatusCard(
                        isSaving = uiState.isSaving,
                        isOfflineMode = uiState.isOfflineMode,
                        lastSyncAt = uiState.lastSyncAt,
                        syncError = uiState.syncError,
                        onRetry = { viewModel.retrySync() },
                        onDismissError = { viewModel.clearSyncError() }
                    )
                }

                // Main Toggle
                item {
                    SettingsCard {
                        Row(
                            modifier = Modifier.fillMaxWidth(),
                            horizontalArrangement = Arrangement.SpaceBetween,
                            verticalAlignment = Alignment.CenterVertically
                        ) {
                            Column(modifier = Modifier.weight(1f)) {
                                Text(
                                    text = "Notifikasi",
                                    style = MaterialTheme.typography.titleMedium,
                                    fontWeight = FontWeight.Medium
                                )
                                Text(
                                    text = preferences.getNotificationSettingsSummary(),
                                    style = MaterialTheme.typography.bodySmall,
                                    color = MaterialTheme.colorScheme.onSurfaceVariant
                                )
                            }
                            Switch(
                                checked = preferences.enableNotifications,
                                onCheckedChange = { enabled -> 
                                    viewModel.updatePreferences { 
                                        it.copy(enableNotifications = enabled) 
                                    }
                                }
                            )
                        }
                    }
                }
                
                // Notification Types
                item {
                    SettingsSection(title = "Jenis Notifikasi") {
                        NotificationTypeItem(
                            title = "Kitab Baru",
                            description = "Notifikasi ketika admin menambahkan kitab baru",
                            icon = Icons.AutoMirrored.Filled.MenuBook,
                            enabled = preferences.enableNotifications,
                            isChecked = preferences.newBookNotifications,
                            onCheckedChange = { enabled -> 
                                viewModel.updatePreferences { 
                                    it.copy(newBookNotifications = enabled) 
                                }
                            }
                        )
                        
                        NotificationTypeItem(
                            title = "Update Kitab",
                            description = "Notifikasi ketika kitab diperbarui",
                            icon = Icons.Default.Update,
                            enabled = preferences.enableNotifications,
                            isChecked = preferences.updateNotifications,
                            onCheckedChange = { enabled -> 
                                viewModel.updatePreferences { 
                                    it.copy(updateNotifications = enabled) 
                                }
                            }
                        )
                        
                        NotificationTypeItem(
                            title = "Pengingat Baca",
                            description = "Pengingat untuk melanjutkan membaca",
                            icon = Icons.Default.NotificationAdd,
                            enabled = preferences.enableNotifications,
                            isChecked = preferences.reminderNotifications,
                            onCheckedChange = { enabled -> 
                                viewModel.updatePreferences { 
                                    it.copy(reminderNotifications = enabled) 
                                }
                            }
                        )
                    }
                }
                
                // Quiet Hours
                item {
                    SettingsSection(title = "Jam Tenang") {
                        Row(
                            modifier = Modifier.fillMaxWidth(),
                            horizontalArrangement = Arrangement.SpaceBetween,
                            verticalAlignment = Alignment.CenterVertically
                        ) {
                            Column(modifier = Modifier.weight(1f)) {
                                Text(
                                    text = "Aktifkan Jam Tenang",
                                    style = MaterialTheme.typography.titleMedium,
                                    fontWeight = FontWeight.Medium
                                )
                                Text(
                                    text = "Tidak ada notifikasi dari ${preferences.quietHoursStart} hingga ${preferences.quietHoursEnd}",
                                    style = MaterialTheme.typography.bodySmall,
                                    color = MaterialTheme.colorScheme.onSurfaceVariant
                                )
                            }
                            Switch(
                                checked = preferences.quietHoursEnabled,
                                onCheckedChange = { enabled -> 
                                    viewModel.updatePreferences { 
                                        it.copy(quietHoursEnabled = enabled) 
                                    }
                                }
                            )
                        }
                        
                        if (preferences.quietHoursEnabled) {
                            Spacer(modifier = Modifier.height(16.dp))
                            
                            Row(
                                modifier = Modifier.fillMaxWidth(),
                                horizontalArrangement = Arrangement.SpaceEvenly
                            ) {
                                TimeSelector(
                                    label = "Mulai",
                                    time = preferences.quietHoursStart,
                                    onTimeChange = { time -> 
                                        viewModel.updatePreferences { 
                                            it.copy(quietHoursStart = time) 
                                        }
                                    }
                                )
                                
                                TimeSelector(
                                    label = "Selesai",
                                    time = preferences.quietHoursEnd,
                                    onTimeChange = { time -> 
                                        viewModel.updatePreferences { 
                                            it.copy(quietHoursEnd = time) 
                                        }
                                    }
                                )
                            }
                        }
                    }
                }
                
                // Notification Style
                item {
                    SettingsSection(title = "Gaya Notifikasi") {
                        Column(verticalArrangement = Arrangement.spacedBy(12.dp)) {
                            Text(
                                text = "Suara & Getar",
                                style = MaterialTheme.typography.titleMedium,
                                fontWeight = FontWeight.Medium
                            )
                            
                            NotificationStyleItem(
                                title = "Suara",
                                icon = Icons.AutoMirrored.Filled.VolumeUp,
                                isChecked = preferences.soundEnabled,
                                onCheckedChange = { enabled -> 
                                    viewModel.updatePreferences { 
                                        it.copy(soundEnabled = enabled) 
                                    }
                                }
                            )
                            
                            NotificationStyleItem(
                                title = "Getaran",
                                icon = Icons.Default.Vibration,
                                isChecked = preferences.vibrationEnabled,
                                onCheckedChange = { enabled -> 
                                    viewModel.updatePreferences { 
                                        it.copy(vibrationEnabled = enabled) 
                                    }
                                }
                            )
                            
                            NotificationStyleItem(
                                title = "LED Notifikasi",
                                icon = Icons.Default.Lightbulb,
                                isChecked = preferences.ledEnabled,
                                onCheckedChange = { enabled -> 
                                    viewModel.updatePreferences { 
                                        it.copy(ledEnabled = enabled) 
                                    }
                                }
                            )
                        }
                    }
                }
                
                // Categories
                item {
                    SettingsSection(title = "Notifikasi per Kategori") {
                        Text(
                            text = "Pilih kategori kitab yang ingin kamu terima notifikasinya",
                            style = MaterialTheme.typography.bodySmall,
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                            modifier = Modifier.padding(bottom = 16.dp)
                        )
                        
                        preferences.categories.forEach { (category, enabled) ->
                            NotificationStyleItem(
                                title = category.replaceFirstChar { it.uppercase() },
                                icon = getCategoryIcon(category),
                                isChecked = enabled,
                                onCheckedChange = { 
                                    val newCategories = preferences.categories.toMutableMap()
                                    newCategories[category] = it
                                    viewModel.updatePreferences { 
                                        it.copy(categories = newCategories) 
                                    }
                                }
                            )
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun SyncStatusCard(
    isSaving: Boolean,
    isOfflineMode: Boolean,
    lastSyncAt: Long?,
    syncError: String?,
    onRetry: () -> Unit,
    onDismissError: () -> Unit
) {
    val statusColor = when {
        isSaving -> MaterialTheme.colorScheme.primary
        isOfflineMode -> MaterialTheme.colorScheme.error
        else -> MaterialTheme.colorScheme.tertiary
    }

    SettingsCard {
        Row(
            modifier = Modifier.fillMaxWidth(),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(12.dp)
        ) {
            when {
                isSaving -> {
                    CircularProgressIndicator(
                        modifier = Modifier.size(20.dp),
                        strokeWidth = 2.dp
                    )
                }
                isOfflineMode -> {
                    Icon(
                        imageVector = Icons.Default.CloudOff,
                        contentDescription = null,
                        tint = statusColor
                    )
                }
                else -> {
                    Icon(
                        imageVector = Icons.Default.CloudQueue,
                        contentDescription = null,
                        tint = statusColor
                    )
                }
            }

            Column(modifier = Modifier.weight(1f)) {
                Text(
                    text = when {
                        isSaving -> "Menyimpan perubahan..."
                        isOfflineMode -> "Offline mode aktif"
                        else -> "Tersinkron dengan server"
                    },
                    style = MaterialTheme.typography.bodyMedium,
                    fontWeight = FontWeight.SemiBold,
                    color = statusColor
                )

                if (!isSaving && lastSyncAt != null && !isOfflineMode) {
                    Text(
                        text = "Sinkron terakhir: ${formatSyncTime(lastSyncAt)}",
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                }

                if (!syncError.isNullOrBlank()) {
                    Text(
                        text = syncError,
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.error
                    )
                }
            }

            if (isOfflineMode || !syncError.isNullOrBlank()) {
                TextButton(onClick = onRetry) {
                    Text("Coba lagi")
                }
            }

            if (!syncError.isNullOrBlank()) {
                IconButton(onClick = onDismissError) {
                    Icon(Icons.Default.Close, contentDescription = "Tutup pesan")
                }
            }
        }
    }
}

private fun formatSyncTime(timestamp: Long): String {
    return SimpleDateFormat("HH:mm:ss", Locale.getDefault()).format(Date(timestamp))
}

@Composable
private fun SettingsCard(
    content: @Composable ColumnScope.() -> Unit
) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        colors = CardDefaults.cardColors(
            containerColor = MaterialTheme.colorScheme.surface
        ),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(
            modifier = Modifier.padding(16.dp),
            content = content
        )
    }
}

@Composable
private fun SettingsSection(
    title: String,
    content: @Composable ColumnScope.() -> Unit
) {
    Column {
        Text(
            text = title,
            style = MaterialTheme.typography.titleSmall,
            fontWeight = FontWeight.Medium,
            color = MaterialTheme.colorScheme.primary,
            modifier = Modifier.padding(bottom = 12.dp)
        )
        
        SettingsCard(content = content)
    }
}

@Composable
private fun NotificationTypeItem(
    title: String,
    description: String,
    icon: ImageVector,
    enabled: Boolean,
    isChecked: Boolean,
    onCheckedChange: (Boolean) -> Unit
) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .padding(vertical = 8.dp),
        horizontalArrangement = Arrangement.SpaceBetween,
        verticalAlignment = Alignment.CenterVertically
    ) {
        Row(
            modifier = Modifier.weight(1f),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Icon(
                imageVector = icon,
                contentDescription = null,
                tint = if (enabled) MaterialTheme.colorScheme.primary else MaterialTheme.colorScheme.onSurfaceVariant,
                modifier = Modifier.size(24.dp)
            )
            
            Spacer(modifier = Modifier.width(12.dp))
            
            Column {
                Text(
                    text = title,
                    style = MaterialTheme.typography.bodyLarge,
                    color = if (enabled) MaterialTheme.colorScheme.onSurface else MaterialTheme.colorScheme.onSurfaceVariant
                )
                Text(
                    text = description,
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
            }
        }
        
        Switch(
            checked = isChecked && enabled,
            onCheckedChange = onCheckedChange,
            enabled = enabled
        )
    }
}

@Composable
private fun NotificationStyleItem(
    title: String,
    icon: ImageVector,
    isChecked: Boolean,
    onCheckedChange: (Boolean) -> Unit
) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .padding(vertical = 4.dp),
        horizontalArrangement = Arrangement.SpaceBetween,
        verticalAlignment = Alignment.CenterVertically
    ) {
        Row(
            modifier = Modifier.weight(1f),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Icon(
                imageVector = icon,
                contentDescription = null,
                tint = MaterialTheme.colorScheme.primary,
                modifier = Modifier.size(20.dp)
            )
            
            Spacer(modifier = Modifier.width(12.dp))
            
            Text(
                text = title,
                style = MaterialTheme.typography.bodyLarge
            )
        }
        
        Switch(
            checked = isChecked,
            onCheckedChange = onCheckedChange
        )
    }
}

@Composable
private fun TimeSelector(
    label: String,
    time: String,
    onTimeChange: (String) -> Unit
) {
    val context = LocalContext.current

    Column(
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Text(
            text = label,
            style = MaterialTheme.typography.bodySmall,
            color = MaterialTheme.colorScheme.onSurfaceVariant
        )
        
        Spacer(modifier = Modifier.height(4.dp))
        
        OutlinedButton(
            onClick = {
                val (hour, minute) = parseTime(time)
                TimePickerDialog(
                    context,
                    { _, selectedHour, selectedMinute ->
                        onTimeChange(String.format("%02d:%02d", selectedHour, selectedMinute))
                    },
                    hour,
                    minute,
                    true
                ).show()
            },
            modifier = Modifier.width(100.dp)
        ) {
            Text(time)
        }
    }
}

private fun parseTime(value: String): Pair<Int, Int> {
    return try {
        val parts = value.split(":")
        Pair(parts[0].toInt(), parts[1].toInt())
    } catch (_: Exception) {
        Pair(22, 0)
    }
}

private fun getCategoryIcon(category: String): ImageVector {
    return when (category.lowercase()) {
        "islamic" -> Icons.AutoMirrored.Filled.MenuBook
        "education" -> Icons.Default.School
        "literature" -> Icons.Default.AutoStories
        "history" -> Icons.Default.History
        "science" -> Icons.Default.Science
        else -> Icons.Default.Book
    }
}
