<?php

namespace App\Http\Controllers;

use App\Models\Informasi;
use App\Support\InformationVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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
            $status = $request->status === 'draft' ? 'draf' : $request->status;
            $query->where('status', $status);
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
            'video_url' => ['nullable', 'string', 'max:2048', 'url', function ($attribute, $value, $fail) {
                if ($value && ! InformationVideo::embedUrl($value)) {
                    $fail('Tautan video harus berasal dari YouTube atau Vimeo.');
                }
            }],
            'video' => 'nullable|file|mimes:mp4,webm|max:51200',
        ]);

        if ($request->hasFile('video') && ! empty($validated['video_url'])) {
            throw ValidationException::withMessages([
                'video' => 'Pilih tautan video atau unggah file video, jangan keduanya.',
            ]);
        }

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('informasi', 'public');
        }

        if ($request->hasFile('video')) {
            $validated['video_path'] = $request->file('video')->store('informasi/videos', 'public');
        }
        unset($validated['video']);

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
            'video_url' => ['nullable', 'string', 'max:2048', 'url', function ($attribute, $value, $fail) {
                if ($value && ! InformationVideo::embedUrl($value)) {
                    $fail('Tautan video harus berasal dari YouTube atau Vimeo.');
                }
            }],
            'video' => 'nullable|file|mimes:mp4,webm|max:51200',
        ]);

        $informasi = Informasi::findOrFail($id);

        if ($request->hasFile('video') && ! empty($validated['video_url'])) {
            throw ValidationException::withMessages([
                'video' => 'Pilih tautan video atau unggah file video, jangan keduanya.',
            ]);
        }

        if ($request->hasFile('gambar')) {
            $this->deleteStoredFile($informasi->gambar);
            $validated['gambar'] = $request->file('gambar')->store('informasi', 'public');
        } elseif ($request->boolean('hapus_gambar')) {
            $this->deleteStoredFile($informasi->gambar);
            $validated['gambar'] = null;
        }

        if ($request->hasFile('video')) {
            $this->deleteStoredFile($informasi->video_path);
            $validated['video_path'] = $request->file('video')->store('informasi/videos', 'public');
            $validated['video_url'] = null;
        } elseif (! empty($validated['video_url'])) {
            $this->deleteStoredFile($informasi->video_path);
            $validated['video_path'] = null;
        } elseif ($request->boolean('hapus_video')) {
            $this->deleteStoredFile($informasi->video_path);
            $validated['video_path'] = null;
            $validated['video_url'] = null;
        }

        unset($validated['video']);
        $informasi->update($validated);

        return redirect()->route('admin.informasi.index')->with('success', 'Informasi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $informasi = Informasi::findOrFail($id);

        $this->deleteStoredFile($informasi->gambar);
        $this->deleteStoredFile($informasi->video_path);

        $informasi->delete();

        return back()->with('success', 'Informasi berhasil dihapus.');
    }

    private function deleteStoredFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}