package com.example.al_kutub.ui.screens

import android.os.Bundle
import android.widget.Toast
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.Visibility
import androidx.compose.material.icons.filled.VisibilityOff
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
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.example.al_kutub.ui.theme.AlKutubTheme
import com.example.al_kutub.ui.viewmodel.TwoFactorViewModel
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class TwoFactorVerificationActivity : ComponentActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        
        val userId = intent.getIntExtra("USER_ID", 0)
        val tempToken = intent.getStringExtra("TEMP_TOKEN") ?: ""
        val username = intent.getStringExtra("USERNAME") ?: ""

        setContent {
            AlKutubTheme {
                TwoFactorVerificationScreen(
                    userId = userId,
                    tempToken = tempToken,
                    username = username,
                    onBack = { finish() },
                    onSuccess = { token, user ->
                        // Save token and navigate to main
                        setResult(RESULT_OK)
                        finish()
                    }
                )
            }
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun TwoFactorVerificationScreen(
    userId: Int,
    tempToken: String,
    username: String,
    onBack: () -> Unit,
    onSuccess: (String, com.example.al_kutub.model.LoginData) -> Unit,
    viewModel: TwoFactorViewModel = hiltViewModel()
) {
    val uiState by viewModel.uiState.collectAsStateWithLifecycle()
    var code by remember { mutableStateOf("") }
    var isVisible by remember { mutableStateOf(false) }
    var useBackupCode by remember { mutableStateOf(false) }
    val context = LocalContext.current

    // Handle success
    LaunchedEffect(uiState) {
        when (val state = uiState) {
            is TwoFactorViewModel.TwoFactorUiState.Success -> {
                onSuccess(state.token, state.userData)
            }
            else -> {}
        }
    }

    LaunchedEffect(uiState) {
        val state = uiState
        if (state is TwoFactorViewModel.TwoFactorUiState.Error) {
            Toast.makeText(context, state.message, Toast.LENGTH_LONG).show()
        }
    }

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(
                Brush.verticalGradient(
                    colors = listOf(
                        Color(0xFF2E7D32), // Teal primary
                        Color(0xFF1B5E20)  // Darker teal
                    )
                )
            )
    ) {
        // Header
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
                text = "Verifikasi 2FA",
                color = Color.White,
                fontSize = 20.sp,
                fontWeight = FontWeight.Bold,
                modifier = Modifier.weight(1f)
            )
        }

        // Content
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(24.dp),
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.Center
        ) {
            // Icon
            Box(
                modifier = Modifier
                    .size(80.dp)
                    .background(
                        Color.White.copy(alpha = 0.2f),
                        RoundedCornerShape(40.dp)
                    ),
                contentAlignment = Alignment.Center
            ) {
                Text(
                    text = "🔐",
                    fontSize = 40.sp
                )
            }

            Spacer(modifier = Modifier.height(24.dp))

            // Title
            Text(
                text = "Two-Factor Authentication",
                color = Color.White,
                fontSize = 24.sp,
                fontWeight = FontWeight.Bold,
                textAlign = TextAlign.Center
            )

            Spacer(modifier = Modifier.height(8.dp))

            // Subtitle
            Text(
                text = if (useBackupCode) {
                    "Masukkan backup code 8 karakter Anda"
                } else {
                    "Masukkan kode 6-digit dari aplikasi authenticator Anda"
                },
                color = Color.White.copy(alpha = 0.8f),
                fontSize = 14.sp,
                textAlign = TextAlign.Center,
                lineHeight = 20.sp
            )

            Spacer(modifier = Modifier.height(32.dp))

            // Code Input
            OutlinedTextField(
                value = code,
                onValueChange = { 
                    val sanitized = if (useBackupCode) {
                        it.filter { ch -> ch.isLetterOrDigit() }.uppercase()
                    } else {
                        it.filter { ch -> ch.isDigit() }
                    }
                    val maxLength = if (useBackupCode) 8 else 6
                    if (sanitized.length <= maxLength) {
                        code = sanitized
                    }
                },
                label = { 
                    Text(
                        "Kode Verifikasi",
                        color = Color.White.copy(alpha = 0.7f)
                    ) 
                },
                placeholder = { 
                    Text(
                        if (useBackupCode) "AB12CD34" else "000000",
                        color = Color.White.copy(alpha = 0.5f)
                    ) 
                },
                modifier = Modifier
                    .fillMaxWidth()
                    .clip(RoundedCornerShape(12.dp)),
                keyboardOptions = KeyboardOptions(
                    keyboardType = if (useBackupCode) KeyboardType.Text else KeyboardType.Number
                ),
                visualTransformation = if (isVisible) {
                    VisualTransformation.None
                } else {
                    PasswordVisualTransformation()
                },
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
                ),
                trailingIcon = {
                    IconButton(onClick = { isVisible = !isVisible }) {
                        Icon(
                            if (isVisible) Icons.Default.Visibility else Icons.Default.VisibilityOff,
                            contentDescription = if (isVisible) "Hide" else "Show",
                            tint = Color.White.copy(alpha = 0.7f)
                        )
                    }
                }
            )

            Spacer(modifier = Modifier.height(16.dp))

            // Backup code option
            TextButton(
                onClick = {
                    useBackupCode = !useBackupCode
                    code = ""
                }
            ) {
                Text(
                    text = if (useBackupCode) "Gunakan Kode Authenticator" else "Gunakan Backup Code",
                    color = Color.White,
                    fontSize = 14.sp
                )
            }

            Spacer(modifier = Modifier.height(32.dp))

            // Verify Button
            Button(
                onClick = {
                    val isValid = if (useBackupCode) code.length == 8 else code.length == 6
                    if (isValid) {
                        viewModel.verify2FA(userId, code, tempToken)
                    } else {
                        val message = if (useBackupCode) {
                            "Masukkan 8 karakter backup code"
                        } else {
                            "Masukkan 6 digit kode"
                        }
                        Toast.makeText(context, message, Toast.LENGTH_SHORT).show()
                    }
                },
                modifier = Modifier
                    .fillMaxWidth()
                    .height(56.dp),
                enabled = ((useBackupCode && code.length == 8) || (!useBackupCode && code.length == 6))
                    && uiState !is TwoFactorViewModel.TwoFactorUiState.Loading,
                colors = ButtonDefaults.buttonColors(
                    containerColor = Color.White,
                    contentColor = Color(0xFF2E7D32)
                ),
                shape = RoundedCornerShape(12.dp)
            ) {
                if (uiState is TwoFactorViewModel.TwoFactorUiState.Loading) {
                    CircularProgressIndicator(
                        modifier = Modifier.size(24.dp),
                        color = Color(0xFF2E7D32),
                        strokeWidth = 2.dp
                    )
                } else {
                    Text(
                        text = "Verifikasi",
                        fontSize = 16.sp,
                        fontWeight = FontWeight.Bold
                    )
                }
            }

            Spacer(modifier = Modifier.height(16.dp))

            // Help text
            AnimatedVisibility(visible = code.isNotEmpty()) {
                Text(
                    text = if (useBackupCode) {
                        "Backup code hanya bisa digunakan satu kali demi keamanan akun."
                    } else {
                        "Pastikan kode dari aplikasi authenticator seperti Google Authenticator atau Authy."
                    },
                    color = Color.White.copy(alpha = 0.6f),
                    fontSize = 12.sp,
                    textAlign = TextAlign.Center,
                    lineHeight = 16.sp
                )
            }
        }
    }
}
