<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Walas;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use App\Models\PetaKerawanan;
use App\Models\JenisKerawanan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Http\Controllers\Dompdf;
use Dompdf\Dompdf as DompdfDompdf;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf as PdfDompdf;

class PetaKerawananController extends Controller
{

    public function kerawanan_guru_index()
    {
        $guru = Auth::user()->guru;

        // Ambil ID kelas yang diajar oleh guru
        $kelasGuruDiajar = Kelas::where('guru_id', $guru->id)->pluck('id');

        // Ambil ID kelas yang menjadi wali kelas oleh guru
        $kelasGuruWali = Kelas::where('walas_id', $guru->id)->pluck('id');

        // Gabungkan ID kelas yang diajar dan ID kelas yang menjadi wali kelas
        $kelas = $kelasGuruDiajar->merge($kelasGuruWali);

        // Ambil data siswa yang berada di kelas yang diajar atau menjadi wali kelas oleh guru
        $siswa = Siswa::whereIn('kelas_id', $kelas)->get();

        // Ambil data kerawanan yang terkait dengan siswa-siswa di kelas yang diajar atau menjadi wali kelas oleh guru
        $petaKerawanan = PetaKerawanan::whereIn('siswa_id', $siswa->pluck('id'))->get();

        return view('peta_kerawanan.kerawanan_guru', compact('petaKerawanan'));
    }
    public function kerawanan_walas_index()
    {
        $user = Auth::user();
        $walas = $user->walas;
        $peta = PetaKerawanan::where('walas_id', $walas->id)->get();
        // $peta->with('siswa',)
        return view('peta_kerawanan.kerawanan_walas', compact('walas', 'peta'));
    }


    public function kerawanan_guru_create($id)
    {

        $kelas = Kelas::findOrFail($id);
        $wakel = $kelas->walas;
        $jenisKerawanan = [
            'Sering sakit', 'Sering ijin', 'Sering alpha', 'Sering terlambat', 'Bolos', 'Kelainan jasmani',
            'Minat/ motivasi belajar kurang', 'Introvert / pendiam', 'Tinggal dengan wali',
            'Kemampuan kurang', 'Berkelahi', 'Menentang guru', 'Mengganggu teman', 'Pacaran', 'Broken home', 'Kondisi ekonomi kurang ',
            'Pergaulan di luar sekolah', 'Pengguna narkoba', 'Merokok', 'Membiayai sekolah sendiri / bekerja',
        ];


        $siswa = $wakel->kelas->siswa()->whereDoesntHave('petaKerawanan')->get();

        return view('peta_kerawanan.kerawanan_guru_add', compact('siswa', 'jenisKerawanan', 'wakel'));
    }
    public function kerawanan_walas_create()
    {
        $user = Auth::user();
        $walas = $user->walas;
        $jenisKerawanan = [
            'Sering sakit', 'Sering ijin', 'Sering alpha', 'Sering terlambat', 'Bolos', 'Kelainan jasmani',
            'Minat/ motivasi belajar kurang', 'Introvert / pendiam', 'Tinggal dengan wali',
            'Kemampuan kurang', 'Berkelahi', 'Menentang guru', 'Mengganggu teman', 'Pacaran', 'Broken home', 'Kondisi ekonomi kurang ',
            'Pergaulan di luar sekolah', 'Pengguna narkoba', 'Merokok', 'Membiayai sekolah sendiri / bekerja',
        ];
        $siswa = $walas->kelas->siswa()->whereDoesntHave('petaKerawanan')->get();

        return view('peta_kerawanan.kerawanan_walas_add', compact('walas', 'siswa', 'jenisKerawanan'));
    }



    public function kerawanan_walas_store(Request $request)
    {
        // Validasi inputan jika diperlukan
        $jenisKerawanan = implode(',', $request->jenis_kerawanan);
        $petaKerawanan = PetaKerawanan::create([
            'siswa_id' => $request->siswa_id,
            'walas_id' => $request->walas_id,
            'jenis_kerawanan' => $jenisKerawanan,
            'kesimpulan' => $request->kesimpulan
        ]);
        LogActivity::create([
            'activity' => auth()->user()->name. ' telah menambah data kerawanan'
        ]);
        return redirect('/walas/kerawanan/index')->with('success', 'Data peta kerawanan berhasil disimpan.');
    }
    public function kerawanan_guru_index_kelas()
    {
        $guru = Auth::user()->guru;
        $kelas = $guru->kelass;
        return view('peta_kerawanan.kelas', compact('kelas'));
    }

    public function guruKelas()
    {
        $guru = Auth::user()->guru;
        $kelas = $guru->kelass;
        return view('peta_kerawanan.kelasguru', compact('kelas'));
    }

