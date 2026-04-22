package com.example.al_kutub.ui.screens

import android.content.ClipData
import android.content.ClipboardManager
import android.content.Context
import android.os.Bundle
import android.widget.Toast
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.animation.AnimatedVisibility
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.LifecycleEventObserver
import androidx.lifecycle.compose.LocalLifecycleOwner
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import coil.compose.AsyncImage
import com.example.al_kutub.ui.theme.AlKutubTheme
import com.example.al_kutub.ui.viewmodel.TwoFASetupViewModel
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class TwoFASetupActivity : ComponentActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        val manageMode = intent.getBooleanExtra("MANAGE_MODE", false)

        setContent {
            AlKutubTheme {
                TwoFASetupScreen(
                    isManageMode = manageMode,
                    onBack = { finish() },
                    onSuccess = { finish() },
                    onError = { message ->
                        Toast.makeText(this, message, Toast.LENGTH_LONG).show()
                    }
                )
            }
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun TwoFASetupScreen(
    isManageMode: Boolean,
    onBack: () -> Unit,
    onSuccess: () -> Unit,
    onError: (String) -> Unit,
    viewModel: TwoFASetupViewModel = hiltViewModel()
) {
    var manageMode by remember { mutableStateOf(isManageMode) }
    val uiState by viewModel.uiState.collectAsStateWithLifecycle()
    val manageUiState by viewModel.manageUiState.collectAsStateWithLifecycle()
    val lifecycleOwner = LocalLifecycleOwner.current
    val context = LocalContext.current

    LaunchedEffect(uiState) {
        when (val state = uiState) {
            is TwoFASetupViewModel.TwoFASetupUiState.SetupComplete -> onSuccess()
            is TwoFASetupViewModel.TwoFASetupUiState.Error -> onError(state.message)
            else -> Unit
        }
    }

    LaunchedEffect(Unit) {
        viewModel.manageEvents.collect { message ->
            Toast.makeText(context, message, Toast.LENGTH_SHORT).show()
        }
    }

    LaunchedEffect(manageMode) {
        if (manageMode) {
            viewModel.loadManageData()
        }
    }

    DisposableEffect(lifecycleOwner, manageMode) {
        val observer = LifecycleEventObserver { _, event ->
            if (event == Lifecycle.Event.ON_RESUME && manageMode) {
                viewModel.loadManageData()
            }
        }
        lifecycleOwner.lifecycle.addObserver(observer)
        onDispose {
            lifecycleOwner.lifecycle.removeObserver(observer)
        }
    }

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(
                Brush.verticalGradient(
                    colors = listOf(Color(0xFF2E7D32), Color(0xFF1B5E20))
                )
            )
    ) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(16.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            IconButton(onClick = onBack) {
                Icon(
                    Icons.AutoMirrored.Filled.ArrowBack,
                    contentDescription = "Back",
                    tint = Color.White
                )
            }
            Text(
                text = if (manageMode) "Kelola 2FA" else "Setup 2FA",
                color = Color.White,
                fontSize = 20.sp,
                fontWeight = FontWeight.Bold,
                modifier = Modifier.weight(1f)
            )
        }

        if (manageMode) {
            Manage2FAContent(
                state = manageUiState,
                onRetry = { viewModel.loadManageData() },
                onRegenerate = { password -> viewModel.regenerateBackupCodes(password) },
                onSwitchToSetup = {
                    manageMode = false
                    viewModel.resetState()
                }
            )
        } else {
            Setup2FAContent(
                uiState = uiState,
                onSetup = { viewModel.setup2FA() },
                onEnable = { code -> viewModel.enable2FA(code) }
            )
        }
    }
}

