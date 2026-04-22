package com.example.al_kutub.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.BoxScope
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.offset
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardActions
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.foundation.Canvas
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.MenuBook
import androidx.compose.material.icons.filled.Visibility
import androidx.compose.material.icons.filled.VisibilityOff
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.OutlinedTextFieldDefaults
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.platform.LocalFocusManager
import androidx.compose.ui.platform.LocalSoftwareKeyboardController
import androidx.compose.ui.text.font.FontFamily
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.hilt.navigation.compose.hiltViewModel
import com.example.al_kutub.ui.viewmodel.LoginViewModel
import kotlin.math.roundToInt

private val LoginBackground = Color(0xFFFAFAF5)
private val LoginHeaderStart = Color(0xFF1B5E3B)
private val LoginHeaderMid = Color(0xFF2D7A52)
private val LoginHeaderEnd = Color(0xFF1A4A30)
private val LoginGold = Color(0xFFC8A951)
private val LoginFieldBackground = Color(0xFFF8F5EF)
private val LoginFieldBorder = Color(0xFFE8E3D5)
private val LoginCardBorder = Color(0xFFF0EBE0)
private val LoginErrorBackground = Color(0xFFFFF3F3)
private val LoginErrorBorder = Color(0xFFFFCDD2)
private val LoginErrorText = Color(0xFFC62828)
private val LoginPrimaryText = Color(0xFF1A2E1A)
private val LoginMutedText = Color(0xFF8B8070)
private val LoginLabelText = Color(0xFF3D2C1E)
private val LoginDivider = Color(0xFFE8E3D5)
private val LoginButtonDisabled = Color(0xFF8BAD9A)

