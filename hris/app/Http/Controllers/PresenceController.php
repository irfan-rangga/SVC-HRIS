<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PresenceController extends Controller
{
  public function store(Request $request)
  {
  	// validation
    $this->validate($request, [
        'face' 						=> 'required|string',
        'temp_abs_emp_id' => 'required',
        'temp_abs_alamat' => 'string|nullable',
        'temp_abs_lat' 		=> 'string|nullable',
        'temp_abs_long' 	=> 'string|nullable',
        'status' 					=> 'string|nullable'
    ]);

  	//Inisialisasi Data Dari Request
    $oid 								= Str::uuid();
    $face 							= $request->input('face', "");
    $temp_abs_emp_id 		= $request->input('temp_abs_emp_id');
    $temp_abs_mesin 		= $request->ip();
    $jam 								= date('H');
    $temp_abs_processed = 'N';
    $temp_abs_mode 			= 1;
    $temp_abs_alamat 		= $request->input('temp_abs_alamat');
    $temp_abs_lat 			= $request->input('temp_abs_lat');
    $temp_abs_long 			= $request->input('temp_abs_long');
    $status 						= $request->input('status');

    // if ($status == 'LH') {
    //     $temp_abs_status = 5;//masuk   
    // } else {
        if ($jam < 12) {
          $temp_abs_status = 0;//masuk   
          $cek_absen = DB::select("SELECT * FROM hris.hr_temp_abs WHERE temp_abs_emp_id = $temp_abs_emp_id AND temp_abs_date = current_date AND temp_abs_status=0");
          if (count($cek_absen) >= 1) {
              return response()->json([
                  'success' => false,
                  'message' => 'Maaf ka, Anda sudah melakukan absen masuk. Terimakasih',
                  'data' 		=> []
              ], 200);
          }
        } else {
          $temp_abs_status = 1;//pulang  
          $cek_absen = DB::select("SELECT * FROM hris.hr_temp_abs WHERE temp_abs_emp_id = $temp_abs_emp_id AND temp_abs_date = current_date AND temp_abs_status=1");
          if (count($cek_absen) >= 1) {
              return response()->json([
                  'success' => false,
                  'message' => 'Maaf ka, Anda sudah melakukan absen pulang. Terimakasih',
                  'data' 		=> []
              ], 200);
          } 
        }
    // }
  	try {
  		DB::beginTransaction();

  		$data_nik = DB::select("SELECT 
              a.emp_nik_old AS nik,
              a.emp_fname AS username1
            FROM
              public.emp_mstr a 
            where a.emp_id='$temp_abs_emp_id' ");
	    //Nama Untuk File Foto
	    $nama_hapus_tik = str_replace(".","", $data_nik[0]->username1);
	    $nama_hapus_koma = str_replace(",","",$nama_hapus_tik);
	    $nama_hapus_kutif = str_replace("'","",$nama_hapus_koma);
	    $nama_foto = $temp_abs_status."-".$nama_hapus_kutif."-";
	    $nama = $nama_foto.$data_nik[0]->nik.'.png';
	    //Decode base64 dari mobile
	    $image = base64_decode($face);
	    //Bulan dan tahun untuk direktori
      $bulan = date("m-Y");
      //Tanggal Untuk direktori Detail
      $tgl = date("d");
      $path_cek = storage_path('') . '/../../upload/presence/' . $bulan;
      //Membuat Direktori
      $direk = direktory($path_cek, $bulan, $tgl);
      //Validasi Pembuatan Direktori
      if ($direk == 1) {
          return response()->json([
              'status' 	=> false,
              'message' => 'Maaf Terjadi Kesalahan Saat pembuatan direktori Bulan-Tahun',
              'data' 		=> []
          ], 200);
      } else if ($direk == 2) {
          return response()->json([
              'status' 	=> false,
              'message' => 'Maaf Terjadi Kesalahan Saat pembuatan direktori Tanggal',
              'data' 		=> []
          ], 200);
      }

      // Path Foto Untuk Memindahkan Gambar
      $pathfoto = $path_cek . "/" . $tgl . "/" . $nama;
      //Url Foto Untuk Disimpan Ke database
      $foto = '/upload/presence/' . $bulan . "/" . $tgl . "/" . $nama;

	    //Query Tambah Data Absensi	    
      $store = DB::insert("INSERT INTO hris.hr_temp_abs (temp_abs_oid, temp_abs_date, temp_abs_emp_id, temp_abs_status, temp_abs_time, temp_abs_processed, temp_abs_mesin, temp_abs_mode, temp_abs_alamat, temp_abs_lat, temp_abs_long, temp_abs_foto) values ('$oid', current_date, $temp_abs_emp_id, $temp_abs_status, current_timestamp, '$temp_abs_processed', '$temp_abs_mesin', $temp_abs_mode, '$temp_abs_alamat', $temp_abs_lat, $temp_abs_long, '$foto')");
       

      if ($store) {
      	//Untuk memindahkan file
      	if (file_put_contents($pathfoto, $image)) {
          $motivasi = DB::select("SELECT * FROM motivasi ORDER BY RANDOM() LIMIT 1");
          $ab_terakihr = DB::select("SELECT * FROM hris.hr_temp_abs WHERE temp_abs_emp_id=$temp_abs_emp_id ORDER BY temp_abs_time DESC LIMIT 1");
          DB::commit();
          return response()->json([
              'success' 	=> true,
              'message' 	=> 'Absensi berhasil dilakukan, Membutuhkan waktu hingga 5 menit',
              'motivasi' 	=> 'Membumikan Al-quran dan Menghidupkan Sirah',//$motivasi[0]->motivasi_text,
              'jam' 			=> $ab_terakihr[0]->temp_abs_time,
              'data' 			=> []
          ], 200);
        } else {
        	DB::rollBack();
	        return response()->json([
	            'success' => false,
	            'message' => 'Absensi gagal dilakukan, Gambar Gagal Di Simpan',
	            'data' 		=> []
	        ], 200);
		    }
      } else {
        return response()->json([
            'success' => false,
            'message' => 'Absensi gagal dilakukan',
            'data' 		=> []
        ], 200);
      }
  	} catch (\Throwable $th) {
      DB::rollBack();
      return response()->json([
          'success' => false,
          'message' => $th->getMessage(),
          'data'    => []
      ], 200);
    }
  }
}
