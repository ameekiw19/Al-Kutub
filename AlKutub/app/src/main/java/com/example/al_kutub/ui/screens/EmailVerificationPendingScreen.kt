package com.example.al_kutub.ui.screens

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.Button
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.material3.TopAppBar
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.collectAsState
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import com.example.al_kutub.ui.viewmodel.EmailVerificationViewModel

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun EmailVerificationPendingScreen(
    email: String,
    verificationToken: String,
    onBackToLogin: () -> Unit,
    onVerified: () -> Unit,
    viewModel: EmailVerificationViewModel = hiltViewModel()
) {
    val isResending by viewModel.isResending.collectAsState()
    val isChecking by viewModel.isChecking.collectAsState()
    val isVerified by viewModel.isVerified.collectAsState()
    val message by viewModel.message.collectAsState()
    val error by viewModel.error.collectAsState()

    LaunchedEffect(verificationToken) {
        viewModel.setInitialToken(verificationToken)
    }

    LaunchedEffect(isVerified) {
        if (isVerified) {
            onVerified()
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(title = { Text("Verifikasi Email") })
        }
    ) { paddingValues ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(paddingValues)
                .padding(20.dp),
            verticalArrangement = Arrangement.Top
        ) {
            Text(
                text = "Akun Anda belum terverifikasi.",
                style = MaterialTheme.typography.titleMedium
            )
            Spacer(modifier = Modifier.height(8.dp))
            Text(
                text = "Kami telah mengirim link verifikasi ke: $email",
                style = MaterialTheme.typography.bodyMedium
            )

            Spacer(modifier = Modifier.height(20.dp))

            Button(
                onClick = { viewModel.checkStatus() },
                enabled = !isChecking,
                modifier = Modifier.fillMaxWidth()
            ) {
                if (isChecking) {
                    CircularProgressIndicator(strokeWidth = 2.dp)
                } else {
                    Text("Saya Sudah Verifikasi")
                }
            }

            Spacer(modifier = Modifier.height(10.dp))

            OutlinedButton(
                onClick = { viewModel.resendVerification() },
                enabled = !isResending,
                modifier = Modifier.fillMaxWidth()
            ) {
                if (isResending) {
                    CircularProgressIndicator(strokeWidth = 2.dp)
                } else {
                    Text("Kirim Ulang Email Verifikasi")
                }
            }

            if (!message.isNullOrBlank()) {
                Spacer(modifier = Modifier.height(12.dp))
                Text(
                    text = message ?: "",
                    color = MaterialTheme.colorScheme.primary,
                    style = MaterialTheme.typography.bodySmall
                )
            }

            if (!error.isNullOrBlank()) {
                Spacer(modifier = Modifier.height(12.dp))
                Text(
                    text = error ?: "",
                    color = MaterialTheme.colorScheme.error,
                    style = MaterialTheme.typography.bodySmall
                )
            }

            Spacer(modifier = Modifier.height(12.dp))

            TextButton(onClick = onBackToLogin) {
                Text("Kembali ke Login")
            }
        }
    }
}
