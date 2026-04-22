package com.example.al_kutub.utils

import com.example.al_kutub.model.KitabTranscriptPayload
import com.example.al_kutub.model.KitabTranscriptSegment

fun normalizeNarrationText(text: String): String {
    return text
        .replace("\u0000", "")
        .replace("\r\n", "\n")
        .replace(Regex("([\\p{L}\\u0600-\\u06FF])-\\s*\\n([\\p{L}\\u0600-\\u06FF])"), "$1$2")
        .replace(Regex("^\\s*\\d+\\s*$", RegexOption.MULTILINE), "")
        .replace(Regex("[\\t ]+"), " ")
        .replace(Regex("\\s+\\n"), "\n")
        .replace(Regex("\\n\\s+"), "\n")
        .replace(Regex("\\n{3,}"), "\n\n")
        .trim()
}

fun KitabTranscriptPayload.resolvePageNarration(pageNumber: Int): String {
    return normalizeNarrationText(pageMap[pageNumber.toString()].orEmpty())
}

fun KitabTranscriptPayload.resolvePageSegments(pageNumber: Int): List<KitabTranscriptSegment> {
    return pageSegmentMap[pageNumber.toString()].orEmpty()
}

fun KitabTranscriptSegment.displayTitle(fallbackPageNumber: Int? = pageStart): String {
    return title?.takeIf { it.isNotBlank() }
        ?: fallbackPageNumber?.let { "Halaman $it" }
        ?: "Bagian Kitab"
}

fun KitabTranscriptSegment.translationText(): String {
    return normalizeNarrationText(textTranslation.ifBlank { content })
}

fun KitabTranscriptSegment.arabicText(): String {
    return normalizeNarrationText(textArabic)
}