@Composable
private fun Setup2FAContent(
    uiState: TwoFASetupViewModel.TwoFASetupUiState,
    onSetup: () -> Unit,
    onEnable: (String) -> Unit
) {
    var verificationCode by remember { mutableStateOf("") }
    var showBackupCodes by remember { mutableStateOf(false) }
    var isSecretVisible by remember { mutableStateOf(false) }
    val context = LocalContext.current

    LazyColumn(
        modifier = Modifier
            .fillMaxSize()
            .padding(24.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.spacedBy(24.dp)
    ) {
        item {
            Box(
                modifier = Modifier
                    .size(80.dp)
                    .background(
                        Color.White.copy(alpha = 0.2f),
                        RoundedCornerShape(40.dp)
                    ),
                contentAlignment = Alignment.Center
            ) {
                Text(text = "🔐", fontSize = 40.sp)
            }
        }

        item {
            Text(
                text = "Two-Factor Authentication",
                color = Color.White,
                fontSize = 24.sp,
                fontWeight = FontWeight.Bold,
                textAlign = TextAlign.Center
            )
        }

        item {
            Text(
                text = "Tambahkan keamanan ekstra ke akun Anda dengan 2FA. Setelah login, Anda perlu memasukkan kode dari aplikasi authenticator.",
                color = Color.White.copy(alpha = 0.8f),
                fontSize = 14.sp,
                textAlign = TextAlign.Center,
                lineHeight = 20.sp
            )
        }

        when (val state = uiState) {
            is TwoFASetupViewModel.TwoFASetupUiState.Idle -> {
                item {
                    Button(
                        onClick = onSetup,
                        modifier = Modifier
                            .fillMaxWidth()
                            .height(56.dp),
                        colors = ButtonDefaults.buttonColors(
                            containerColor = Color.White,
                            contentColor = Color(0xFF2E7D32)
                        ),
                        shape = RoundedCornerShape(12.dp)
                    ) {
                        Text("Mulai Setup", fontSize = 16.sp, fontWeight = FontWeight.Bold)
                    }
                }
            }

            is TwoFASetupViewModel.TwoFASetupUiState.Loading -> {
                item {
                    Box(
                        modifier = Modifier
                            .fillMaxWidth()
                            .height(200.dp),
                        contentAlignment = Alignment.Center
                    ) {
                        CircularProgressIndicator(color = Color.White, strokeWidth = 3.dp)
                    }
                }
            }

            is TwoFASetupViewModel.TwoFASetupUiState.Enabling -> {
                item {
                    Box(
                        modifier = Modifier
                            .fillMaxWidth()
                            .height(200.dp),
                        contentAlignment = Alignment.Center
                    ) {
                        Column(horizontalAlignment = Alignment.CenterHorizontally) {
                            CircularProgressIndicator(color = Color.White, strokeWidth = 3.dp)
                            Spacer(modifier = Modifier.height(12.dp))
                            Text(
                                text = "Mengaktifkan 2FA...",
                                color = Color.White
                            )
                        }
                    }
                }
            }

            is TwoFASetupViewModel.TwoFASetupUiState.SetupReady -> {
                item {
                    Card(
                        modifier = Modifier.fillMaxWidth(),
                        colors = CardDefaults.cardColors(containerColor = Color.White),
                        shape = RoundedCornerShape(16.dp),
                        elevation = CardDefaults.cardElevation(8.dp)
                    ) {
                        Column(
                            modifier = Modifier.padding(24.dp),
                            horizontalAlignment = Alignment.CenterHorizontally
                        ) {
                            Text(
                                text = "Scan QR Code",
                                color = Color(0xFF2E7D32),
                                fontSize = 18.sp,
                                fontWeight = FontWeight.Bold
                            )
                            Spacer(modifier = Modifier.height(16.dp))
                            AsyncImage(
                                model = state.qrCodeUrl,
                                contentDescription = "QR Code",
                                modifier = Modifier
                                    .size(200.dp)
                                    .background(Color.White, RoundedCornerShape(8.dp))
                                    .padding(8.dp)
                            )
                            Spacer(modifier = Modifier.height(16.dp))
                            Text(
                                text = "Gunakan Google Authenticator atau Authy",
                                color = Color.Gray,
                                fontSize = 12.sp
                            )
                        }
                    }
                }

                item {
                    Card(
                        modifier = Modifier.fillMaxWidth(),
                        colors = CardDefaults.cardColors(containerColor = Color.White.copy(alpha = 0.1f)),
                        shape = RoundedCornerShape(16.dp)
                    ) {
                        Column(modifier = Modifier.padding(20.dp)) {
                            Text(
                                text = "Atau Masukkan Secara Manual",
                                color = Color.White,
                                fontSize = 16.sp,
                                fontWeight = FontWeight.Bold
                            )
                            Spacer(modifier = Modifier.height(12.dp))
                            Row(
                                modifier = Modifier.fillMaxWidth(),
                                verticalAlignment = Alignment.CenterVertically
                            ) {
                                Text(
                                    text = if (isSecretVisible) state.secretKey else "•••••••••••••••",
                                    color = Color.White,
                                    fontSize = 14.sp,
                                    fontFamily = androidx.compose.ui.text.font.FontFamily.Monospace,
                                    modifier = Modifier.weight(1f)
                                )

                                IconButton(onClick = { isSecretVisible = !isSecretVisible }) {
                                    Icon(
                                        if (isSecretVisible) Icons.Default.Visibility else Icons.Default.VisibilityOff,
                                        contentDescription = null,
                                        tint = Color.White
                                    )
                                }

                                IconButton(
                                    onClick = {
                                        copyToClipboard(context, state.secretKey)
                                        Toast.makeText(context, "Secret key disalin!", Toast.LENGTH_SHORT).show()
                                    }
                                ) {
                                    Icon(
                                        Icons.Default.ContentCopy,
                                        contentDescription = "Copy",
                                        tint = Color.White
                                    )
                                }
                            }
                        }
                    }
                }

                item {
                    OutlinedTextField(
                        value = verificationCode,
                        onValueChange = {
                            if (it.length <= 6) verificationCode = it.filter { ch -> ch.isDigit() }
                        },
                        label = { Text("Kode Verifikasi", color = Color.White.copy(alpha = 0.7f)) },
                        placeholder = { Text("000000", color = Color.White.copy(alpha = 0.5f)) },
                        modifier = Modifier
                            .fillMaxWidth()
                            .clip(RoundedCornerShape(12.dp)),
                        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number),
                        visualTransformation = PasswordVisualTransformation(),
                        singleLine = true,
                        textStyle = LocalTextStyle.current.copy(
                            color = Color.White,
                            fontSize = 18.sp,
                            textAlign = TextAlign.Center,
                            letterSpacing = 4.sp
                        ),
                        colors = OutlinedTextFieldDefaults.colors(
                            focusedBorderColor = Color.White,
                            unfocusedBorderColor = Color.White.copy(alpha = 0.5f),
                            focusedTextColor = Color.White,
                            unfocusedTextColor = Color.White,
                            cursorColor = Color.White
                        )
                    )
                }

                item {
                    Button(
                        onClick = {
                            if (verificationCode.length == 6) {
                                onEnable(verificationCode)
                            } else {
                                Toast.makeText(context, "Masukkan 6 digit kode", Toast.LENGTH_SHORT).show()
                            }
                        },
                        modifier = Modifier
                            .fillMaxWidth()
                            .height(56.dp),
                        enabled = verificationCode.length == 6,
                        colors = ButtonDefaults.buttonColors(
                            containerColor = Color.White,
                            contentColor = Color(0xFF2E7D32)
                        ),
                        shape = RoundedCornerShape(12.dp)
                    ) {
                        Text("Aktifkan 2FA", fontSize = 16.sp, fontWeight = FontWeight.Bold)
                    }
                }

                item {
                    TextButton(onClick = { showBackupCodes = !showBackupCodes }) {
                        Text(
                            text = if (showBackupCodes) "Sembunyikan Backup Codes" else "Lihat Backup Codes",
                            color = Color.White,
                            fontSize = 14.sp
                        )
                    }
                }

                if (showBackupCodes) {
                    item {
                        BackupCodesSection(backupCodes = state.backupCodes)
                    }
                }
            }

            else -> Unit
        }
    }
}

