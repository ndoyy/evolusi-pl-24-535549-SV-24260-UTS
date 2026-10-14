<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;

class MahasiswaController extends Controller
{
    public function index()
    {
        // Mengembalikan semua data mahasiswa dalam format JSON (200 OK)
        return response()->json(Mahasiswa::all(), 200);
    }

    public function store(Request $request)
    {
        // Validasi request. Jika gagal, otomatis mengembalikan 422 Unprocessable Entity
        $validated = $request->validate([
            'nim' => 'required|unique:mahasiswas',
            'nama' => 'required',
            'jurusan' => 'required',
        ]);

        $mahasiswa = Mahasiswa::create($validated);
        
        // Mengembalikan data yang baru dibuat (201 Created)
        return response()->json($mahasiswa, 201);
    }
}