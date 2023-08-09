<?php

namespace App\Http\Controllers;

use App\Models\KonselingBK;
use App\Models\PivotBK;
use App\Models\User;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpParser\Node\Expr\Cast\String_;

class AuthController extends Controller
{
    function login(Request $R){
        $user= User::where('email', $R->email)->first();

        if($user!='[]' && Hash::check($R->password,$user->password)){
            $token = $user->createToken('secret')->plainTextToken;
            $response= ['status'=>200,'token'=>$token,'user'=>$user,'message'=>'Successfully Login'];
            return response()->json($response); 
        }else if($user=='[]'){
            $response= ['status'=>500,'user'=>$user,'message'=>'Not Found Account found with this Email'];
            return response()->json($response); 
        }else{
            $response= ['status'=>500,'user'=>$user,'message'=>'Wrong Email Or Password, Try Again!'];
            return response()->json($response); 
        }
    }


    public function index(string $id)
    {    

        $userid = $id;
        
        $siswa = Siswa::where('user_id', $userid)->first();
    
        if (!$siswa) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        try {
            $pivot = DB::table('pivot_bk')->where('siswa_id', $siswa->id)->get();
        } catch (\Throwable $th) {
            throw $th;
        }

        $konselingbk = [];


        foreach($pivot as $item){
            array_push($konselingbk, KonselingBK::find($item->konseling_id));
        }
    
        $array = [];
    
        foreach ($konselingbk as $item) {
            array_push($array, [
                'nama_guru' => $item->guru->nama,
                'judul' => $item->judul,
                'status' => $item->status,
                'jadwal_konseling' => $item->jadwal_konseling
            ]);
        }
    
        return response()->json([
            'data' => $array,
        ]);
    }


    public function logout(){
      
        auth()->user()->tokens()->delete();   
        return response([
            'message' => 'Logout Success'
        ],200);
    }


    public function indexlayanan()
    {
        $user = auth()->user();

        if ($user->hasRole('siswa')) {
            $siswa = Siswa::where('user_id', $user->id)->first();
            $dataLayanan = PivotBK::where('siswa_id', $siswa->id)->with('konseling.layanan', 'konseling.guru', 'konseling.walas')->get();

            $layananList = [];
            foreach ($dataLayanan as $pivot) {
                $konseling = $pivot->konseling;
                $layanan = $konseling->layanan;
                $guru = $konseling->guru;
                $walas = $konseling->walas;
                $jadwal = $konseling->jadwal_konseling; // Update this line
                $status = $konseling->status; // Update this line

                $layananList[] = [
                    'id' => $konseling->id,
                    'guru' => $guru->nama,
                    'walas' => $walas->nama,
                    'judul' => $konseling->judul,
                    'alasan' => $konseling->alasan_kesimpulan,
                    'jenis_layanan' => $layanan->jenis_layanan,
                    'jadwal' => $jadwal,
                    'status' => $status,
                ];
            }

            return response()->json([
                'status' => 200,
                'layanan' => $layananList,
                'message' => 'Successfully retrieved data',
            ]);
        }

        return response()->json([
            'status' => 403,
            'message' => 'Unauthorized',
        ], 403);
    }

}