@Composable
private fun Manage2FAContent(
    state: TwoFASetupViewModel.ManageUiState,
    onRetry: () -> Unit,
    onRegenerate: (String) -> Unit,
    onSwitchToSetup: () -> Unit
) {
    var showPasswordDialog by remember { mutableStateOf(false) }

    LazyColumn(
        modifier = Modifier
            .fillMaxSize()
            .padding(24.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.spacedBy(16.dp)
    ) {
        item {
            Text(
                text = "Kelola Backup Code 2FA",
                color = Color.White,
                fontSize = 22.sp,
                fontWeight = FontWeight.Bold,
                textAlign = TextAlign.Center
            )
        }

        when (state) {
            is TwoFASetupViewModel.ManageUiState.ManageLoading,
            is TwoFASetupViewModel.ManageUiState.Regenerating -> {
                item {
                    Box(
                        modifier = Modifier
                            .fillMaxWidth()
                            .height(220.dp),
                        contentAlignment = Alignment.Center
                    ) {
                        Column(horizontalAlignment = Alignment.CenterHorizontally) {
                            CircularProgressIndicator(color = Color.White)
                            Spacer(modifier = Modifier.height(12.dp))
                            Text(
                                text = if (state is TwoFASetupViewModel.ManageUiState.Regenerating) {
                                    "Memperbarui backup code..."
                                } else {
                                    "Memuat data 2FA..."
                                },
                                color = Color.White
                            )
                        }
                    }
                }
            }

            is TwoFASetupViewModel.ManageUiState.Disabled -> {
                item {
                    Card(
                        modifier = Modifier.fillMaxWidth(),
                        colors = CardDefaults.cardColors(containerColor = Color.White.copy(alpha = 0.12f)),
                        shape = RoundedCornerShape(16.dp)
                    ) {
                        Column(
                            modifier = Modifier.padding(20.dp),
                            verticalArrangement = Arrangement.spacedBy(12.dp)
                        ) {
                            Text("2FA belum aktif", color = Color.White, fontWeight = FontWeight.Bold)
                            Text(
                                "Aktifkan 2FA terlebih dahulu untuk mengelola backup code.",
                                color = Color.White.copy(alpha = 0.85f),
                                fontSize = 13.sp
                            )
                            Button(
                                onClick = onSwitchToSetup,
                                modifier = Modifier.fillMaxWidth(),
                                colors = ButtonDefaults.buttonColors(
                                    containerColor = Color.White,
                                    contentColor = Color(0xFF2E7D32)
                                )
                            ) {
                                Text("Mulai Setup 2FA")
                            }
                        }
                    }
                }
            }

            is TwoFASetupViewModel.ManageUiState.Error -> {
                item {
                    Card(
                        modifier = Modifier.fillMaxWidth(),
                        colors = CardDefaults.cardColors(containerColor = Color.White.copy(alpha = 0.12f)),
                        shape = RoundedCornerShape(16.dp)
                    ) {
                        Column(
                            modifier = Modifier.padding(20.dp),
                            verticalArrangement = Arrangement.spacedBy(12.dp)
                        ) {
                            Text("Gagal memuat data", color = Color.White, fontWeight = FontWeight.Bold)
                            Text(state.message, color = Color.White.copy(alpha = 0.85f), fontSize = 13.sp)
                            Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                                OutlinedButton(onClick = onRetry) {
                                    Text("Coba lagi")
                                }
                                Button(
                                    onClick = onSwitchToSetup,
                                    colors = ButtonDefaults.buttonColors(
                                        containerColor = Color.White,
                                        contentColor = Color(0xFF2E7D32)
                                    )
                                ) {
                                    Text("Setup 2FA")
                                }
                            }
                        }
                    }
                }
            }

            is TwoFASetupViewModel.ManageUiState.ManageReady -> {
                item {
                    Card(
                        modifier = Modifier.fillMaxWidth(),
                        colors = CardDefaults.cardColors(containerColor = Color.White),
                        shape = RoundedCornerShape(16.dp)
                    ) {
                        Column(
                            modifier = Modifier.padding(20.dp),
                            verticalArrangement = Arrangement.spacedBy(8.dp)
                        ) {
                            Text(
                                text = "Backup code tersedia: ${state.remainingCount}",
                                color = Color(0xFF2E7D32),
                                fontWeight = FontWeight.Bold
                            )
                            if (!state.lastUsedAt.isNullOrBlank()) {
                                Text(
                                    text = "Terakhir dipakai: ${state.lastUsedAt}",
                                    color = Color.Gray,
                                    fontSize = 12.sp
                                )
                            }
                        }
                    }
                }

                item {
                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        horizontalArrangement = Arrangement.spacedBy(10.dp)
                    ) {
                        OutlinedButton(
                            onClick = onRetry,
                            modifier = Modifier.weight(1f)
                        ) {
                            Text("Refresh")
                        }

                        Button(
                            onClick = { showPasswordDialog = true },
                            modifier = Modifier.weight(1f),
                            colors = ButtonDefaults.buttonColors(
                                containerColor = Color.White,
                                contentColor = Color(0xFF2E7D32)
                            )
                        ) {
                            Text("Regenerate")
                        }
                    }
                }

                item {
                    BackupCodesSection(backupCodes = state.backupCodes)
                }
            }

            else -> Unit
        }
    }

    if (showPasswordDialog) {
        RegenerateBackupCodesDialog(
            onDismiss = { showPasswordDialog = false },
            onConfirm = { password ->
                showPasswordDialog = false
                onRegenerate(password)
            }
        )
    }
}

