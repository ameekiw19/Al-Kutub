package com.example.al_kutub.ui.screens

internal object PdfResumeCoordinator {
    data class ResumeState(
        val currentPage: Int,
        val resumeTargetPage: Int,
        val resumeDecisionPending: Boolean,
        val showContinueDialog: Boolean
    )

    fun prepare(initialPage: Int, pageCount: Int): ResumeState {
        val safePageCount = pageCount.coerceAtLeast(1)
        val safeInitialPage = initialPage.coerceIn(1, safePageCount)
        val needsDecision = safeInitialPage > 1
        return ResumeState(
            currentPage = safeInitialPage,
            resumeTargetPage = safeInitialPage,
            resumeDecisionPending = needsDecision,
            showContinueDialog = needsDecision
        )
    }

    fun shouldTrackVisiblePage(resumeDecisionPending: Boolean): Boolean = !resumeDecisionPending
}
