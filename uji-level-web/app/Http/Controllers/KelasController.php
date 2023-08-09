<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Walas;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class KelasController extends Controller
{
    public function indexKelas()
    {
        $kelas = Kelas::with('guru', 'walas', 'siswa')->paginate(10);
    
        return view('layouts.kelas.index', compact('kelas'));
    }
    public function createKelas()
    {
        // Mengambil semua data walas yang belum memiliki kelas
        $walas = Walas::whereDoesntHave('kelas')->get();
        $gurus = Guru::all();
        // Mengirim data walas dan guru ke view
        return view('layouts.kelas.create', compact('walas', 'gurus'));
    }

public function storeKelas(Request $request)
{
    $request->validate([
        'nama' => 'required',
        'guru_id' => 'required',
        'walas_id' => 'required',
    ]);

   $kelas = Kelas::create([
        'nama' => $request->nama,
        'guru_id' => $request->guru_id,
        'walas_id' => $request->walas_id,
    ]);

    LogActivity::create([
        'activity' => auth()->user()->name. ' telah menambahkan kelas baru dengan nama kelas '.$kelas->nama
    ]);
    return redirect('index-kelas')->with('success', 'Kelas berhasil ditambahkan.');
}

public function update(Request $request, $id)
{
    $request->validate([
        'nama' => 'required',
        'guru_id' => 'required',
        'walas_id' => 'required',
    ]);

    $kelas = Kelas::findOrFail($id);

    $kelas->nama = $request->nama;
    $kelas->guru_id = $request->guru_id;
    $kelas->walas_id = $request->walas_id;
    $kelas->save();
    LogActivity::create([
        'activity' => auth()->user()->name. ' telah mengupdate data kelas dengan nama kelas '.$kelas->nama
    ]);
    return redirect()->route('kelas.index')->with('success', 'Kelas berhasil diperbarui.');
}

public function edit($id)
{
    $kelas = Kelas::findOrFail($id);
    $gurus = Guru::all();
    $walas = Walas::whereDoesntHave('kelas')->get();

    return view('layouts.kelas.update', compact('kelas', 'gurus', 'walas'));
}

public function destroy($id)
{

    $kelas = Kelas::findOrFail($id);
  
    Schema::disableForeignKeyConstraints();
    $kelas->delete();
    Schema::enableForeignKeyConstraints();
    LogActivity::create([
        'activity' => auth()->user()->name. ' telah menghapus kelas dengan nama kelas '.$kelas->nama
    ]);
    return redirect()->route('kelas.index')->with('success', 'Kelas berhasil dihapus.');
}

}
