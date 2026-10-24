<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BukuController extends Controller
{
    /** GET /api/buku (opsional ?search=...) */
    public function index(Request $request): JsonResponse
    {
        $query = Buku::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhere('penulis', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        return response()->json(['data' => $query->latest('id')->get()], 200);
    }

    /** POST /api/buku */
    public function store(Request $request): JsonResponse
    {
        $buku = Buku::create($this->validated($request));

        return response()->json(['data' => $buku], 201);
    }

    /** GET /api/buku/{buku} */
    public function show(Buku $buku): JsonResponse
    {
        return response()->json(['data' => $buku], 200);
    }

    /** PUT/PATCH /api/buku/{buku} */
    public function update(Request $request, Buku $buku): JsonResponse
    {
        $buku->update($this->validated($request, $buku));

        return response()->json(['data' => $buku->fresh()], 200);
    }

    /** DELETE /api/buku/{buku} */
    public function destroy(Buku $buku): JsonResponse
    {
        $buku->delete();

        return response()->json(['message' => 'Buku berhasil dihapus.'], 200);
    }

    private function validated(Request $request, ?Buku $buku = null): array
    {
        // PATCH boleh mengirim sebagian field saja
        $req = $request->isMethod('patch') ? 'sometimes|required' : 'required';

        return $request->validate([
            'judul' => "$req|string|max:255",
            'penulis' => "$req|string|max:255",
            'penerbit' => 'nullable|string|max:255',
            'tahun_terbit' => "$req|integer|between:1000," . date('Y'),
            'isbn' => [
                $request->isMethod('patch') ? 'sometimes' : 'required',
                'string', 'max:20',
                Rule::unique('bukus', 'isbn')->ignore($buku?->id),
            ],
            'kategori' => 'nullable|string|max:100',
            'stok' => 'nullable|integer|min:0',
            'deskripsi' => 'nullable|string',
        ]);
    }
}