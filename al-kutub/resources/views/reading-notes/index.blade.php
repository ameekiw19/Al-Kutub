@extends('TemplateUser')

@section('konten')
<div class="reading-notes-header">
    <div class="section-title">
        <h3><i class="fas fa-sticky-note me-2"></i> Catatan Baca</h3>
    </div>
    <a href="{{ route('reading-notes.create') }}" class="btn-add">
        <i class="fas fa-plus"></i> Tambah Catatan
    </a>
</div>

@if(session('success'))
<div class="alert-success">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<div class="reading-notes-filter">
    <form action="{{ route('reading-notes.index') }}" method="GET" class="filter-form">
        <select name="kitab_id" class="filter-select" onchange="this.form.submit()">
            <option value="">Semua Kitab</option>
            @foreach($kitabs as $k)
                <option value="{{ $k->id_kitab }}" {{ request('kitab_id') == $k->id_kitab ? 'selected' : '' }}>
                    {{ \Illuminate\Support\Str::limit($k->judul, 50) }}
                </option>
            @endforeach
        </select>
    </form>
</div>

<div class="reading-notes-container">
    @if($notes->count() > 0)
        <div class="notes-grid">
            @foreach($notes as $note)
                <div class="note-card">
                    <div class="note-header">
                        <a href="{{ route('kitab.view', $note->kitab->id_kitab ?? 0) }}" class="note-kitab">
                            @if($note->kitab)
                                {{ \Illuminate\Support\Str::limit($note->kitab->judul, 40) }}
                            @else
                                Kitab tidak ditemukan
                            @endif
                        </a>
                        <span class="note-badge {{ $note->is_private ? 'private' : 'public' }}">
                            {{ $note->is_private ? 'Pribadi' : 'Publik' }}
                        </span>
                    </div>
                    <div class="note-content">
                        {{ \Illuminate\Support\Str::limit($note->note_content, 150) }}
                        @if(strlen($note->note_content) > 150)...@endif
                    </div>
                    <div class="note-meta">
                        @if($note->page_number)
                            <span><i class="fas fa-file-alt"></i> Halaman {{ $note->page_number }}</span>
                        @endif
                        <span><i class="far fa-clock"></i> {{ $note->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="note-color-bar" style="background: {{ $note->note_color ?? '#FFFF00' }};"></div>
                    <div class="note-actions">
                        <a href="{{ route('reading-notes.edit', $note->id) }}" class="btn-edit" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('reading-notes.destroy', $note->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus catatan ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-delete" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        @if($notes->hasPages())
        <div class="notes-pagination">
            {{ $notes->withQueryString()->links() }}
        </div>
        @endif
    @else
        <div class="empty-state">
            <i class="fas fa-sticky-note" style="font-size: 3rem; color: #ddd; margin-bottom: 20px;"></i>
            <p>Belum ada catatan baca.</p>
            <a href="{{ route('reading-notes.create') }}" class="btn-home">
                <i class="fas fa-plus"></i> Tambah Catatan Pertama
            </a>
        </div>
    @endif
</div>

<style>
.reading-notes-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}
.section-title h3 {
    font-weight: 800;
    color: var(--primary-color);
    margin: 0;
    font-size: 1.5rem;
}
.btn-add {
    background: var(--primary-color);
    color: white;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}
.btn-add:hover {
    background: var(--primary-dark);
    color: white;
    transform: translateY(-2px);
}
.alert-success {
    background: #d4edda;
    color: #155724;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.reading-notes-filter { margin-bottom: 20px; }
.filter-select {
    padding: 10px 16px;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    font-size: 0.95rem;
    min-width: 250px;
}
.notes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}
.note-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
    position: relative;
    overflow: hidden;
}
.note-card:hover {
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}
.note-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 12px; }
.note-kitab {
    font-weight: 700;
    color: var(--primary-color);
    text-decoration: none;
    flex: 1;
}
.note-kitab:hover { text-decoration: underline; }
.note-badge {
    font-size: 0.7rem;
    padding: 3px 8px;
    border-radius: 6px;
    font-weight: 600;
}
.note-badge.private { background: #fef3c7; color: #92400e; }
.note-badge.public { background: #d1fae5; color: #065f46; }
.note-content {
    color: var(--text-color);
    line-height: 1.5;
    margin-bottom: 12px;
    font-size: 0.95rem;
}
.note-meta {
    display: flex;
    gap: 15px;
    font-size: 0.85rem;
    color: var(--light-text);
}
.note-color-bar {
    position: absolute;
    left: 0;
    bottom: 0;
    width: 100%;
    height: 4px;
}
.note-actions {
    display: flex;
    gap: 8px;
    margin-top: 15px;
    padding-top: 12px;
    border-top: 1px solid var(--border-color);
}
.btn-edit, .btn-delete {
    padding: 8px 12px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-edit {
    background: #e0f2fe;
    color: #0369a1;
}
.btn-edit:hover { background: #bae6fd; }
.btn-delete {
    background: #fee2e2;
    color: #dc2626;
}
.btn-delete:hover { background: #fecaca; }
.notes-pagination { margin-top: 25px; }
.empty-state {
    text-align: center;
    padding: 60px 20px;
}
.empty-state p { margin-bottom: 20px; color: var(--light-text); }
.btn-home {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: var(--primary-color);
    color: white;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
}
.btn-home:hover { color: white; background: var(--primary-dark); }
</style>
@endsection
