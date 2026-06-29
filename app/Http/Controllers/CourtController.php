<?php

namespace App\Http\Controllers;

use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CourtController extends Controller
{
    // Public: Daftar lapangan aktif untuk halaman utama
    public function index()
    {
        $courts = Court::where('is_active', true)->get();
        return Inertia::render('Courts/Index', [
            'courts' => $courts
        ]);
    }

    // Public: Detail lapangan
    public function show(Court $court)
    {
        return Inertia::render('Courts/Show', [
            'court' => $court
        ]);
    }

    // Admin: Daftar semua lapangan
    public function adminIndex()
    {
        $courts = Court::orderBy('created_at', 'desc')->get();
        return Inertia::render('Admin/Courts/Index', [
            'courts' => $courts,
            'flash'  => session()->only(['success', 'error']),
        ]);
    }

    // Admin: Form tambah lapangan
    public function create()
    {
        return Inertia::render('Admin/Courts/Form', [
            'court' => null,
        ]);
    }

    // Admin: Simpan lapangan baru
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'required|string',
            'price_per_hour'=> 'required|numeric|min:0',
            'facilities'    => 'nullable|array',
            'facilities.*'  => 'string|max:100',
            'is_active'     => 'boolean',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = cloudinary()->upload($request->file('image')->getRealPath(), [
                'folder' => 'volleyzone/courts'
            ])->getSecurePath();
        }

        Court::create([
            'name'           => $request->name,
            'description'    => $request->description,
            'price_per_hour' => $request->price_per_hour,
            'facilities'     => $request->facilities ?? [],
            'is_active'      => $request->boolean('is_active', true),
            'image_path'     => $imagePath,
        ]);

        return redirect()->route('admin.courts.index')
            ->with('success', 'Lapangan berhasil ditambahkan!');
    }

    // Admin: Form edit lapangan
    public function edit(Court $court)
    {
        return Inertia::render('Admin/Courts/Form', [
            'court' => $court,
        ]);
    }

    // Admin: Simpan perubahan lapangan
    public function update(Request $request, Court $court)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'required|string',
            'price_per_hour'=> 'required|numeric|min:0',
            'facilities'    => 'nullable|array',
            'facilities.*'  => 'string|max:100',
            'is_active'     => 'boolean',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = $court->image_path;

        if ($request->hasFile('image')) {
            // Hapus foto lama di Cloudinary jika ada
            if ($court->image_path && str_contains($court->image_path, 'cloudinary')) {
                $parts = explode('/', parse_url($court->image_path, PHP_URL_PATH));
                $filename = end($parts);
                $publicId = 'volleyzone/courts/' . pathinfo($filename, PATHINFO_FILENAME);
                try { cloudinary()->destroy($publicId); } catch (\Exception $e) {}
            }
            $imagePath = cloudinary()->upload($request->file('image')->getRealPath(), [
                'folder' => 'volleyzone/courts'
            ])->getSecurePath();
        }

        $court->update([
            'name'           => $request->name,
            'description'    => $request->description,
            'price_per_hour' => $request->price_per_hour,
            'facilities'     => $request->facilities ?? [],
            'is_active'      => $request->boolean('is_active', true),
            'image_path'     => $imagePath,
        ]);

        return redirect()->route('admin.courts.index')
            ->with('success', 'Lapangan berhasil diperbarui!');
    }

    // Admin: Hapus lapangan
    public function destroy(Court $court)
    {
        if ($court->image_path && str_contains($court->image_path, 'cloudinary')) {
            $parts = explode('/', parse_url($court->image_path, PHP_URL_PATH));
            $filename = end($parts);
            $publicId = 'volleyzone/courts/' . pathinfo($filename, PATHINFO_FILENAME);
            try { cloudinary()->destroy($publicId); } catch (\Exception $e) {}
        }

        $court->delete();

        return redirect()->route('admin.courts.index')
            ->with('success', 'Lapangan berhasil dihapus!');
    }
}
