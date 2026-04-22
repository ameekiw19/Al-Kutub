package com.example.al_kutub.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
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
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.hilt.navigation.compose.hiltViewModel
import com.example.al_kutub.ui.viewmodel.AccountUiState
import com.example.al_kutub.ui.theme.*
import com.example.al_kutub.ui.viewmodel.AccountViewModel

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun EditProfileScreen(
    onBack: () -> Unit,
    onSuccess: () -> Unit,
    viewModel: AccountViewModel = hiltViewModel()
) {
    val accountState by viewModel.accountState.collectAsState()
    val profile = when (val s = accountState) {
        is AccountUiState.Success -> s.data.profile
        else -> null
    }

    var username by remember(profile) { mutableStateOf(profile?.username ?: "") }
    var email by remember(profile) { mutableStateOf(profile?.email ?: "") }
    var deskripsi by remember(profile) { mutableStateOf(profile?.deskripsi ?: "") }
    var password by remember { mutableStateOf("") }
    var passwordConfirmation by remember { mutableStateOf("") }
    var passwordVisible by remember { mutableStateOf(false) }
    val updateProfileState by viewModel.updateProfileState.collectAsState()
    val context = LocalContext.current

    LaunchedEffect(Unit) {
        viewModel.loadAccount()
    }

    LaunchedEffect(profile) {
        profile?.let { p ->
            username = p.username
            email = p.email
            deskripsi = p.deskripsi ?: ""
        }
    }

    LaunchedEffect(updateProfileState) {
        when (updateProfileState) {
            true -> {
                onSuccess()
                viewModel.resetUpdateProfileState()
            }
            false -> {
                viewModel.resetUpdateProfileState()
            }
            null -> {}
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = {
                    Text(
                        "Edit Profil",
                        fontWeight = FontWeight.SemiBold,
                        fontSize = 18.sp
                    )
                },
                navigationIcon = {
                    IconButton(onClick = onBack) {
                        Icon(
                            Icons.AutoMirrored.Filled.ArrowBack,
                            contentDescription = "Kembali"
                        )
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = MaterialTheme.colorScheme.surface,
                    titleContentColor = MaterialTheme.colorScheme.onSurface
                )
            )
        },
        containerColor = MaterialTheme.colorScheme.background
    ) { paddingValues ->
        when (accountState) {
            is AccountUiState.Loading -> {
                Box(
                    modifier = Modifier
                        .fillMaxSize()
                        .padding(paddingValues),
                    contentAlignment = Alignment.Center
                ) {
                    CircularProgressIndicator(color = TealMain)
                }
            }
            is AccountUiState.Error -> {
                Box(
                    modifier = Modifier
                        .fillMaxSize()
                        .padding(paddingValues),
                    contentAlignment = Alignment.Center
                ) {
                    Text((accountState as AccountUiState.Error).message)
                }
            }
            else -> ProfileFormContent(
                paddingValues = paddingValues,
                username = username,
                onUsernameChange = { username = it },
                email = email,
                onEmailChange = { email = it },
                deskripsi = deskripsi,
                onDeskripsiChange = { deskripsi = it },
                password = password,
                onPasswordChange = { password = it },
                passwordConfirmation = passwordConfirmation,
                onPasswordConfirmationChange = { passwordConfirmation = it },
                passwordVisible = passwordVisible,
                onPasswordVisibleToggle = { passwordVisible = !passwordVisible },
                updateProfile = {
                    if (password.isNotEmpty() && password != passwordConfirmation) return@ProfileFormContent
                    viewModel.updateProfile(
                        username = username.trim(),
                        email = email.trim(),
                        deskripsi = deskripsi.trim().ifBlank { null },
                        password = password.ifBlank { null },
                        passwordConfirmation = passwordConfirmation.ifBlank { null }
                    )
                }
            )
        }
    }
}

@Composable
private fun ProfileFormContent(
    paddingValues: PaddingValues,
    username: String,
    onUsernameChange: (String) -> Unit,
    email: String,
    onEmailChange: (String) -> Unit,
    deskripsi: String,
    onDeskripsiChange: (String) -> Unit,
    password: String,
    onPasswordChange: (String) -> Unit,
    passwordConfirmation: String,
    onPasswordConfirmationChange: (String) -> Unit,
    passwordVisible: Boolean,
    onPasswordVisibleToggle: () -> Unit,
    updateProfile: () -> Unit
) {
    Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(paddingValues)
                .verticalScroll(rememberScrollState())
                .padding(16.dp)
        ) {
            OutlinedTextField(
                value = username,
                onValueChange = onUsernameChange,
                label = { Text("Username") },
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(bottom = 12.dp),
                shape = RoundedCornerShape(16.dp),
                singleLine = true
            )

            OutlinedTextField(
                value = email,
                onValueChange = onEmailChange,
                label = { Text("Email") },
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(bottom = 12.dp),
                shape = RoundedCornerShape(16.dp),
                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Email),
                singleLine = true
            )

            OutlinedTextField(
                value = deskripsi,
                onValueChange = onDeskripsiChange,
                label = { Text("Deskripsi (opsional)") },
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(bottom = 12.dp)
                    .height(120.dp),
                shape = RoundedCornerShape(16.dp),
                maxLines = 4
            )

            Text(
                "Ubah Password (opsional)",
                style = MaterialTheme.typography.titleSmall.copy(
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                    fontWeight = FontWeight.Medium
                ),
                modifier = Modifier.padding(top = 8.dp, bottom = 8.dp)
            )

            OutlinedTextField(
                value = password,
                onValueChange = onPasswordChange,
                label = { Text("Password Baru") },
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(bottom = 12.dp),
                shape = RoundedCornerShape(16.dp),
                visualTransformation = if (passwordVisible) VisualTransformation.None else PasswordVisualTransformation(),
                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password),
                trailingIcon = {
                    IconButton(onClick = onPasswordVisibleToggle) {
                        Icon(
                            if (passwordVisible) Icons.Default.VisibilityOff else Icons.Default.Visibility,
                            contentDescription = if (passwordVisible) "Sembunyikan" else "Tampilkan"
                        )
                    }
                },
                singleLine = true
            )

            OutlinedTextField(
                value = passwordConfirmation,
                onValueChange = onPasswordConfirmationChange,
                label = { Text("Konfirmasi Password") },
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(bottom = 24.dp),
                shape = RoundedCornerShape(16.dp),
                visualTransformation = if (passwordVisible) VisualTransformation.None else PasswordVisualTransformation(),
                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password),
                singleLine = true
            )

            Button(
                onClick = updateProfile,
                modifier = Modifier
                    .fillMaxWidth()
                    .height(52.dp),
                shape = RoundedCornerShape(14.dp),
                colors = ButtonDefaults.buttonColors(containerColor = TealMain)
            ) {
                Text(
                    "Simpan",
                    fontWeight = FontWeight.SemiBold,
                    fontSize = 16.sp
                )
            }

            if (password.isNotEmpty() && password != passwordConfirmation) {
                Text(
                    "Password dan konfirmasi harus sama",
                    color = ErrorRed,
                    style = MaterialTheme.typography.bodySmall,
                    modifier = Modifier.padding(top = 8.dp)
                )
            }
        }
}