    public function kerawanan_guru_store(Request $request)
    {
        $jenisKerawanan = implode(',', $request->jenis_kerawanan);
        $petaKerawanan = PetaKerawanan::create([
            'siswa_id' => $request->siswa_id,
            'walas_id' => $request->walas_id,
            'jenis_kerawanan' => $jenisKerawanan,
            'kesimpulan' => $request->kesimpulan
        ]);
        LogActivity::create([
            'activity' => auth()->user()->name. ' telah menambahkan data kerawanan'.$petaKerawanan->activity
        ]);
        return redirect('kerawanan-indexs')->with('success', 'Data peta kerawanan berhasil disimpan.');
    }

    public function kerawanan_guru_edit($id)
    {
        $wakel = Auth::user()->guru;
        $petaKerawanan = PetaKerawanan::findOrFail($id);
        $siswa = Siswa::where('kelas_id', $petaKerawanan->siswa->kelas_id)->get();
        $jenisKerawanan = [
            'Sering sakit', 'Sering ijin', 'Sering alpha', 'Sering terlambat', 'Bolos', 'Kelainan jasmani',
            'Minat/ motivasi belajar kurang', 'Introvert / pendiam', 'Tinggal dengan wali',
            'Kemampuan kurang', 'Berkelahi', 'Menentang guru', 'Mengganggu teman', 'Pacaran', 'Broken home', 'Kondisi ekonomi kurang ',
            'Pergaulan di luar sekolah', 'Pengguna narkoba', 'Merokok', 'Membiayai sekolah sendiri / bekerja',
        ];
        return view('peta_kerawanan.edit_guru', compact('petaKerawanan', 'siswa', 'wakel', 'jenisKerawanan'));
    }
    public function kerawanan_walas_edit($id)
    {
        $user = Auth::user();
        $walas = $user->walas;
        $petaKerawanan = PetaKerawanan::findOrFail($id);
        $siswa = $walas->kelas->siswa;
        $jenisKerawanan = [
            'Sering sakit', 'Sering ijin', 'Sering alpha', 'Sering terlambat', 'Bolos', 'Kelainan jasmani',
            'Minat/ motivasi belajar kurang', 'Introvert / pendiam', 'Tinggal dengan wali',
            'Kemampuan kurang', 'Berkelahi', 'Menentang guru', 'Mengganggu teman', 'Pacaran', 'Broken home', 'Kondisi ekonomi kurang ',
            'Pergaulan di luar sekolah', 'Pengguna narkoba', 'Merokok', 'Membiayai sekolah sendiri / bekerja',
        ];
       
        return view('peta_kerawanan.edit_walas', compact('petaKerawanan', 'siswa', 'walas', 'jenisKerawanan'));
    }

    public function kerawanan_guru_update(Request $request, $id)
    {
        $jenisKerawanan = implode(',', $request->jenis_kerawanan);
        $petaKerawanan = PetaKerawanan::findOrFail($id);
        $petaKerawanan->siswa_id = $request->siswa_id;
        $petaKerawanan->kesimpulan = $request->kesimpulan;
        $petaKerawanan->jenis_kerawanan = $jenisKerawanan;
        $petaKerawanan->save();
        LogActivity::create([
            'activity' => auth()->user()->name. ' telah merubah data kerawanan'
        ]);
        return redirect('/guru/kerawanan/index')->with('success', 'Data peta kerawanan berhasil diperbarui.');
    }
    public function kerawanan_walas_update(Request $request, $id)
    {
        $jenisKerawanan = implode(',', $request->jenis_kerawanan);
        $petaKerawanan = PetaKerawanan::findOrFail($id);
        $petaKerawanan->siswa_id = $request->siswa_id;
        $petaKerawanan->kesimpulan = $request->kesimpulan;
        $petaKerawanan->jenis_kerawanan = $jenisKerawanan;
        $petaKerawanan->save();
        LogActivity::create([
            'activity' => auth()->user()->name. ' telah merubah data kerawanan'
        ]);
        return redirect('/walas/kerawanan/index')->with('success', 'Data peta kerawanan berhasil diperbarui.');
    }


    public function kerawanan_delete_guru($id)
    {
        $petaKerawanan = PetaKerawanan::findOrFail($id);
        Schema::disableForeignKeyConstraints();
        $petaKerawanan->delete();
        Schema::enableForeignKeyConstraints();
        LogActivity::create([
            'activity' => auth()->user()->name. ' telah mengapus data kerawanan'.$petaKerawanan->nama
        ]);
        return redirect('/guru/kerawanan/index')->with('success', 'Data peta kerawanan berhasil dihapus.');
    }
    public function kerawanan_delete_walas($id)
    {
        $petaKerawanan = PetaKerawanan::findOrFail($id);
        Schema::disableForeignKeyConstraints();
        $petaKerawanan->delete();
        Schema::enableForeignKeyConstraints();
        LogActivity::create([
            'activity' => auth()->user()->name. ' telah mengapus data kerawanan'.$petaKerawanan->nama
        ]);
        return redirect('/walas/kerawanan/index')->with('success', 'Data peta kerawanan berhasil dihapus.');
    }

