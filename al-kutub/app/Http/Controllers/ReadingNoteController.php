<?php

namespace App\Http\Controllers;

use App\Models\ReadingNote;
use App\Models\Kitab;
use Illuminate\Http\Request;

class ReadingNoteController extends Controller
{
    public function index(Request $request)
    {
        $query = ReadingNote::forUser(auth()->id())
            ->with('kitab:id_kitab,judul,cover,kategori')
            ->orderBy('created_at', 'desc');

        if ($request->filled('kitab_id')) {
            $query->forKitab($request->kitab_id);
        }

        $notes = $query->paginate(15);
        $kitabs = Kitab::orderBy('judul')->get(['id_kitab', 'judul']);

        return view('reading-notes.index', compact('notes', 'kitabs'));
    }

    public function create()
    {
        $kitabs = Kitab::orderBy('judul')->get(['id_kitab', 'judul']);
        return view('reading-notes.form', compact('kitabs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kitab_id' => 'required|exists:kitab,id_kitab',
            'note_content' => 'required|string|max:2000',
            'page_number' => 'nullable|integer|min:1',
            'highlighted_text' => 'nullable|string|max:500',
            'note_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_private' => 'nullable|boolean',
        ]);

        ReadingNote::create([
            'user_id' => auth()->id(),
            'kitab_id' => $validated['kitab_id'],
            'note_content' => $validated['note_content'],
            'page_number' => $validated['page_number'] ?? null,
            'highlighted_text' => $validated['highlighted_text'] ?? null,
            'note_color' => $validated['note_color'] ?? '#FFFF00',
            'is_private' => $request->boolean('is_private'),
        ]);

        return redirect()->route('reading-notes.index')
            ->with('success', 'Catatan berhasil disimpan.');
    }

    public function edit($id)
    {
        $note = ReadingNote::forUser(auth()->id())->findOrFail($id);
        $kitabs = Kitab::orderBy('judul')->get(['id_kitab', 'judul']);
        return view('reading-notes.form', compact('note', 'kitabs'));
    }

    public function update(Request $request, $id)
    {
        $note = ReadingNote::forUser(auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'note_content' => 'required|string|max:2000',
            'page_number' => 'nullable|integer|min:1',
            'highlighted_text' => 'nullable|string|max:500',
            'note_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_private' => 'nullable|boolean',
        ]);

        $note->update([
            'note_content' => $validated['note_content'],
            'page_number' => $validated['page_number'] ?? null,
            'highlighted_text' => $validated['highlighted_text'] ?? null,
            'note_color' => $validated['note_color'] ?? $note->note_color,
            'is_private' => $request->boolean('is_private'),
        ]);

        return redirect()->route('reading-notes.index')
            ->with('success', 'Catatan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $note = ReadingNote::forUser(auth()->id())->findOrFail($id);
        $note->delete();

        return redirect()->route('reading-notes.index')
            ->with('success', 'Catatan berhasil dihapus.');
    }
}
