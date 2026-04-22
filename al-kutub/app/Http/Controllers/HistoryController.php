<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\History;

class HistoryController extends Controller
{
    // Menampilkan riwayat bacaan user
    public function index()
    {
        $histories = History::where('user_id', auth()->id())
            ->with('kitab')
            ->orderByDesc('last_read_at')
            ->get();

        return view('History', compact('histories'));
    }

    // Menghapus semua history user
   public function clear()
    {
        History::where('user_id', auth()->id())->delete();

        if (request()->ajax()) {
            return response()->json(['message' => 'Riwayat bacaan berhasil dihapus!']);
        }

        return redirect()->back()->with('success', 'Riwayat bacaan berhasil dihapus!');
    }

}
