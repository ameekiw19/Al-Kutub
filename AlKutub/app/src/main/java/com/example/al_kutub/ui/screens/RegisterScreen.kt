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
import androidx.compose.material.icons.filled.CheckCircle
import androidx.compose.material.icons.filled.Lock
import androidx.compose.material.icons.filled.Mail
import androidx.compose.material.icons.filled.Person
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
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.hilt.navigation.compose.hiltViewModel
import com.example.al_kutub.ui.viewmodel.RegisterViewModel
import kotlin.math.roundToInt

private val RegisterBackground = Color(0xFFFAFAF5)
private val RegisterHeaderStart = Color(0xFF1B5E3B)
private val RegisterHeaderMid = Color(0xFF2D7A52)
private val RegisterHeaderEnd = Color(0xFF1A4A30)
private val RegisterGold = Color(0xFFC8A951)
private val RegisterFieldBackground = Color(0xFFF8F5EF)
private val RegisterFieldBorder = Color(0xFFE8E3D5)
private val RegisterCardBorder = Color(0xFFF0EBE0)
private val RegisterErrorBackground = Color(0xFFFFF3F3)
private val RegisterErrorBorder = Color(0xFFFFCDD2)
private val RegisterErrorText = Color(0xFFC62828)
private val RegisterPrimaryText = Color(0xFF1A2E1A)
private val RegisterMutedText = Color(0xFF8B8070)
private val RegisterLabelText = Color(0xFF3D2C1E)
private val RegisterDivider = Color(0xFFE8E3D5)
private val RegisterButtonDisabled = Color(0xFF8BAD9A)

