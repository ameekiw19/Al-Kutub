@extends('TemplateUser')

@section('konten')
<div class="form-header">
    <h3><i class="fas fa-sticky-note me-2"></i> {{ isset($note) ? 'Edit Catatan' : 'Tambah Catatan Baca' }}</h3>
    <a href="{{ route('reading-notes.index') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="form-container">
    <form action="{{ isset($note) ? route('reading-notes.update', $note->id) : route('reading-notes.store') }}" method="POST">
        @csrf
        @if(isset($note)) @method('PUT') @endif

        @if(!isset($note))
        <div class="form-group">
            <label for="kitab_id">Kitab</label>
            <select name="kitab_id" id="kitab_id" class="form-input" required>
                <option value="">Pilih Kitab</option>
                @foreach($kitabs as $k)
                    <option value="{{ $k->id_kitab }}" {{ old('kitab_id') == $k->id_kitab ? 'selected' : '' }}>
                        {{ $k->judul }}
                    </option>
                @endforeach
            </select>
            @error('kitab_id')<span class="error">{{ $message }}</span>@enderror
        </div>
        @else
        <div class="form-group">
            <label>Kitab</label>
            <p class="form-static">{{ $note->kitab->judul ?? 'N/A' }}</p>
        </div>
        @endif

        <div class="form-group">
            <label for="note_content">Isi Catatan</label>
            <textarea name="note_content" id="note_content" class="form-input" rows="5" required placeholder="Tulis catatan Anda...">{{ old('note_content', $note->note_content ?? '') }}</textarea>
            @error('note_content')<span class="error">{{ $message }}</span>@enderror
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="page_number">Halaman (opsional)</label>
                <input type="number" name="page_number" id="page_number" class="form-input" min="1" value="{{ old('page_number', $note->page_number ?? '') }}" placeholder="Contoh: 42">
                @error('page_number')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="note_color">Warna Catatan</label>
                <div class="color-input-wrap">
                    <input type="color" name="note_color" id="note_color" value="{{ old('note_color', $note->note_color ?? '#FFFF00') }}" class="color-picker">
                    <input type="text" value="{{ old('note_color', $note->note_color ?? '#FFFF00') }}" class="form-input color-text" readonly id="colorDisplay">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="is_private" value="1" {{ old('is_private', $note->is_private ?? true) ? 'checked' : '' }}>
                Catatan pribadi (hanya saya yang bisa melihat)
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> {{ isset($note) ? 'Simpan Perubahan' : 'Simpan Catatan' }}
            </button>
            <a href="{{ route('reading-notes.index') }}" class="btn-cancel">Batal</a>
        </div>
    </form>
</div>

<script>
document.getElementById('note_color')?.addEventListener('input', function() {
    document.getElementById('colorDisplay').value = this.value;
});
</script>

<style>
.form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}
.form-header h3 { font-weight: 800; color: var(--primary-color); margin: 0; }
.btn-back {
    color: var(--primary-color);
    font-weight: 600;
    text-decoration: none;
}
.btn-back:hover { text-decoration: underline; }
.form-container {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 30px;
    max-width: 600px;
}
.form-group { margin-bottom: 20px; }
.form-group label { display: block; font-weight: 600; margin-bottom: 8px; }
.form-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    font-size: 1rem;
}
.form-input:focus {
    outline: none;
    border-color: var(--primary-color);
}
.form-static { padding: 10px; background: #f1f5f9; border-radius: 8px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.color-input-wrap { display: flex; gap: 10px; align-items: center; }
.color-picker { width: 50px; height: 40px; border: none; cursor: pointer; border-radius: 6px; }
.color-text { width: 100px !important; }
.checkbox-label { display: flex; align-items: center; gap: 10px; cursor: pointer; }
.checkbox-label input { width: 18px; height: 18px; }
.error { color: #dc2626; font-size: 0.9rem; display: block; margin-top: 5px; }
.form-actions { display: flex; gap: 15px; margin-top: 25px; }
.btn-submit {
    background: var(--primary-color);
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-submit:hover { background: var(--primary-dark); }
.btn-cancel {
    padding: 12px 24px;
    color: var(--light-text);
    text-decoration: none;
}
.btn-cancel:hover { color: var(--text-color); }
</style>
@endsection
