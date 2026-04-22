package com.example.al_kutub.ui

import android.Manifest
import androidx.compose.ui.test.assertIsDisplayed
import androidx.compose.ui.test.hasText
import androidx.compose.ui.test.junit4.createAndroidComposeRule
import androidx.compose.ui.test.onNodeWithText
import androidx.compose.ui.test.performClick
import androidx.test.rule.GrantPermissionRule
import com.example.al_kutub.MainActivity
import org.junit.Rule
import org.junit.Test

class MainActivitySmokeTest {

    @get:Rule(order = 0)
    val permissionRule: GrantPermissionRule = GrantPermissionRule.grant(
        Manifest.permission.POST_NOTIFICATIONS
    )

    @get:Rule(order = 1)
    val composeTestRule = createAndroidComposeRule<MainActivity>()

    private fun waitForLoginScreen() {
        composeTestRule.waitUntil(timeoutMillis = 6_000) {
            composeTestRule
                .onAllNodes(hasText("Selamat Datang"))
                .fetchSemanticsNodes().isNotEmpty()
        }
    }

    @Test
    fun loginScreen_isDisplayed() {
        waitForLoginScreen()
        composeTestRule.onNodeWithText("Selamat Datang").assertIsDisplayed()
        composeTestRule.onNodeWithText("Masuk").assertIsDisplayed()
    }

    @Test
    fun canNavigate_toRegisterScreen() {
        waitForLoginScreen()
        composeTestRule.onNodeWithText("Daftar").performClick()
        composeTestRule.onNodeWithText("Buat Akun Baru").assertIsDisplayed()
    }
}