@Composable
fun LoginScreen(
    onNavigateToRegister: () -> Unit = {},
    onNavigateToForgotPassword: () -> Unit = {},
    onLoginSuccess: () -> Unit,
    onNavigateTo2FA: (Int, String, String) -> Unit = { _, _, _ -> },
    onEmailVerificationRequired: (String, String) -> Unit = { _, _ -> },
    onNavigateBack: (() -> Unit)? = null,
    viewModel: LoginViewModel = hiltViewModel()
) {
    val error by viewModel.error.collectAsState()
    val isLoading by viewModel.isLoading.collectAsState()
    val keyboardController = LocalSoftwareKeyboardController.current
    val focusManager = LocalFocusManager.current

    var email by rememberSaveable { mutableStateOf("") }
    var password by rememberSaveable { mutableStateOf("") }
    var showPassword by rememberSaveable { mutableStateOf(false) }
    var localError by remember { mutableStateOf<String?>(null) }

    fun submitLogin() {
        if (email.isBlank() || password.isBlank()) {
            localError = "Harap isi semua kolom."
            return
        }

        localError = null
        focusManager.clearFocus()
        keyboardController?.hide()

        viewModel.login(
            username = email,
            password = password,
            onSuccess = onLoginSuccess,
            on2FARequired = { userId, tempToken, username ->
                onNavigateTo2FA(userId, tempToken, username)
            },
            onEmailVerificationRequired = { email, verificationToken ->
                onEmailVerificationRequired(email, verificationToken)
            }
        )
    }

    LaunchedEffect(Unit) {
        viewModel.reset2FAState()
    }

    val visibleError = localError ?: error

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(LoginBackground)
    ) {
        Column(
            modifier = Modifier
                .fillMaxSize()
                .verticalScroll(rememberScrollState())
        ) {
            LoginHeader(onNavigateBack = onNavigateBack)

            Card(
                modifier = Modifier
                    .fillMaxWidth()
                    .offset(y = (-32).dp)
                    .padding(horizontal = 20.dp),
                shape = RoundedCornerShape(28.dp),
                colors = CardDefaults.cardColors(containerColor = Color.White),
                elevation = CardDefaults.cardElevation(defaultElevation = 10.dp)
            ) {
                Column(
                    modifier = Modifier
                        .fillMaxWidth()
                        .border(1.dp, LoginCardBorder, RoundedCornerShape(28.dp))
                        .padding(22.dp),
                    verticalArrangement = Arrangement.spacedBy(14.dp)
                ) {
                    if (!visibleError.isNullOrBlank()) {
                        Box(
                            modifier = Modifier
                                .fillMaxWidth()
                                .clip(RoundedCornerShape(12.dp))
                                .background(LoginErrorBackground)
                                .border(1.dp, LoginErrorBorder, RoundedCornerShape(12.dp))
                                .padding(horizontal = 12.dp, vertical = 10.dp)
                        ) {
                            Text(
                                text = visibleError.orEmpty(),
                                color = LoginErrorText,
                                style = MaterialTheme.typography.bodySmall
                            )
                        }
                    }

                    AuthField(
                        label = "Email",
                        value = email,
                        onValueChange = {
                            email = it
                            localError = null
                            viewModel.clearError()
                        },
                        placeholder = "contoh@email.com",
                        keyboardOptions = KeyboardOptions(
                            keyboardType = KeyboardType.Email,
                            imeAction = ImeAction.Next
                        )
                    )

                    AuthField(
                        label = "Kata Sandi",
                        value = password,
                        onValueChange = {
                            password = it
                            localError = null
                            viewModel.clearError()
                        },
                        placeholder = "••••••••",
                        keyboardOptions = KeyboardOptions(
                            keyboardType = KeyboardType.Password,
                            imeAction = ImeAction.Done
                        ),
                        keyboardActions = KeyboardActions(onDone = { submitLogin() }),
                        visualTransformation = if (showPassword) {
                            VisualTransformation.None
                        } else {
                            PasswordVisualTransformation()
                        },
                        trailingIcon = {
                            IconButton(onClick = { showPassword = !showPassword }) {
                                Icon(
                                    imageVector = if (showPassword) {
                                        Icons.Default.VisibilityOff
                                    } else {
                                        Icons.Default.Visibility
                                    },
                                    contentDescription = "Toggle Password",
                                    tint = LoginMutedText
                                )
                            }
                        }
                    )

                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        horizontalArrangement = Arrangement.End
                    ) {
                        Text(
                            text = "Lupa kata sandi?",
                            color = LoginHeaderStart,
                            fontSize = 12.sp,
                            fontWeight = FontWeight.SemiBold,
                            modifier = Modifier.clickable(onClick = onNavigateToForgotPassword)
                        )
                    }

                    PrimaryGradientButton(
                        text = if (isLoading) "Memproses..." else "Masuk",
                        enabled = !isLoading,
                        onClick = { submitLogin() }
                    )

                    Row(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(vertical = 2.dp),
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Box(
                            modifier = Modifier
                                .weight(1f)
                                .height(1.dp)
                                .background(LoginDivider)
                        )
                        Text(
                            text = "atau",
                            color = LoginMutedText,
                            fontSize = 12.sp,
                            modifier = Modifier.padding(horizontal = 10.dp)
                        )
                        Box(
                            modifier = Modifier
                                .weight(1f)
                                .height(1.dp)
                                .background(LoginDivider)
                        )
                    }

                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        horizontalArrangement = Arrangement.Center
                    ) {
                        Text(
                            text = "Belum punya akun? ",
                            color = Color(0xFF6B5E4E),
                            fontSize = 14.sp
                        )
                        Text(
                            text = "Daftar",
                            color = LoginHeaderStart,
                            fontSize = 14.sp,
                            fontWeight = FontWeight.Bold,
                            modifier = Modifier.clickable(onClick = onNavigateToRegister)
                        )
                    }
                }
            }

            Spacer(modifier = Modifier.height(4.dp))

            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 16.dp, vertical = 12.dp),
                horizontalAlignment = Alignment.CenterHorizontally
            ) {
                Text(
                    text = "بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيم",
                    color = LoginGold,
                    fontFamily = FontFamily.Serif,
                    fontSize = 20.sp,
                    textAlign = TextAlign.Center
                )
                Text(
                    text = "Mulailah belajar dengan nama Allah",
                    color = LoginMutedText,
                    fontSize = 11.sp,
                    modifier = Modifier.padding(top = 4.dp)
                )
            }
        }
    }
}