    public function kerawananSiswa(Request $request)
    {
        $walasId = Auth::user()->walas->id;

        $kelas = Kelas::with('siswa')
            ->where('walas_id', $walasId)
            ->first();

        $siswa = $kelas->siswa;

        return view('peta_kerawanan.datasiswa', compact('siswa', 'walasId'));
    }

    public function gurusiswaIndex($id)
    {

        $kelas = Kelas::findOrFail($id);

        $siswa = $kelas->siswa;

        return view('peta_kerawanan.datasiswa_guru', compact('siswa', 'kelas'));
    }

    //PDF

    public function pdfWalas($id)
    {
        $siswa = Siswa::find($id);

        if (!$siswa) {
            return redirect()->back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $html = View::make('peta_kerawanan.surat_walas', compact('siswa'))->render();

        $dompdf = new DompdfDompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();

        return Response::make($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="pemanggilanwalas.pdf"',
        ]);

        return redirect()->route('/walas/siswa/kerawanan')->with('success', 'Surat pemanggilan telah diunduh.');
    }

    public function pdfGuru($id)
    {
        $siswa = Siswa::find($id);

        if (!$siswa) {
            return redirect()->back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $html = View::make('peta_kerawanan.surat_guru', compact('siswa'))->render();

        $dompdf = new DompdfDompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();

        return Response::make($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="pemanggilanGuru.pdf"',
        ]);

        return redirect()->route('/walas/siswa/kerawanan')->with('success', 'Surat pemanggilan telah diunduh.');
    }

    //SEARCH

    public function searchSiswa(Request $request)
    {
        $keyword = $request->input('keyword');

        $siswa = Siswa::where('nama', 'LIKE', "%$keyword%")
            ->orWhere('nisn', 'LIKE', "%$keyword%")
            ->orWhere('jenis_kelamin', 'LIKE', "%$keyword%")
            ->orWhere('ttl', 'LIKE', "%$keyword%")
            ->orWhereHas('kelas', function ($query) use ($keyword) {
                $query->where('nama', 'LIKE', "%$keyword%");
            })
            ->paginate(10);

        return view('peta_kerawanan.datasiswa_guru', compact('siswa'));
    }

    public function searchKerawanan(Request $request)
    {
        $keyword = $request->input('keyword');

        $petaKerawanan = PetaKerawanan::WhereHas('siswa', function ($query) use ($keyword) {
                $query->where('nama', 'LIKE', "%$keyword%");
            })
            ->orWhereHas('walas', function ($query) use ($keyword) {
                $query->where('nama', 'LIKE', "%$keyword%");
            })
            ->paginate(10);

        return view('peta_kerawanan.kerawanan_guru', compact('petaKerawanan'));
    }

    public function searchSiswaGuru(Request $request)
    {
        $keyword = $request->input('query');

        $walas = Auth::user()->walas;
        $kelas = $walas->kelas;

        $siswa = Siswa::where('kelas_id', $kelas->id)
            ->where(function ($query) use ($keyword) {
                $query->where('nama', 'like', '%' . $keyword . '%')
                    ->orWhere('nisn', 'like', '%' . $keyword . '%')
                    ->orWhere('jenis_kelamin', 'like', '%' . $keyword . '%')
                    ->orWhere('ttl', 'like', '%' . $keyword . '%');
            })
            ->get();


        return view('peta_kerawanan.datasiswa', compact('siswa'));
    }

    public function searchSiswaKerawanan(Request $request)
    {
        $keyword = $request->input('query');

        $walas = Auth::user()->walas;

        $siswaIds = [];
        foreach ($walas->kelass as $kelas) {
            foreach ($kelas->siswa as $siswa) {
                $siswaIds[] = $siswa->id;
            }
        }

        $peta = PetaKerawanan::whereIn('siswa_id', $siswaIds)
                    ->whereHas('siswa', function ($query) use ($keyword) {
                        $query->where('nama', 'like', '%' . $keyword . '%');
                    })
                    ->orwhere('jenis_kerawanan', 'like', '%' . $keyword . '%')
                    ->with('siswa')
                    ->get();



        return view('peta_kerawanan.kerawanan_walas', compact('peta'));
    }
}
