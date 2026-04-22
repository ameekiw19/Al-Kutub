package com.example.al_kutub.ui.screens

import android.widget.Toast
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.example.al_kutub.ui.theme.SharedColors
import com.example.al_kutub.ui.viewmodel.Settings2FAViewModel

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun Settings2FASection(
    refreshKey: Int = 0,
    onNavigateToSetup: () -> Unit,
    onNavigateToManage: () -> Unit,
    viewModel: Settings2FAViewModel = hiltViewModel()
) {
    val uiState by viewModel.uiState.collectAsStateWithLifecycle()
    val context = LocalContext.current

    LaunchedEffect(refreshKey) {
        viewModel.load2FAStatus(showLoading = refreshKey == 0)
    }

    LaunchedEffect(Unit) {
        viewModel.events.collect { event ->
            when (event) {
                is Settings2FAViewModel.Settings2FAEvent.DisableSuccess -> {
                    Toast.makeText(context, "2FA berhasil dinonaktifkan", Toast.LENGTH_SHORT).show()
                }
                is Settings2FAViewModel.Settings2FAEvent.Error -> {
                    Toast.makeText(context, event.message, Toast.LENGTH_LONG).show()
                }
            }
        }
    }

    Card(
        modifier = Modifier
            .fillMaxWidth()
            .padding(horizontal = 16.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        shape = RoundedCornerShape(16.dp),
        elevation = CardDefaults.cardElevation(4.dp)
    ) {
        Column(modifier = Modifier.padding(20.dp)) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                verticalAlignment = Alignment.CenterVertically
            ) {
                Box(
                    modifier = Modifier
                        .size(48.dp)
                        .background(
                            Brush.verticalGradient(
                                colors = listOf(SharedColors.TealMain, Color(0xFF1B5E20))
                            ),
                            RoundedCornerShape(12.dp)
                        ),
                    contentAlignment = Alignment.Center
                ) {
                    Icon(
                        Icons.Default.Security,
                        contentDescription = "Security",
                        tint = Color.White,
                        modifier = Modifier.size(24.dp)
                    )
                }

                Spacer(modifier = Modifier.width(16.dp))

                Column(modifier = Modifier.weight(1f)) {
                    Text(
                        text = "Two-Factor Authentication",
                        color = Color.Black,
                        fontSize = 18.sp,
                        fontWeight = FontWeight.Bold
                    )
                    Text(
                        text = "Tambahkan lapisan keamanan ekstra",
                        color = Color.Gray,
                        fontSize = 14.sp
                    )
                }

                when {
                    uiState.isLoading -> {
                        CircularProgressIndicator(
                            modifier = Modifier.size(24.dp),
                            color = SharedColors.TealMain,
                            strokeWidth = 2.dp
                        )
                    }
                    uiState.status == Settings2FAViewModel.TwoFAStatus.ENABLED -> {
                        Icon(
                            Icons.Default.CheckCircle,
                            contentDescription = "Enabled",
                            tint = Color(0xFF4CAF50),
                            modifier = Modifier.size(24.dp)
                        )
                    }
                    else -> {
                        Icon(
                            Icons.Default.CheckCircleOutline,
                            contentDescription = "Disabled",
                            tint = Color.Gray,
                            modifier = Modifier.size(24.dp)
                        )
                    }
                }
            }

            Spacer(modifier = Modifier.height(20.dp))

            if (!uiState.errorMessage.isNullOrBlank()) {
                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .background(
                            Color(0xFFF44336).copy(alpha = 0.1f),
                            RoundedCornerShape(8.dp)
                        )
                        .padding(12.dp),
                    horizontalArrangement = Arrangement.SpaceBetween,
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Text(
                        text = uiState.errorMessage ?: "",
                        color = Color(0xFFD32F2F),
                        fontSize = 13.sp,
                        modifier = Modifier.weight(1f)
                    )
                    TextButton(
                        onClick = {
                            viewModel.clearError()
                            viewModel.load2FAStatus()
                        }
                    ) {
                        Text("Coba lagi")
                    }
                }

                Spacer(modifier = Modifier.height(12.dp))
            }

            when {
                uiState.isLoading && uiState.status == Settings2FAViewModel.TwoFAStatus.UNKNOWN -> {
                    Box(
                        modifier = Modifier
                            .fillMaxWidth()
                            .height(120.dp),
                        contentAlignment = Alignment.Center
                    ) {
                        Column(horizontalAlignment = Alignment.CenterHorizontally) {
                            CircularProgressIndicator(color = SharedColors.TealMain)
                            Spacer(modifier = Modifier.height(8.dp))
                            Text(
                                text = "Memeriksa status 2FA...",
                                color = Color.Gray,
                                fontSize = 14.sp
                            )
                        }
                    }
                }
                uiState.status == Settings2FAViewModel.TwoFAStatus.ENABLED -> {
                    Enabled2FAStatus(
                        enabledAt = uiState.enabledAt,
                        lastUsedAt = uiState.lastUsedAt,
                        backupCodesCount = uiState.backupCodesCount,
                        onManage = onNavigateToManage,
                        onDisable = { password, code ->
                            viewModel.disable2FA(password, code)
                        }
                    )
                }
                else -> {
                    Disabled2FAStatus(onSetup = onNavigateToSetup)
                }
            }
        }
    }
}

