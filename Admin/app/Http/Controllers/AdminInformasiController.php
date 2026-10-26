<?php

namespace App\Http\Controllers;

use App\Models\Informasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminInformasiController extends Controller
{
    public function index(Request $request)
    {
        $query = Informasi::query();

        // Filter berdasarkan pencarian judul
        if ($request->filled('search')) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Gunakan paginate(10) agar method ->total() dan links() berfungsi
        $informasis = $query->latest()->paginate(10)->withQueryString();

        // Mengarah ke resources/views/informasi/index.blade.php
        return view('informasi.index', compact('informasis'));
    }

    public function create()
    {
        // Mengarah ke resources/views/informasi/create.blade.php
        return view('informasi.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'status' => 'required|in:draf,published',
            'tanggal_publish' => 'required|date',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:5120', // Validasi foto maksimal 5MB
        ]);

        // Tangani upload gambar jika ada
        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('informasi', 'public');
        }

        Informasi::create($validated);

        return redirect()->route('admin.informasi.index')->with('success', 'Informasi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $informasi = Informasi::findOrFail($id);
        
        // Mengarah ke resources/views/informasi/edit.blade.php
        return view('informasi.edit', compact('informasi'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'status' => 'required|in:draf,published',
            'tanggal_publish' => 'required|date',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $informasi = Informasi::findOrFail($id);

        // Tangani jika ada upload gambar baru
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada di storage
            if ($informasi->gambar && Storage::disk('public')->exists($informasi->gambar)) {
                Storage::disk('public')->delete($informasi->gambar);
            }
            // Simpan gambar baru
            $validated['gambar'] = $request->file('gambar')->store('informasi', 'public');
        }

        $informasi->update($validated);

        return redirect()->route('admin.informasi.index')->with('success', 'Informasi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $informasi = Informasi::findOrFail($id);

        // Hapus file gambar dari storage jika ada saat data dihapus
        if ($informasi->gambar && Storage::disk('public')->exists($informasi->gambar)) {
            Storage::disk('public')->delete($informasi->gambar);
        }

        $informasi->delete();

        return back()->with('success', 'Informasi berhasil dihapus.');
    }
}