@Composable
private fun BackupCodesSection(backupCodes: List<String>) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        colors = CardDefaults.cardColors(containerColor = Color.White.copy(alpha = 0.1f)),
        shape = RoundedCornerShape(16.dp)
    ) {
        Column(modifier = Modifier.padding(20.dp)) {
            Text(
                text = "⚠️ Backup Codes",
                color = Color.White,
                fontSize = 16.sp,
                fontWeight = FontWeight.Bold
            )

            Spacer(modifier = Modifier.height(8.dp))

            Text(
                text = "Simpan kode-kode ini di tempat aman. Gunakan jika kehilangan akses ke authenticator.",
                color = Color.White.copy(alpha = 0.8f),
                fontSize = 12.sp,
                lineHeight = 16.sp
            )

            Spacer(modifier = Modifier.height(16.dp))

            LazyColumn(verticalArrangement = Arrangement.spacedBy(8.dp)) {
                items(backupCodes) { code ->
                    BackupCodeItem(code = code)
                }
            }
        }
    }
}

@Composable
private fun BackupCodeItem(code: String) {
    val context = LocalContext.current

    Row(
        modifier = Modifier
            .fillMaxWidth()
            .background(Color.White.copy(alpha = 0.1f), RoundedCornerShape(8.dp))
            .padding(12.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Text(
            text = code,
            color = Color.White,
            fontSize = 14.sp,
            fontFamily = androidx.compose.ui.text.font.FontFamily.Monospace,
            modifier = Modifier.weight(1f)
        )

        IconButton(
            onClick = {
                copyToClipboard(context, code)
                Toast.makeText(context, "Backup code disalin!", Toast.LENGTH_SHORT).show()
            }
        ) {
            Icon(
                Icons.Default.ContentCopy,
                contentDescription = "Copy",
                tint = Color.White.copy(alpha = 0.7f),
                modifier = Modifier.size(20.dp)
            )
        }
    }
}

@Composable
private fun RegenerateBackupCodesDialog(
    onDismiss: () -> Unit,
    onConfirm: (String) -> Unit
) {
    var password by remember { mutableStateOf("") }
    var passwordVisible by remember { mutableStateOf(false) }

    AlertDialog(
        onDismissRequest = onDismiss,
        title = { Text("Regenerate Backup Code") },
        text = {
            Column(verticalArrangement = Arrangement.spacedBy(12.dp)) {
                Text("Masukkan password akun untuk membuat backup code baru.")
                OutlinedTextField(
                    value = password,
                    onValueChange = { password = it },
                    label = { Text("Password") },
                    singleLine = true,
                    visualTransformation = if (passwordVisible) {
                        androidx.compose.ui.text.input.VisualTransformation.None
                    } else {
                        PasswordVisualTransformation()
                    },
                    trailingIcon = {
                        IconButton(onClick = { passwordVisible = !passwordVisible }) {
                            Icon(
                                imageVector = if (passwordVisible) Icons.Default.Visibility else Icons.Default.VisibilityOff,
                                contentDescription = null
                            )
                        }
                    }
                )
            }
        },
        confirmButton = {
            Button(
                onClick = { onConfirm(password) },
                enabled = password.isNotBlank()
            ) {
                Text("Regenerate")
            }
        },
        dismissButton = {
            TextButton(onClick = onDismiss) {
                Text("Batal")
            }
        }
    )
}

private fun copyToClipboard(context: Context, text: String) {
    val clipboard = context.getSystemService(Context.CLIPBOARD_SERVICE) as ClipboardManager
    val clip = ClipData.newPlainText("2FA Code", text)
    clipboard.setPrimaryClip(clip)
}