@Composable
fun RegisterScreen(
    onRegisterSuccess: () -> Unit = {},
    onEmailVerificationPending: (String, String) -> Unit = { _, _ -> },
    onNavigateBack: (() -> Unit)? = null,
    onNavigateToLogin: () -> Unit = {},
    viewModel: RegisterViewModel = hiltViewModel()
) {
    val isLoading by viewModel.isLoading.collectAsState()
    val error by viewModel.error.collectAsState()
    val keyboardController = LocalSoftwareKeyboardController.current
    val focusManager = LocalFocusManager.current

    var name by rememberSaveable { mutableStateOf("") }
    var email by rememberSaveable { mutableStateOf("") }
    var password by rememberSaveable { mutableStateOf("") }
    var confirmPassword by rememberSaveable { mutableStateOf("") }
    var showPassword by rememberSaveable { mutableStateOf(false) }
    var localError by remember { mutableStateOf<String?>(null) }

    val strength = passwordStrength(password)
    val visibleError = localError ?: error

    fun submitRegister() {
        when {
            name.isBlank() || email.isBlank() || password.isBlank() || confirmPassword.isBlank() -> {
                localError = "Harap isi semua kolom."
                return
            }

            password.length < 6 -> {
                localError = "Kata sandi minimal 6 karakter."
                return
            }

            password != confirmPassword -> {
                localError = "Konfirmasi kata sandi tidak cocok."
                return
            }
        }

        localError = null
        focusManager.clearFocus()
        keyboardController?.hide()

        viewModel.register(
            username = name,
            password = password,
            email = email,
            phone = null,
            deskripsi = null,
            onSuccess = onRegisterSuccess,
            onEmailVerificationPending = { pendingEmail, verificationToken ->
                onEmailVerificationPending(pendingEmail, verificationToken)
            }
        )
    }

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(RegisterBackground)
    ) {
        Column(
            modifier = Modifier
                .fillMaxSize()
                .verticalScroll(rememberScrollState())
        ) {
            RegisterHeader(onNavigateBack = onNavigateBack)

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
                        .border(1.dp, RegisterCardBorder, RoundedCornerShape(28.dp))
                        .padding(22.dp),
                    verticalArrangement = Arrangement.spacedBy(14.dp)
                ) {
                    if (!visibleError.isNullOrBlank()) {
                        Box(
                            modifier = Modifier
                                .fillMaxWidth()
                                .clip(RoundedCornerShape(12.dp))
                                .background(RegisterErrorBackground)
                                .border(1.dp, RegisterErrorBorder, RoundedCornerShape(12.dp))
                                .padding(horizontal = 12.dp, vertical = 10.dp)
                        ) {
                            Text(
                                text = visibleError.orEmpty(),
                                color = RegisterErrorText,
                                style = MaterialTheme.typography.bodySmall
                            )
                        }
                    }

                    RegisterField(
                        label = "Nama Lengkap",
                        value = name,
                        onValueChange = {
                            name = it
                            localError = null
                            viewModel.clearError()
                        },
                        placeholder = "Nama kamu",
                        leadingIcon = Icons.Default.Person,
                        keyboardOptions = KeyboardOptions(
                            keyboardType = KeyboardType.Text,
                            imeAction = ImeAction.Next
                        )
                    )

                    RegisterField(
                        label = "Email",
                        value = email,
                        onValueChange = {
                            email = it
                            localError = null
                            viewModel.clearError()
                        },
                        placeholder = "contoh@email.com",
                        leadingIcon = Icons.Default.Mail,
                        keyboardOptions = KeyboardOptions(
                            keyboardType = KeyboardType.Email,
                            imeAction = ImeAction.Next
                        )
                    )

                    RegisterField(
                        label = "Kata Sandi",
                        value = password,
                        onValueChange = {
                            password = it
                            localError = null
                            viewModel.clearError()
                        },
                        placeholder = "Min. 6 karakter",
                        leadingIcon = Icons.Default.Lock,
                        keyboardOptions = KeyboardOptions(
                            keyboardType = KeyboardType.Password,
                            imeAction = ImeAction.Next
                        ),
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
                                    tint = RegisterMutedText
                                )
                            }
                        }
                    )

                    if (password.isNotBlank()) {
                        PasswordStrengthBar(
                            level = strength.level,
                            label = strength.label,
                            color = strength.color
                        )
                    }

                    RegisterField(
                        label = "Konfirmasi Kata Sandi",
                        value = confirmPassword,
                        onValueChange = {
                            confirmPassword = it
                            localError = null
                            viewModel.clearError()
                        },
                        placeholder = "Ulangi kata sandi",
                        leadingIcon = if (confirmPassword.isNotBlank() && confirmPassword == password) {
                            Icons.Default.CheckCircle
                        } else {
                            Icons.Default.Lock
                        },
                        keyboardOptions = KeyboardOptions(
                            keyboardType = KeyboardType.Password,
                            imeAction = ImeAction.Done
                        ),
                        keyboardActions = KeyboardActions(onDone = { submitRegister() }),
                        visualTransformation = PasswordVisualTransformation(),
                        customBorderColor = if (confirmPassword.isNotBlank() && confirmPassword != password) {
                            RegisterErrorBorder
                        } else {
                            RegisterFieldBorder
                        }
                    )

                    PrimaryRegisterButton(
                        text = if (isLoading) "Mendaftar..." else "Daftar Sekarang",
                        enabled = !isLoading,
                        onClick = { submitRegister() }
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
                                .background(RegisterDivider)
                        )
                        Text(
                            text = "atau",
                            color = RegisterMutedText,
                            fontSize = 12.sp,
                            modifier = Modifier.padding(horizontal = 10.dp)
                        )
                        Box(
                            modifier = Modifier
                                .weight(1f)
                                .height(1.dp)
                                .background(RegisterDivider)
                        )
                    }

                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        horizontalArrangement = Arrangement.Center
                    ) {
                        Text(
                            text = "Sudah punya akun? ",
                            color = Color(0xFF6B5E4E),
                            fontSize = 14.sp
                        )
                        Text(
                            text = "Masuk",
                            color = RegisterHeaderStart,
                            fontSize = 14.sp,
                            fontWeight = FontWeight.Bold,
                            modifier = Modifier.clickable(onClick = onNavigateToLogin)
                        )
                    }
                }
            }
        }
    }
}

@Composable
private fun RegisterHeader(onNavigateBack: (() -> Unit)?) {
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .background(
                brush = Brush.linearGradient(
                    colors = listOf(RegisterHeaderStart, RegisterHeaderMid, RegisterHeaderEnd)
                )
            )
            .padding(start = 20.dp, end = 20.dp, top = 20.dp, bottom = 64.dp)
    ) {
        RegisterHeaderPattern()

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
                        .background(RegisterGold.copy(alpha = 0.2f)),
                    contentAlignment = Alignment.Center
                ) {
                    Icon(
                        imageVector = Icons.AutoMirrored.Filled.MenuBook,
                        contentDescription = "Logo",
                        tint = RegisterGold,
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
                text = "Buat Akun Baru",
                color = Color.White,
                fontWeight = FontWeight.Bold,
                fontSize = 25.sp,
                modifier = Modifier.padding(top = 14.dp)
            )
            Text(
                text = "Bergabunglah dan nikmati koleksi kitab ulama",
                color = Color.White.copy(alpha = 0.78f),
                fontSize = 14.sp,
                modifier = Modifier.padding(top = 4.dp)
            )
        }
    }
}