@Composable
private fun Enabled2FAStatus(
    enabledAt: String?,
    lastUsedAt: String?,
    backupCodesCount: Int,
    onManage: () -> Unit,
    onDisable: (String, String) -> Unit
) {
    var showDisableDialog by remember { mutableStateOf(false) }

    Column {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .background(
                    Color(0xFF4CAF50).copy(alpha = 0.1f),
                    RoundedCornerShape(8.dp)
                )
                .padding(12.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Icon(
                Icons.Default.Shield,
                contentDescription = "Protected",
                tint = Color(0xFF4CAF50),
                modifier = Modifier.size(20.dp)
            )
            Spacer(modifier = Modifier.width(8.dp))
            Text(
                text = "2FA Aktif - Akun Anda Terlindungi",
                color = Color(0xFF4CAF50),
                fontSize = 14.sp,
                fontWeight = FontWeight.Medium
            )
        }

        Spacer(modifier = Modifier.height(16.dp))

        Column(verticalArrangement = Arrangement.spacedBy(8.dp)) {
            if (enabledAt != null) {
                InfoRow(
                    icon = Icons.Default.Schedule,
                    label = "Diaktifkan",
                    value = formatDate(enabledAt)
                )
            }

            if (lastUsedAt != null) {
                InfoRow(
                    icon = Icons.Default.AccessTime,
                    label = "Terakhir digunakan",
                    value = formatDate(lastUsedAt)
                )
            }

            InfoRow(
                icon = Icons.Default.Key,
                label = "Backup codes tersedia",
                value = "$backupCodesCount kode"
            )
        }

        Spacer(modifier = Modifier.height(20.dp))

        Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.spacedBy(12.dp)
        ) {
            OutlinedButton(
                onClick = onManage,
                modifier = Modifier.weight(1f),
                border = androidx.compose.foundation.BorderStroke(1.dp, SharedColors.TealMain),
                colors = ButtonDefaults.outlinedButtonColors(contentColor = SharedColors.TealMain)
            ) {
                Text(
                    text = "Kelola",
                    fontSize = 14.sp,
                    fontWeight = FontWeight.Medium
                )
            }

            Button(
                onClick = { showDisableDialog = true },
                modifier = Modifier.weight(1f),
                colors = ButtonDefaults.buttonColors(
                    containerColor = Color(0xFFF44336),
                    contentColor = Color.White
                )
            ) {
                Text(
                    text = "Nonaktifkan",
                    fontSize = 14.sp,
                    fontWeight = FontWeight.Medium
                )
            }
        }
    }

    if (showDisableDialog) {
        Disable2FADialog(
            onDismiss = { showDisableDialog = false },
            onConfirm = { password, code ->
                onDisable(password, code)
                showDisableDialog = false
            }
        )
    }
}

@Composable
private fun Disabled2FAStatus(onSetup: () -> Unit) {
    Column {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .background(
                    Color(0xFFFF9800).copy(alpha = 0.1f),
                    RoundedCornerShape(8.dp)
                )
                .padding(12.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Icon(
                Icons.Default.Warning,
                contentDescription = "Warning",
                tint = Color(0xFFFF9800),
                modifier = Modifier.size(20.dp)
            )
            Spacer(modifier = Modifier.width(8.dp))
            Text(
                text = "2FA Tidak Aktif - Akun Rentan",
                color = Color(0xFFFF9800),
                fontSize = 14.sp,
                fontWeight = FontWeight.Medium
            )
        }

        Spacer(modifier = Modifier.height(16.dp))

        Text(
            text = "Aktifkan Two-Factor Authentication untuk melindungi akun Anda dari akses tidak sah.",
            color = Color.Gray,
            fontSize = 14.sp,
            lineHeight = 20.sp
        )

        Spacer(modifier = Modifier.height(20.dp))

        Button(
            onClick = onSetup,
            modifier = Modifier.fillMaxWidth(),
            colors = ButtonDefaults.buttonColors(
                containerColor = SharedColors.TealMain,
                contentColor = Color.White
            )
        ) {
            Icon(
                Icons.Default.Security,
                contentDescription = null,
                modifier = Modifier.size(20.dp)
            )
            Spacer(modifier = Modifier.width(8.dp))
            Text(
                text = "Aktifkan 2FA",
                fontSize = 16.sp,
                fontWeight = FontWeight.Bold
            )
        }
    }
}

