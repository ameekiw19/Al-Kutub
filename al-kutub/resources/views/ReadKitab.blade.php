@extends('TemplateUser')

@section('konten')
<style>
    .reader-wrapper {
        max-width: 1280px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .reader-header {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 14px 16px;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    .reader-title-group {
        min-width: 0;
    }

    .reader-title {
        margin: 0;
        color: var(--text-color);
        font-size: 1.15rem;
        font-weight: 700;
        line-height: 1.3;
    }

    .reader-subtitle {
        color: var(--light-text);
        font-size: 0.9rem;
        margin-top: 2px;
    }

    .reader-controls {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .reader-btn {
        border: 1px solid var(--border-color);
        background: var(--card-bg);
        color: var(--text-color);
        border-radius: 12px;
        padding: 8px 12px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
    }

    .reader-btn:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    .reader-btn.primary {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        border-color: var(--primary-color);
        color: #fff;
        box-shadow: 0 2px 8px rgba(27, 94, 59, 0.18);
    }

    .reader-page-control {
        display: flex;
        align-items: center;
        gap: 6px;
        background: rgba(148, 163, 184, 0.12);
        border-radius: 10px;
        padding: 6px 10px;
    }

    .reader-page-control input {
        width: 58px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: var(--card-bg);
        color: var(--text-color);
        text-align: center;
        padding: 6px 4px;
        outline: none;
        font-weight: 600;
    }

    .reader-main {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 14px;
    }

    .pdf-stage {
        background: #d7d7d7;
        border-radius: 16px;
        min-height: 72vh;
        position: relative;
        overflow: auto;
        border: 1px solid #c9c9c9;
    }

    .pdf-inner {
        min-height: 72vh;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 20px 14px;
    }

    #pdfCanvas {
        background: #fff;
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.15);
        border-radius: 6px;
        max-width: 100%;
    }

    .reader-loading,
    .reader-error {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 10px;
        text-align: center;
        padding: 20px;
        background: rgba(245, 245, 245, 0.95);
        color: #334155;
    }

    .reader-loading i {
        font-size: 1.35rem;
        color: var(--primary-color);
    }

    .reader-error {
        display: none;
    }

    .reader-side,
    .reader-transcript-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 16px;
    }

    .reader-side {
        display: flex;
        flex-direction: column;
        min-height: 72vh;
        max-height: 78vh;
    }

    .reader-side-header,
    .reader-transcript-head {
        padding: 12px 14px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .reader-side-title,
    .reader-transcript-title {
        margin: 0;
        color: var(--text-color);
        font-size: 0.95rem;
        font-weight: 700;
    }

    .reader-transcript-subtitle {
        color: var(--light-text);
        font-size: 0.84rem;
        margin-top: 4px;
        line-height: 1.5;
    }

    .reader-transcript-badge {
        border-radius: 999px;
        border: 1px solid rgba(27, 94, 59, 0.14);
        background: rgba(27, 94, 59, 0.08);
        color: var(--primary-color);
        padding: 6px 10px;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .marker-list,
    .transcript-list {
        padding: 12px;
        overflow: auto;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .marker-item {
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 10px;
        cursor: pointer;
        transition: 0.2s;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .marker-item:hover {
        border-color: var(--primary-color);
        background: rgba(68, 161, 148, 0.08);
    }

    .marker-page {
        font-size: 0.78rem;
        color: var(--primary-color);
        font-weight: 700;
    }

    .marker-label {
        font-size: 0.9rem;
        color: var(--text-color);
        font-weight: 600;
    }

    .marker-actions {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .marker-icon-btn {
        border: 1px solid var(--border-color);
        background: var(--card-bg);
        border-radius: 8px;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--light-text);
        cursor: pointer;
    }

    .marker-empty,
    .transcript-empty {
        padding: 20px 12px;
        text-align: center;
        color: var(--light-text);
        font-size: 0.9rem;
        line-height: 1.7;
        border: 1px dashed var(--border-color);
        border-radius: 14px;
        background: rgba(248, 250, 252, 0.8);
    }

    .transcript-segment {
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 14px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(247, 250, 247, 0.96));
    }

    .transcript-segment-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .transcript-segment-title {
        color: var(--text-color);
        font-size: 0.96rem;
        font-weight: 800;
        margin: 0;
    }

    .transcript-segment-meta {
        color: var(--light-text);
        font-size: 0.82rem;
        margin-top: 2px;
    }

    .transcript-translation {
        margin-top: 12px;
        color: var(--text-color);
        font-size: 0.96rem;
        line-height: 1.8;
    }

    .transcript-translation p,
    .transcript-arabic p {
        margin: 0 0 10px;
    }

    .transcript-translation p:last-child,
    .transcript-arabic p:last-child {
        margin-bottom: 0;
    }

    .transcript-arabic {
        margin-top: 14px;
        border-top: 1px dashed rgba(148, 163, 184, 0.3);
        padding-top: 12px;
        color: var(--text-color);
    }

    .transcript-arabic-label {
        color: var(--light-text);
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 8px;
    }

    .transcript-arabic-body {
        font-size: 1.08rem;
        line-height: 2;
        text-align: right;
    }

    .hidden {
        display: none !important;
    }

    @media (max-width: 1024px) {
        .reader-main {
            grid-template-columns: 1fr;
        }

        .reader-side {
            min-height: 250px;
            max-height: 320px;
        }

        .reader-side.collapsed {
            display: none;
        }
    }
</style>

<div class="reader-wrapper">
    <div class="reader-header">
        <div class="reader-title-group">
            <h1 class="reader-title">{{ $kitab->judul }}</h1>
            <div class="reader-subtitle">
                {{ $kitab->penulis }} · {{ $kitab->kategori }}
            </div>
        </div>

        <div class="reader-controls">
            <button class="reader-btn" type="button" id="btnBackDetail">
                <i class="fas fa-arrow-left"></i> Detail
            </button>
            <button class="reader-btn" type="button" id="btnPrevPage">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="reader-page-control">
                <input id="pageInput" type="number" min="1" value="1">
                <span>/ <span id="totalPages">0</span></span>
            </div>
            <button class="reader-btn" type="button" id="btnNextPage">
                <i class="fas fa-chevron-right"></i>
            </button>
            <button class="reader-btn primary" type="button" id="btnAddMarker">
                <i class="fas fa-bookmark"></i> Tambah Marker
            </button>
            <button class="reader-btn" type="button" id="btnToggleMarker">
                Marker (<span id="markerCount">0</span>)
            </button>
        </div>
    </div>

    <div class="reader-main">
        <div class="pdf-stage" id="pdfStage">
            <div class="pdf-inner">
                <canvas id="pdfCanvas"></canvas>
            </div>
            <div class="reader-loading" id="readerLoading">
                <i class="fas fa-spinner fa-spin"></i>
                <div>Menyiapkan PDF...</div>
            </div>
            <div class="reader-error" id="readerError">
                <i class="fas fa-triangle-exclamation" style="font-size: 1.4rem; color: #dc2626;"></i>
                <div id="readerErrorText">PDF gagal dibuka.</div>
                <div style="display:flex; gap:8px; flex-wrap:wrap; justify-content:center;">
                    <button class="reader-btn primary" type="button" id="btnRetry">Coba Lagi</button>
                    <a class="reader-btn" href="{{ route('kitab.view', $kitab->id_kitab) }}">Kembali</a>
                </div>
            </div>
        </div>

        <aside class="reader-side" id="markerPanel">
            <div class="reader-side-header">
                <h3 class="reader-side-title">Daftar Marker Halaman</h3>
                <span style="font-size:0.82rem; color:var(--light-text);" id="markerSummary">0 marker</span>
            </div>
            <div class="marker-list" id="markerList"></div>
        </aside>
    </div>

    <section class="reader-transcript-card">
        <div class="reader-transcript-head">
            <div>
                <h2 class="reader-transcript-title" id="readerTranscriptTitle">Teks Terjemahan Halaman 1</h2>
                <div class="reader-transcript-subtitle" id="readerTranscriptSubtitle">
                    Setiap bagian menampilkan terjemahan utama dan teks Arab bila tersedia.
                </div>
            </div>
            <div class="reader-transcript-badge" id="readerTranscriptBadge">0 bagian</div>
        </div>
        <div class="transcript-list" id="readerTranscriptList"></div>
    </section>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    (function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || @json(csrf_token());
        const pdfUrl = @json(asset('pdf/' . $kitab->file_pdf));
        const transcriptPayload = @json($transcriptPayload ?? null);
        const transcriptPageMap = transcriptPayload?.pageMap || {};
        const transcriptPageSegmentMap = transcriptPayload?.pageSegmentMap || {};
        const transcriptChapterMap = transcriptPayload?.chapterMap || {};
        const initialResumePage = Math.max(1, Number(@json($resumePage ?? 1)) || 1);
        let markers = @json($initialMarkers ?? []);

        const progressUrl = @json(route('kitab.read.progress', ['id_kitab' => $kitab->id_kitab]));
        const markerStoreUrl = @json(route('kitab.read.markers.store', ['id_kitab' => $kitab->id_kitab]));
        const markerUpdateTemplate = @json(route('kitab.read.markers.update', ['id_kitab' => $kitab->id_kitab, 'bookmarkId' => '__BOOKMARK_ID__']));
        const markerDeleteTemplate = @json(route('kitab.read.markers.destroy', ['id_kitab' => $kitab->id_kitab, 'bookmarkId' => '__BOOKMARK_ID__']));

        const canvas = document.getElementById('pdfCanvas');
        const stage = document.getElementById('pdfStage');
        const loadingOverlay = document.getElementById('readerLoading');
        const errorOverlay = document.getElementById('readerError');
        const errorText = document.getElementById('readerErrorText');
        const pageInput = document.getElementById('pageInput');
        const totalPagesEl = document.getElementById('totalPages');
        const markerListEl = document.getElementById('markerList');
        const markerCountEl = document.getElementById('markerCount');
        const markerSummaryEl = document.getElementById('markerSummary');
        const markerPanel = document.getElementById('markerPanel');
        const readerTranscriptTitle = document.getElementById('readerTranscriptTitle');
        const readerTranscriptSubtitle = document.getElementById('readerTranscriptSubtitle');
        const readerTranscriptBadge = document.getElementById('readerTranscriptBadge');
        const readerTranscriptList = document.getElementById('readerTranscriptList');

        const OPEN_TIMEOUT_MS = 6000;
        const PAGE_TIMEOUT_MS = 4000;

        let pdfDoc = null;
        let renderTask = null;
        let currentPage = 1;
        let totalPages = 0;
        let isRendering = false;
        let pendingPage = null;
        let trackingEnabled = false;
        let syncInFlight = false;
        let syncQueuedForce = false;
        let lastSyncedAt = 0;
        let lastSyncedPage = initialResumePage;
        let transcriptRenderToken = 0;
        const pageTextCache = new Map();

        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        function clampPage(page) {
            if (!totalPages || totalPages < 1) return 1;
            return Math.min(Math.max(1, page), totalPages);
        }

        function setLoading(loading) {
            loadingOverlay.classList.toggle('hidden', !loading);
        }

        function setError(message) {
            errorText.textContent = message || 'PDF gagal dibuka.';
            errorOverlay.style.display = 'flex';
            setLoading(false);
        }

        function hideError() {
            errorOverlay.style.display = 'none';
        }

        function normalizeNarrationText(text) {
            return String(text || '')
                .replace(/\u0000/g, '')
                .replace(/([A-Za-z\u00C0-\u024F\u0600-\u06FF])-\s*\n([A-Za-z\u00C0-\u024F\u0600-\u06FF])/g, '$1$2')
                .replace(/^\s*\d+\s*$/gm, '')
                .replace(/\r\n/g, '\n')
                .replace(/[ \t]+/g, ' ')
                .replace(/\s+\n/g, '\n')
                .replace(/\n\s+/g, '\n')
                .replace(/\n{3,}/g, '\n\n')
                .trim();
        }

        function splitIntoParagraphs(text) {
            const normalized = normalizeNarrationText(text);
            if (!normalized) return [];

            return normalized
                .split(/\n{2,}|\n/g)
                .map((paragraph) => normalizeNarrationText(paragraph))
                .filter(Boolean);
        }

        function escapeHtml(value) {
            return String(value || '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function getChapterForPage(pageNumber) {
            return transcriptChapterMap[String(pageNumber)] || transcriptChapterMap[pageNumber] || null;
        }

        function renderParagraphHtml(paragraphs, emptyText = '') {
            if (!Array.isArray(paragraphs) || paragraphs.length === 0) {
                return emptyText ? `<p>${escapeHtml(emptyText)}</p>` : '';
            }

            return paragraphs.map((paragraph) => `<p>${escapeHtml(paragraph)}</p>`).join('');
        }

        function formatMarkerCount() {
            markerSummaryEl.textContent = `${markers.length} marker`;
            markerCountEl.textContent = String(markers.length);
        }

        function sortedMarkers() {
            return [...markers].sort((a, b) => Number(a.page_number) - Number(b.page_number));
        }

        function renderMarkerList() {
            formatMarkerCount();
            const items = sortedMarkers();

            if (items.length === 0) {
                markerListEl.innerHTML = '<div class="marker-empty">Belum ada marker.<br>Tambah dari halaman aktif.</div>';
                return;
            }

            markerListEl.innerHTML = items.map((item) => `
                <div class="marker-item" data-action="jump" data-page="${item.page_number}">
                    <div>
                        <div class="marker-page">Halaman ${item.page_number}</div>
                        <div class="marker-label">${escapeHtml(item.label || ('Halaman ' + item.page_number))}</div>
                    </div>
                    <div class="marker-actions">
                        <button class="marker-icon-btn" data-action="edit" data-id="${item.id}" title="Edit label">
                            <i class="fas fa-pen" style="font-size: 11px;"></i>
                        </button>
                        <button class="marker-icon-btn" data-action="delete" data-id="${item.id}" title="Hapus marker">
                            <i class="fas fa-trash" style="font-size: 11px;"></i>
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function buildFallbackSegment(pageNumber, text) {
            const normalizedText = normalizeNarrationText(text);

            return {
                id: 0,
                key: `page-${pageNumber}-fallback`,
                type: 'page',
                title: `Halaman ${pageNumber}`,
                content: normalizedText,
                textTranslation: normalizedText,
                textArabic: '',
                translationParagraphs: splitIntoParagraphs(normalizedText),
                arabicParagraphs: [],
                pageStart: pageNumber,
                pageEnd: pageNumber,
                sortOrder: 0,
            };
        }

        function renderSegmentCard(segment) {
            const translationParagraphs = Array.isArray(segment.translationParagraphs) && segment.translationParagraphs.length
                ? segment.translationParagraphs
                : splitIntoParagraphs(segment.textTranslation || segment.content || '');
            const arabicParagraphs = Array.isArray(segment.arabicParagraphs) && segment.arabicParagraphs.length
                ? segment.arabicParagraphs
                : splitIntoParagraphs(segment.textArabic || '');
            const title = segment.title || (segment.pageStart ? `Halaman ${segment.pageStart}` : 'Bagian Kitab');
            const meta = [];

            if (segment.type) {
                meta.push(String(segment.type).toUpperCase());
            }

            if (segment.pageStart) {
                const suffix = segment.pageEnd && segment.pageEnd !== segment.pageStart ? `-${segment.pageEnd}` : '';
                meta.push(`halaman ${segment.pageStart}${suffix}`);
            }

            return `
                <article class="transcript-segment">
                    <div class="transcript-segment-header">
                        <div>
                            <h3 class="transcript-segment-title">${escapeHtml(title)}</h3>
                            <div class="transcript-segment-meta">${escapeHtml(meta.join(' • '))}</div>
                        </div>
                    </div>
                    <div class="transcript-translation">
                        ${renderParagraphHtml(translationParagraphs, 'Terjemahan untuk bagian ini belum tersedia.')}
                    </div>
                    ${arabicParagraphs.length ? `
                        <div class="transcript-arabic">
                            <div class="transcript-arabic-label">Teks Arab</div>
                            <div class="transcript-arabic-body" dir="rtl">
                                ${renderParagraphHtml(arabicParagraphs)}
                            </div>
                        </div>
                    ` : ''}
                </article>
            `;
        }

        async function resolvePageSegments(pageNumber) {
            const mappedSegments = transcriptPageSegmentMap[String(pageNumber)] || transcriptPageSegmentMap[pageNumber];
            if (Array.isArray(mappedSegments) && mappedSegments.length) {
                return mappedSegments;
            }

            const transcriptText = normalizeNarrationText(transcriptPageMap[String(pageNumber)] || transcriptPageMap[pageNumber] || '');
            if (transcriptText) {
                return [buildFallbackSegment(pageNumber, transcriptText)];
            }

            if (!pdfDoc) {
                return [];
            }

            const extractedText = await extractPageText(pageNumber);
            return extractedText ? [buildFallbackSegment(pageNumber, extractedText)] : [];
        }

        async function renderTranscriptPanel(pageNumber) {
            const requestToken = ++transcriptRenderToken;
            const targetPage = clampPage(pageNumber);
            const chapter = getChapterForPage(targetPage);

            readerTranscriptTitle.textContent = `Teks Terjemahan Halaman ${targetPage}`;
            readerTranscriptSubtitle.textContent = chapter
                ? `${chapter.title} • terjemahan utama dan teks Arab ditampilkan per bagian di bawah.`
                : 'Setiap bagian menampilkan terjemahan utama dan teks Arab bila tersedia.';

            let segments = [];
            try {
                segments = await resolvePageSegments(targetPage);
            } catch (error) {
                console.warn('Gagal memuat transcript:', error?.message || error);
            }

            if (requestToken !== transcriptRenderToken) {
                return;
            }

            const safeSegments = Array.isArray(segments) ? segments : [];
            readerTranscriptBadge.textContent = `${safeSegments.length} bagian`;

            if (safeSegments.length === 0) {
                readerTranscriptList.innerHTML = `
                    <div class="transcript-empty">
                        Belum ada transcript terstruktur untuk halaman ini.
                        Tambahkan transcript pada kitab ini agar isi per halaman tersusun otomatis.
                    </div>
                `;
                return;
            }

            readerTranscriptList.innerHTML = safeSegments.map(renderSegmentCard).join('');
        }

        async function fetchJson(url, options = {}) {
            const response = await fetch(url, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(options.headers || {})
                },
                ...options,
            });

            const raw = await response.text();
            let data = {};

            if (raw) {
                try {
                    data = JSON.parse(raw);
                } catch (error) {
                    throw new Error('Respons server tidak valid');
                }
            }

            if (!response.ok || data.success === false) {
                throw new Error(data.message || `Request gagal (${response.status})`);
            }

            return data;
        }

        async function extractPageText(pageNumber) {
            const safePageNumber = clampPage(pageNumber);
            if (pageTextCache.has(safePageNumber)) {
                return pageTextCache.get(safePageNumber);
            }

            const page = await pdfDoc.getPage(safePageNumber);
            const textContent = await page.getTextContent();
            const fragments = [];
            let previousY = null;

            (textContent.items || []).forEach((item) => {
                const value = String(item?.str || '').trim();
                if (!value) return;

                const y = Array.isArray(item.transform) ? Number(item.transform[5]) : null;

                if (previousY !== null && y !== null) {
                    if (Math.abs(previousY - y) > 9) {
                        fragments.push('\n');
                    } else if (fragments.length && fragments[fragments.length - 1] !== '\n') {
                        fragments.push(' ');
                    }
                } else if (fragments.length) {
                    fragments.push(' ');
                }

                fragments.push(value);
                previousY = y;
            });

            const pageText = normalizeNarrationText(fragments.join(''));
            pageTextCache.set(safePageNumber, pageText);
            return pageText;
        }

        async function openPdfWithTimeout() {
            const task = pdfjsLib.getDocument({ url: pdfUrl, withCredentials: false });

            const timeoutPromise = new Promise((_, reject) => {
                setTimeout(() => reject(new Error('Waktu membuka PDF habis')), OPEN_TIMEOUT_MS);
            });

            return Promise.race([task.promise, timeoutPromise]);
        }

        async function resolveStartPage(maxPage) {
            const targetPage = Math.min(initialResumePage, maxPage);
            if (targetPage <= 1) return 1;

            const result = await Swal.fire({
                title: 'Lanjutkan bacaan?',
                text: `Terakhir di halaman ${targetPage}.`,
                icon: 'question',
                showDenyButton: true,
                confirmButtonText: 'Lanjutkan',
                denyButtonText: 'Mulai dari awal',
                reverseButtons: true,
            });

            return result.isDenied ? 1 : targetPage;
        }

        async function renderPage(pageNumber) {
            if (!pdfDoc) return;
            const targetPage = clampPage(pageNumber);

            currentPage = targetPage;
            pageInput.value = String(targetPage);
            setLoading(true);

            if (renderTask) {
                try {
                    renderTask.cancel();
                } catch (error) {}
            }

            isRendering = true;

            try {
                const pagePromise = Promise.race([
                    pdfDoc.getPage(targetPage),
                    new Promise((_, reject) => setTimeout(() => reject(new Error('Waktu render halaman habis')), PAGE_TIMEOUT_MS)),
                ]);

                const page = await pagePromise;
                const baseViewport = page.getViewport({ scale: 1 });
                const containerWidth = Math.max(360, stage.clientWidth - 40);
                const scale = Math.max(0.7, Math.min(2.2, containerWidth / baseViewport.width));
                const viewport = page.getViewport({ scale });
                const context = canvas.getContext('2d');

                canvas.width = Math.floor(viewport.width);
                canvas.height = Math.floor(viewport.height);
                canvas.style.width = `${Math.floor(viewport.width)}px`;
                canvas.style.height = `${Math.floor(viewport.height)}px`;

                renderTask = page.render({ canvasContext: context, viewport });
                await Promise.race([
                    renderTask.promise,
                    new Promise((_, reject) => setTimeout(() => reject(new Error('Render halaman terlalu lama')), PAGE_TIMEOUT_MS)),
                ]);

                hideError();
                await renderTranscriptPanel(targetPage);

                if (trackingEnabled) {
                    syncProgress(false);
                }
            } catch (error) {
                setError(error?.message || 'Gagal merender halaman PDF');
            } finally {
                isRendering = false;
                setLoading(false);

                if (pendingPage !== null) {
                    const nextPage = pendingPage;
                    pendingPage = null;
                    renderPage(nextPage);
                }
            }
        }

        function queueRenderPage(pageNumber) {
            const targetPage = clampPage(pageNumber);
            if (!pdfDoc) return;

            if (isRendering) {
                pendingPage = targetPage;
                return;
            }

            renderPage(targetPage);
        }

        function shouldSync(force) {
            if (!trackingEnabled || !pdfDoc || currentPage < 1) return false;
            if (force) return true;

            const now = Date.now();
            const dueByTime = now - lastSyncedAt >= 20000;
            const dueByJump = Math.abs(currentPage - lastSyncedPage) >= 3;
            const dueByFinish = totalPages > 0 && currentPage >= totalPages;

            return dueByTime || dueByJump || dueByFinish;
        }

        async function syncProgress(force) {
            if (!shouldSync(force)) return;

            if (syncInFlight) {
                syncQueuedForce = syncQueuedForce || !!force;
                return;
            }

            syncInFlight = true;

            try {
                await fetchJson(progressUrl, {
                    method: 'POST',
                    body: JSON.stringify({
                        current_page: currentPage,
                        total_pages: totalPages,
                        last_position: `page:${currentPage}`,
                    }),
                });

                lastSyncedAt = Date.now();
                lastSyncedPage = currentPage;
            } catch (error) {
                console.warn('Gagal sync progress:', error?.message || error);
            } finally {
                syncInFlight = false;
                if (syncQueuedForce) {
                    const nextForce = syncQueuedForce;
                    syncQueuedForce = false;
                    syncProgress(nextForce);
                }
            }
        }

        async function addMarker() {
            if (!pdfDoc) return;
            if (markers.some((marker) => Number(marker.page_number) === Number(currentPage))) {
                Swal.fire({
                    icon: 'info',
                    title: 'Marker sudah ada',
                    text: `Halaman ${currentPage} sudah ditandai.`,
                    timer: 1600,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                });
                return;
            }

            try {
                const response = await fetchJson(markerStoreUrl, {
                    method: 'POST',
                    body: JSON.stringify({
                        page_number: currentPage,
                        label: `Halaman ${currentPage}`,
                    }),
                });

                const saved = response.data;
                const existingIndex = markers.findIndex((item) => Number(item.id) === Number(saved.id));
                if (existingIndex >= 0) {
                    markers[existingIndex] = saved;
                } else {
                    markers.push(saved);
                }

                renderMarkerList();
                Swal.fire({
                    icon: 'success',
                    title: 'Marker ditambahkan',
                    timer: 1300,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                });
            } catch (error) {
                Swal.fire('Gagal', error?.message || 'Tidak dapat menambah marker', 'error');
            }
        }

        async function renameMarker(markerId) {
            const marker = markers.find((item) => Number(item.id) === Number(markerId));
            if (!marker) return;

            const result = await Swal.fire({
                title: `Edit label halaman ${marker.page_number}`,
                input: 'text',
                inputValue: marker.label || `Halaman ${marker.page_number}`,
                inputPlaceholder: 'Masukkan label marker',
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal',
            });

            if (!result.isConfirmed) return;

            try {
                const updateUrl = markerUpdateTemplate.replace('__BOOKMARK_ID__', String(markerId));
                const response = await fetchJson(updateUrl, {
                    method: 'PUT',
                    body: JSON.stringify({
                        label: String(result.value || '').trim(),
                    }),
                });

                markers = markers.map((item) => Number(item.id) === Number(markerId) ? response.data : item);
                renderMarkerList();
            } catch (error) {
                Swal.fire('Gagal', error?.message || 'Tidak dapat mengubah marker', 'error');
            }
        }

        async function deleteMarker(markerId) {
            try {
                const deleteUrl = markerDeleteTemplate.replace('__BOOKMARK_ID__', String(markerId));
                await fetchJson(deleteUrl, {
                    method: 'DELETE',
                });

                markers = markers.filter((item) => Number(item.id) !== Number(markerId));
                renderMarkerList();
            } catch (error) {
                Swal.fire('Gagal', error?.message || 'Tidak dapat menghapus marker', 'error');
            }
        }

        async function initReader() {
            pageTextCache.clear();
            setLoading(true);
            hideError();

            try {
                pdfDoc = await openPdfWithTimeout();
                totalPages = Number(pdfDoc.numPages || 0);
                totalPagesEl.textContent = String(totalPages);
                pageInput.max = String(Math.max(totalPages, 1));

                const startPage = await resolveStartPage(totalPages);
                currentPage = clampPage(startPage);
                await renderPage(currentPage);
                trackingEnabled = true;
                syncProgress(true);
            } catch (error) {
                setError(error?.message || 'PDF gagal dibuka');
            }
        }

        document.getElementById('btnBackDetail').addEventListener('click', () => {
            syncProgress(true);
            window.location.href = @json(route('kitab.view', $kitab->id_kitab));
        });

        document.getElementById('btnPrevPage').addEventListener('click', () => {
            queueRenderPage(currentPage - 1);
        });

        document.getElementById('btnNextPage').addEventListener('click', () => {
            queueRenderPage(currentPage + 1);
        });

        pageInput.addEventListener('change', () => {
            queueRenderPage(Number(pageInput.value || 1));
        });

        pageInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                pageInput.blur();
                queueRenderPage(Number(pageInput.value || 1));
            }
        });

        document.getElementById('btnAddMarker').addEventListener('click', addMarker);

        document.getElementById('btnToggleMarker').addEventListener('click', () => {
            markerPanel.classList.toggle('collapsed');
        });

        markerListEl.addEventListener('click', (event) => {
            const actionEl = event.target.closest('[data-action]');
            if (!actionEl) return;

            const action = actionEl.getAttribute('data-action');
            if (action === 'jump') {
                queueRenderPage(Number(actionEl.getAttribute('data-page') || 1));
                return;
            }

            const markerId = Number(actionEl.getAttribute('data-id') || 0);
            if (!markerId) return;

            event.stopPropagation();
            if (action === 'edit') {
                renameMarker(markerId);
            } else if (action === 'delete') {
                deleteMarker(markerId);
            }
        });

        document.getElementById('btnRetry').addEventListener('click', () => {
            initReader();
        });

        window.addEventListener('resize', () => {
            if (!pdfDoc) return;
            clearTimeout(window.__readerResizeTimeout);
            window.__readerResizeTimeout = setTimeout(() => {
                queueRenderPage(currentPage);
            }, 220);
        });

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                syncProgress(true);
            }
        });

        window.addEventListener('beforeunload', () => {
            syncProgress(true);
        });

        renderMarkerList();
        initReader();
    })();
</script>
@endsection
