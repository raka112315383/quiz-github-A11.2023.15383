<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Obat;
use Illuminate\Http\Request;

class ObatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $obats = Obat::latest()->get();
        return view('admin.obat.index', compact('obats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.obat.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_obat' => 'required|string|max:255',
            'kemasan' => 'required|string|max:35',
            'stok' => 'nullable|integer|min:0',
            'harga' => 'required|numeric|min:0',
        ]);

        $validated['stok'] = $validated['stok'] ?? 0;

        Obat::create($validated);

        return redirect()->route('obat.index')
            ->with('success', 'Data obat berhasil ditambahkan')
            ->with('message', 'Data obat berhasil ditambahkan')
            ->with('type', 'success');
    }

    /**
     * Display the specified resource.
     */
    public function show(Obat $obat)
    {
        return redirect()->route('obat.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Obat $obat)
    {
        return view('admin.obat.edit', compact('obat'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Obat $obat)
    {
        $validated = $request->validate([
            'nama_obat' => 'required|string|max:255',
            'kemasan' => 'required|string|max:35',
            'stok' => 'nullable|integer|min:0',
            'harga' => 'required|numeric|min:0',
        ]);

        $validated['stok'] = $validated['stok'] ?? 0;

        $obat->update($validated);

        return redirect()->route('obat.index')
            ->with('success', 'Data obat berhasil diubah')
            ->with('message', 'Data obat berhasil diubah')
            ->with('type', 'success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Obat $obat)
    {
        $obat->delete();

        return redirect()->route('obat.index')
            ->with('success', 'Data obat berhasil dihapus')
            ->with('message', 'Data obat berhasil dihapus')
            ->with('type', 'success');
    }

    /**
     * Export data obat ke format Excel (.xls), termasuk kolom stok dan harga.
     */
    public function exportExcel()
    {
        $obats = Obat::all();
        $headers = ['No', 'Nama Obat', 'Kemasan', 'Stok', 'Harga'];
        $rows = [];

        foreach ($obats as $index => $o) {
            $rows[] = [
                $index + 1,
                $o->nama_obat,
                $o->kemasan ?? '-',
                $o->stok ?? 0,
                'Rp ' . number_format($o->harga, 0, ',', '.'),
            ];
        }

        return \App\Services\ExcelExportService::download('data-obat', 'Data Obat Poliklinik', $headers, $rows);
    }
}