@Composable
private fun BoxScope.RegisterHeaderPattern() {
    Canvas(
        modifier = Modifier
            .fillMaxSize()
            .align(Alignment.Center)
    ) {
        val spacing = 50.dp.toPx()
        val radius = 1.dp.toPx()
        val rows = (size.height / spacing).roundToInt() + 2
        val cols = (size.width / spacing).roundToInt() + 2

        for (row in 0..rows) {
            for (col in 0..cols) {
                val x = col * spacing + spacing * 0.3f
                val y = row * spacing + spacing * 0.5f
                drawCircle(
                    color = RegisterGold.copy(alpha = 0.08f),
                    radius = radius,
                    center = Offset(x, y)
                )
            }
        }
    }
}

@Composable
private fun RegisterField(
    label: String,
    value: String,
    onValueChange: (String) -> Unit,
    placeholder: String,
    leadingIcon: ImageVector,
    keyboardOptions: KeyboardOptions,
    keyboardActions: KeyboardActions = KeyboardActions.Default,
    visualTransformation: VisualTransformation = VisualTransformation.None,
    trailingIcon: @Composable (() -> Unit)? = null,
    customBorderColor: Color = RegisterFieldBorder
) {
    Column {
        Text(
            text = label,
            color = RegisterLabelText,
            fontSize = 13.sp,
            fontWeight = FontWeight.SemiBold,
            modifier = Modifier.padding(bottom = 6.dp)
        )

        OutlinedTextField(
            value = value,
            onValueChange = onValueChange,
            modifier = Modifier.fillMaxWidth(),
            singleLine = true,
            placeholder = { Text(text = placeholder, color = RegisterMutedText, fontSize = 14.sp) },
            leadingIcon = {
                Icon(
                    imageVector = leadingIcon,
                    contentDescription = null,
                    tint = RegisterMutedText
                )
            },
            trailingIcon = trailingIcon,
            visualTransformation = visualTransformation,
            keyboardOptions = keyboardOptions,
            keyboardActions = keyboardActions,
            shape = RoundedCornerShape(12.dp),
            colors = OutlinedTextFieldDefaults.colors(
                focusedContainerColor = RegisterFieldBackground,
                unfocusedContainerColor = RegisterFieldBackground,
                focusedBorderColor = RegisterHeaderStart,
                unfocusedBorderColor = customBorderColor,
                cursorColor = RegisterHeaderStart,
                focusedTextColor = RegisterPrimaryText,
                unfocusedTextColor = RegisterPrimaryText
            )
        )
    }
}

@Composable
private fun PasswordStrengthBar(level: Int, label: String, color: Color) {
    Column {
        Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.spacedBy(4.dp)
        ) {
            repeat(3) { index ->
                Box(
                    modifier = Modifier
                        .weight(1f)
                        .height(4.dp)
                        .clip(RoundedCornerShape(10.dp))
                        .background(if (index < level) color else RegisterDivider)
                )
            }
        }
        Text(
            text = label,
            color = color,
            fontSize = 11.sp,
            modifier = Modifier.padding(top = 4.dp)
        )
    }
}

@Composable
private fun PrimaryRegisterButton(
    text: String,
    enabled: Boolean,
    onClick: () -> Unit
) {
    val buttonBrush = if (enabled) {
        Brush.linearGradient(colors = listOf(RegisterHeaderStart, RegisterHeaderMid))
    } else {
        Brush.linearGradient(colors = listOf(RegisterButtonDisabled, RegisterButtonDisabled))
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

private data class PasswordStrength(val level: Int, val label: String, val color: Color)

private fun passwordStrength(password: String): PasswordStrength {
    if (password.isBlank()) {
        return PasswordStrength(level = 0, label = "", color = RegisterMutedText)
    }
    if (password.length < 6) {
        return PasswordStrength(level = 1, label = "Lemah", color = Color(0xFFE53935))
    }
    if (password.length < 10) {
        return PasswordStrength(level = 2, label = "Sedang", color = Color(0xFFF57C00))
    }
    return PasswordStrength(level = 3, label = "Kuat", color = Color(0xFF2E7D32))
}
