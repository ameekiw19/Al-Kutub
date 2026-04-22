package com.example.al_kutub.utils

import android.util.LruCache
import com.tom_roush.pdfbox.pdmodel.PDDocument
import com.tom_roush.pdfbox.text.PDFTextStripper
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.sync.Mutex
import kotlinx.coroutines.sync.withLock
import kotlinx.coroutines.withContext
import java.io.File

class PdfTextExtractor(
    private val filePath: String
) {
    private val accessMutex = Mutex()
    private val pageTextCache = object : LruCache<Int, String>(48) {}
    private var document: PDDocument? = null

    suspend fun extractPageText(pageIndex: Int): Result<String> = withContext(Dispatchers.IO) {
        runCatching {
            pageTextCache.get(pageIndex)?.let { return@runCatching it }

            accessMutex.withLock {
                pageTextCache.get(pageIndex)?.let { return@withLock it }

                val doc = document ?: openDocument().also { document = it }
                require(pageIndex in 0 until doc.numberOfPages) {
                    "Halaman audio tidak tersedia."
                }

                val stripper = PDFTextStripper().apply {
                    startPage = pageIndex + 1
                    endPage = pageIndex + 1
                }
                val rawText = stripper.getText(doc)
                val normalizedText = normalizeExtractedText(rawText)
                pageTextCache.put(pageIndex, normalizedText)
                normalizedText
            }
        }
    }

    fun close() {
        try {
            document?.close()
        } catch (_: Exception) {
        } finally {
            document = null
            pageTextCache.evictAll()
        }
    }

    private fun openDocument(): PDDocument {
        val file = File(filePath)
        require(file.isFile) { "Lokasi PDF tidak valid." }
        require(file.exists()) { "File PDF tidak ditemukan." }
        require(file.canRead()) { "File PDF tidak dapat dibaca." }
        return PDDocument.load(file)
    }

    private fun normalizeExtractedText(text: String): String {
        return normalizeNarrationText(text)
    }
}