@Composable
private fun LoginHeader(onNavigateBack: (() -> Unit)?) {
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .background(
                brush = Brush.linearGradient(
                    colors = listOf(LoginHeaderStart, LoginHeaderMid, LoginHeaderEnd)
                )
            )
            .padding(start = 20.dp, end = 20.dp, top = 20.dp, bottom = 64.dp)
    ) {
        LoginHeaderPattern()

        Column(modifier = Modifier.fillMaxWidth()) {
            if (onNavigateBack != null) {
                IconButton(
                    onClick = onNavigateBack,
                    modifier = Modifier.offset(x = (-8).dp)
                ) {
                    Icon(
                        imageVector = Icons.AutoMirrored.Filled.ArrowBack,
                        contentDescription = "Back",
                        tint = Color.White.copy(alpha = 0.82f)
                    )
                }
            } else {
                Spacer(modifier = Modifier.height(8.dp))
            }

            Row(
                verticalAlignment = Alignment.CenterVertically,
                modifier = Modifier.padding(top = 8.dp)
            ) {
                Box(
                    modifier = Modifier
                        .size(54.dp)
                        .clip(RoundedCornerShape(18.dp))
                        .background(LoginGold.copy(alpha = 0.2f)),
                    contentAlignment = Alignment.Center
                ) {
                    Icon(
                        imageVector = Icons.AutoMirrored.Filled.MenuBook,
                        contentDescription = "Logo",
                        tint = LoginGold,
                        modifier = Modifier.size(30.dp)
                    )
                }

                Spacer(modifier = Modifier.width(12.dp))

                Column {
                    Text(
                        text = "KitabKu",
                        color = Color.White,
                        fontWeight = FontWeight.ExtraBold,
                        fontSize = 22.sp
                    )
                    Text(
                        text = "Perpustakaan Kitab Ulama Digital",
                        color = Color.White.copy(alpha = 0.72f),
                        fontSize = 12.sp
                    )
                }
            }

            Text(
                text = "Selamat Datang",
                color = Color.White,
                fontWeight = FontWeight.Bold,
                fontSize = 25.sp,
                modifier = Modifier.padding(top = 14.dp)
            )
            Text(
                text = "Masuk untuk melanjutkan perjalanan ilmu",
                color = Color.White.copy(alpha = 0.78f),
                fontSize = 14.sp,
                modifier = Modifier.padding(top = 4.dp)
            )
        }
    }
}

@Composable
private fun BoxScope.LoginHeaderPattern() {
    Canvas(
        modifier = Modifier
            .fillMaxSize()
            .align(Alignment.Center)
    ) {
        val spacing = 60.dp.toPx()
        val radius = 1.dp.toPx()
        val rows = (size.height / spacing).roundToInt() + 2
        val cols = (size.width / spacing).roundToInt() + 2

        for (row in 0..rows) {
            for (col in 0..cols) {
                val x = col * spacing
                val y = row * spacing
                drawCircle(
                    color = LoginGold.copy(alpha = 0.08f),
                    radius = radius,
                    center = Offset(x + spacing * 0.2f, y + spacing * 0.5f)
                )
                drawCircle(
                    color = LoginGold.copy(alpha = 0.08f),
                    radius = radius,
                    center = Offset(x + spacing * 0.8f, y + spacing * 0.2f)
                )
            }
        }
    }
}

@Composable
private fun AuthField(
    label: String,
    value: String,
    onValueChange: (String) -> Unit,
    placeholder: String,
    keyboardOptions: KeyboardOptions,
    keyboardActions: KeyboardActions = KeyboardActions.Default,
    visualTransformation: VisualTransformation = VisualTransformation.None,
    trailingIcon: @Composable (() -> Unit)? = null
) {
    Column {
        Text(
            text = label,
            color = LoginLabelText,
            fontSize = 13.sp,
            fontWeight = FontWeight.SemiBold,
            modifier = Modifier.padding(bottom = 6.dp)
        )

        OutlinedTextField(
            value = value,
            onValueChange = onValueChange,
            modifier = Modifier.fillMaxWidth(),
            singleLine = true,
            placeholder = { Text(text = placeholder, color = LoginMutedText, fontSize = 14.sp) },
            visualTransformation = visualTransformation,
            keyboardOptions = keyboardOptions,
            keyboardActions = keyboardActions,
            trailingIcon = trailingIcon,
            shape = RoundedCornerShape(12.dp),
            colors = OutlinedTextFieldDefaults.colors(
                focusedContainerColor = LoginFieldBackground,
                unfocusedContainerColor = LoginFieldBackground,
                focusedBorderColor = LoginHeaderStart,
                unfocusedBorderColor = LoginFieldBorder,
                cursorColor = LoginHeaderStart,
                focusedTextColor = LoginPrimaryText,
                unfocusedTextColor = LoginPrimaryText
            )
        )
    }
}

@Composable
private fun PrimaryGradientButton(
    text: String,
    enabled: Boolean,
    onClick: () -> Unit
) {
    val buttonBrush = if (enabled) {
        Brush.linearGradient(colors = listOf(LoginHeaderStart, LoginHeaderMid))
    } else {
        Brush.linearGradient(colors = listOf(LoginButtonDisabled, LoginButtonDisabled))
    }

    Box(
        modifier = Modifier
            .fillMaxWidth()
            .clip(RoundedCornerShape(18.dp))
            .background(buttonBrush)
            .clickable(enabled = enabled, onClick = onClick)
            .padding(vertical = 14.dp),
        contentAlignment = Alignment.Center
    ) {
        Text(
            text = text,
            color = Color.White,
            fontSize = 15.sp,
            fontWeight = FontWeight.Bold
        )
    }
}
