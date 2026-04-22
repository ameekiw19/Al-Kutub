package com.example.al_kutub.model


import com.google.gson.annotations.SerializedName

data class Kitab(
    // Coba SEMUA kemungkinan nama field untuk ID
    @SerializedName(value = "idKitab", alternate = ["id_kitab", "id", "kitab_id"])
    val idKitab: Int = 0,  // Default 0 jika gagal parse

    @SerializedName("judul")
    val judul: String = "",

    @SerializedName("penulis")
    val penulis: String = "",

    @SerializedName("kategori")
    val kategori: String = "",

    @SerializedName("deskripsi")
    val deskripsi: String = "",

    @SerializedName("bahasa")
    val bahasa: String = "",

    @SerializedName("cover")
    val cover: String = "",

    @SerializedName(value = "views", alternate = ["pembaca"])
    val views: Int = 0,

    @SerializedName("downloads")
    val downloads: Int = 0,

    @SerializedName(value = "filePdf", alternate = ["file_pdf", "pdf", "pdf_url", "urlPdf"])
    val filePdf: String = "",

    @SerializedName("averageRating")
    val averageRating: Double = 0.0,

    @SerializedName("ratingsCount")
    val ratingsCount: Int = 0,
    
    // Additional fields for HomePage compatibility
    @SerializedName("lastReadTime")
    val lastReadTime: Long = 0L
) {
    // Debug function untuk logging
    override fun toString(): String {
        return "Kitab(idKitab=$idKitab, judul='$judul', penulis='$penulis')"
    }
}

// Extension for compatibility with HomePage
fun Kitab.toKitabCompat(): KitabCompat {
    return KitabCompat(
        id = idKitab.toString(),
        title = judul,
        author = penulis,
        category = kategori,
        description = deskripsi,
        language = bahasa,
        coverImage = cover.ifEmpty { "https://picsum.photos/seed/$judul/200/300.jpg" },
        views = views,
        downloads = downloads,
        filePdf = filePdf,
        averageRating = averageRating,
        ratingsCount = ratingsCount,
        lastReadTime = lastReadTime
    )
}

data class KitabCompat(
    val id: String,
    val title: String,
    val author: String,
    val category: String,
    val description: String,
    val language: String,
    val coverImage: String,
    val views: Int,
    val downloads: Int,
    val filePdf: String,
    val averageRating: Double,
    val ratingsCount: Int,
    val lastReadTime: Long = 0L
)

// Extension for UiKitab compatibility
fun UiKitab.toKitabCompat(): KitabCompat {
    return KitabCompat(
        id = idKitab.toString(),
        title = judul,
        author = penulis,
        category = kategori,
        description = deskripsi,
        language = bahasa,
        coverImage = cover.ifEmpty { "https://picsum.photos/seed/$judul/200/300.jpg" },
        views = views,
        downloads = downloads,
        filePdf = "",
        averageRating = 0.0,
        ratingsCount = 0,
        lastReadTime = 0L
    )
}