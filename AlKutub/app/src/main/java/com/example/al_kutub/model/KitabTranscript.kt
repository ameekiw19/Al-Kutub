package com.example.al_kutub.model

import com.google.gson.annotations.SerializedName

data class KitabTranscriptPayload(
    @SerializedName("kitabId")
    val kitabId: Int = 0,

    @SerializedName("title")
    val title: String = "",

    @SerializedName("language")
    val language: String = "",

    @SerializedName("summaryText")
    val summaryText: String = "",

    @SerializedName("hasTranscript")
    val hasTranscript: Boolean = false,

    @SerializedName("hasSummaryTranscript")
    val hasSummaryTranscript: Boolean = false,

    @SerializedName("hasPageTranscript")
    val hasPageTranscript: Boolean = false,

    @SerializedName("pageMap")
    val pageMap: Map<String, String> = emptyMap(),

    @SerializedName("pageSegmentMap")
    val pageSegmentMap: Map<String, List<KitabTranscriptSegment>> = emptyMap(),

    @SerializedName("chapterMap")
    val chapterMap: Map<String, KitabTranscriptChapterInfo> = emptyMap(),

    @SerializedName("segments")
    val segments: List<KitabTranscriptSegment> = emptyList()
)

data class KitabTranscriptSegment(
    @SerializedName("id")
    val id: Int = 0,

    @SerializedName("key")
    val key: String? = null,

    @SerializedName("type")
    val type: String = "",

    @SerializedName("title")
    val title: String? = null,

    @SerializedName("content")
    val content: String = "",

    @SerializedName("textTranslation")
    val textTranslation: String = "",

    @SerializedName("textArabic")
    val textArabic: String = "",

    @SerializedName("translationParagraphs")
    val translationParagraphs: List<String> = emptyList(),

    @SerializedName("arabicParagraphs")
    val arabicParagraphs: List<String> = emptyList(),

    @SerializedName("language")
    val language: String? = null,

    @SerializedName("pageStart")
    val pageStart: Int? = null,

    @SerializedName("pageEnd")
    val pageEnd: Int? = null,

    @SerializedName("sortOrder")
    val sortOrder: Int = 0
)

data class KitabTranscriptChapterInfo(
    @SerializedName("key")
    val key: String? = null,

    @SerializedName("title")
    val title: String = "",

    @SerializedName("pageStart")
    val pageStart: Int? = null,

    @SerializedName("pageEnd")
    val pageEnd: Int? = null
)
