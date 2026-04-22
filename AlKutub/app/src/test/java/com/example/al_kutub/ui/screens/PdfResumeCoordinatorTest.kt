package com.example.al_kutub.ui.screens

import org.junit.Assert.assertEquals
import org.junit.Assert.assertFalse
import org.junit.Assert.assertTrue
import org.junit.Test

class PdfResumeCoordinatorTest {

    @Test
    fun prepare_requiresDecisionWhenInitialPageGreaterThanOne() {
        val state = PdfResumeCoordinator.prepare(initialPage = 12, pageCount = 300)
        assertEquals(12, state.currentPage)
        assertEquals(12, state.resumeTargetPage)
        assertTrue(state.resumeDecisionPending)
        assertTrue(state.showContinueDialog)
    }

    @Test
    fun prepare_noDecisionOnFirstPage() {
        val state = PdfResumeCoordinator.prepare(initialPage = 1, pageCount = 300)
        assertFalse(state.resumeDecisionPending)
        assertFalse(state.showContinueDialog)
        assertEquals(1, state.currentPage)
    }

    @Test
    fun shouldTrackVisiblePage_falseWhenDecisionPending() {
        assertFalse(PdfResumeCoordinator.shouldTrackVisiblePage(true))
        assertTrue(PdfResumeCoordinator.shouldTrackVisiblePage(false))
    }
}