@Composable
private fun InfoRow(
    icon: androidx.compose.ui.graphics.vector.ImageVector,
    label: String,
    value: String
) {
    Row(
        modifier = Modifier.fillMaxWidth(),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Icon(
            icon,
            contentDescription = null,
            tint = Color.Gray,
            modifier = Modifier.size(16.dp)
        )
        Spacer(modifier = Modifier.width(12.dp))
        Column(modifier = Modifier.weight(1f)) {
            Text(
                text = label,
                color = Color.Gray,
                fontSize = 12.sp
            )
            Text(
                text = value,
                color = Color.Black,
                fontSize = 14.sp,
                fontWeight = FontWeight.Medium
            )
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
private fun Disable2FADialog(
    onDismiss: () -> Unit,
    onConfirm: (String, String) -> Unit
) {
    var password by remember { mutableStateOf("") }
    var code by remember { mutableStateOf("") }
    var passwordVisible by remember { mutableStateOf(false) }
    var codeVisible by remember { mutableStateOf(false) }

    AlertDialog(
        onDismissRequest = onDismiss,
        title = {
            Text(
                text = "Nonaktifkan 2FA",
                fontWeight = FontWeight.Bold
            )
        },
        text = {
            Column(verticalArrangement = Arrangement.spacedBy(16.dp)) {
                Text(
                    text = "Untuk keamanan, masukkan password dan kode verifikasi 2FA Anda.",
                    fontSize = 14.sp,
                    color = Color.Gray
                )

                OutlinedTextField(
                    value = password,
                    onValueChange = { password = it },
                    label = { Text("Password") },
                    visualTransformation = if (passwordVisible) {
                        androidx.compose.ui.text.input.VisualTransformation.None
                    } else {
                        androidx.compose.ui.text.input.PasswordVisualTransformation()
                    },
                    trailingIcon = {
                        IconButton(onClick = { passwordVisible = !passwordVisible }) {
                            Icon(
                                if (passwordVisible) Icons.Default.Visibility else Icons.Default.VisibilityOff,
                                contentDescription = if (passwordVisible) "Hide" else "Show"
                            )
                        }
                    },
                    modifier = Modifier.fillMaxWidth(),
                    singleLine = true
                )

                OutlinedTextField(
                    value = code,
                    onValueChange = { if (it.length <= 6) code = it.filter { c -> c.isDigit() } },
                    label = { Text("Kode 2FA") },
                    visualTransformation = if (codeVisible) {
                        androidx.compose.ui.text.input.VisualTransformation.None
                    } else {
                        androidx.compose.ui.text.input.PasswordVisualTransformation()
                    },
                    trailingIcon = {
                        IconButton(onClick = { codeVisible = !codeVisible }) {
                            Icon(
                                if (codeVisible) Icons.Default.Visibility else Icons.Default.VisibilityOff,
                                contentDescription = if (codeVisible) "Hide" else "Show"
                            )
                        }
                    },
                    modifier = Modifier.fillMaxWidth(),
                    singleLine = true,
                    keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number)
                )
            }
        },
        confirmButton = {
            Button(
                onClick = {
                    if (password.isNotEmpty() && code.length == 6) {
                        onConfirm(password, code)
                    }
                },
                enabled = password.isNotEmpty() && code.length == 6,
                colors = ButtonDefaults.buttonColors(
                    containerColor = Color(0xFFF44336),
                    contentColor = Color.White
                )
            ) {
                Text("Nonaktifkan")
            }
        },
        dismissButton = {
            TextButton(onClick = onDismiss) {
                Text("Batal")
            }
        }
    )
}

private fun formatDate(dateString: String): String {
    return try {
        val formatter = java.time.format.DateTimeFormatter.ofPattern("dd MMM yyyy, HH:mm")
        val parsed = java.time.LocalDateTime.parse(dateString.replace(" ", "T"))
        parsed.format(formatter)
    } catch (e: Exception) {
        dateString
    }
}
