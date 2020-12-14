<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PieceController extends Controller
{
  public function late(Request $request, $id)
  {
  	$tgl_mulai = date("d/m/Y",strtotime($request->input('start_date', "01/".date('m/Y'))));
    $tgl_akhir = date("d/m/Y",strtotime($request->input('end_date', date('d/m/Y'))));
    $page_limit   = $request->input('page_limit', 25);

    $pieces = DB::table("hris.hrgaji_mstr")
    							->join("hris.hrgajid_det", "hris.hrgaji_mstr.hrgaji_code", "=", "hris.hrgajid_det.hrgajid_hrgaji_code")
    							->join("hris.hrperiode_mstr", "hris.hrgaji_mstr.hrgaji_periode", "=", "hris.hrperiode_mstr.hrperiode_code")
    							->select(
    								"hris.hrgaji_mstr.hrgaji_periode",
			              "hris.hrgajid_det.hrgajid_emp_id",
			              "hris.hrgajid_det.hrgajid_emp_nik_old",
			              "hris.hrgajid_det.hrgajid_emp_fname",
			              "hris.hrperiode_mstr.hrperiode_end_date", 
			              DB::Raw("to_char(hris.hrgajid_det.hrgajid_pot_terlambat_akumulasi_periode,'FM999,999,999') as hrgajid_pot_terlambat_akumulasi_periode"),
			              DB::Raw("to_char(hris.hrgajid_det.hrgajid_pot_terlambat_poin ,'FM999,999,999') as hrgajid_pot_terlambat_poin,
                  hris.hrgajid_det.hrgajid_pot_terlambat_pengali as hrgajid_pot_terlambat_pengali"),
			              DB::Raw("to_char(to_number(encode(decrypt( hris.hrgajid_det.hrgajid_pot_terlambat, decode('11011101','escape'),'aes'),'escape'),'999999999999999999D9999999S'),'FM999,999,999') as hrgajid_pot_terlambat")
    							)
    							->where("hris.hrgajid_det.hrgajid_emp_id" , $id)
    							->whereBetween("hris.hrperiode_mstr.hrperiode_end_date", [$tgl_mulai, $tgl_akhir])
    							->orderBy("hrgajid_emp_fname","asc")
    							->orderBy("hrgaji_periode","asc")
          				->paginate($page_limit);
    							// ->get();
    if (count($pieces) > 0) {
        return response()->json([
            'success' => true,
            'message' => 'Potongan berhasil ditampilkan',
            'data' => $pieces
        ], 200);
    }else{
        return response()->json([
            'success' => false,
            'message' => 'Data Potongan Kosong',
            'data' => []
        ], 200);
    }
  }

  public function permission(Request $request, $id)
  {
  	$tgl_mulai = date("d/m/Y",strtotime($request->input('start_date', "01/".date('m/Y'))));
    $tgl_akhir = date("d/m/Y",strtotime($request->input('end_date', date('d/m/Y'))));
    $page_limit   = $request->input('page_limit', 25);

    $pieces = DB::table("hris.hrgaji_mstr")
    							->join("hris.hrgajid_det", "hris.hrgaji_mstr.hrgaji_code", "=", "hris.hrgajid_det.hrgajid_hrgaji_code")
    							->join("hris.hrperiode_mstr", "hris.hrgaji_mstr.hrgaji_periode", "=", "hris.hrperiode_mstr.hrperiode_code")
    							->select(
    								"hris.hrgaji_mstr.hrgaji_periode",
			              "hris.hrgajid_det.hrgajid_emp_id",
			              "hris.hrgajid_det.hrgajid_emp_nik_old",
			              "hris.hrgajid_det.hrgajid_emp_fname",
			              "hris.hrperiode_mstr.hrperiode_end_date", 
			              DB::Raw("to_char(to_number(encode(decrypt( hris.hrgajid_det.hrgajid_pot_trans_makan, decode('11011101','escape'),'aes'),'escape'),'999999999999999999D9999999S'),'FM999,999,999') as hrgajid_pot_trans_makan"),
			              DB::Raw("to_char(hris.hrgajid_det.hrgajid_izin,'FM999,999,999') as hrgajid_izin"),
			              DB::Raw("to_char(hris.hrgajid_det.hrgajid_sakit_t_ket_dokter,'FM999,999,999') as hrgajid_sakit_t_ket_dokter"),
			              DB::Raw("to_char(hris.hrgajid_det.hrgajid_alpa ,'FM999,999,999') as hrgajid_alpa")
    							)
    							->where("hris.hrgajid_det.hrgajid_emp_id" , $id)
    							->whereBetween("hris.hrperiode_mstr.hrperiode_end_date", [$tgl_mulai, $tgl_akhir])
    							->orderBy("hrgajid_emp_fname","asc")
    							->orderBy("hrgaji_periode","asc")
          				->paginate($page_limit);

    if (count($pieces) > 0) {
        return response()->json([
            'success' => true,
            'message' => 'Potongan berhasil ditampilkan',
            'data' => $pieces
        ], 200);
    }else{
        return response()->json([
            'success' => false,
            'message' => 'Data Potongan Kosong',
            'data' => []
        ], 200);
    }
  }

  public function purchase(Request $request, $id)
  {
  	// validation
    $this->validate($request, [
        'start_date'		=> 'required',
        'end_date' 	=> 'required'
    ]);

  	$tgl_mulai = date("d/m/Y",strtotime($request->input('start_date', "01/".date('m/Y'))));
    $tgl_akhir = date("d/m/Y",strtotime($request->input('end_date', date('d/m/Y'))));
    $page_limit   = $request->input('page_limit', 25);

    $pieces = DB::table("hris.hrpembuku_mstr")
    							->join("hris.hrpembukud_det", "hris.hrpembuku_mstr.hrpembuku_code", "=", "hris.hrpembukud_det.hrpembukud_hrpembuku_code")
    							->join("hris.hrperiode_mstr", "hris.hrpembukud_det.hrpembukud_periode", "=", "hris.hrperiode_mstr.hrperiode_code")
    							->select(
    								"hris.hrpembukud_det.hrpembukud_periode",
			              DB::Raw("round(hris.hrpembukud_det.hrpembukud_jumlah) as hrpembukud_jumlah")
    							)
    							->where("hris.hrpembuku_mstr.hrpembuku_emp_id" , $id)
    							->whereBetween("hris.hrperiode_mstr.hrperiode_end_date", [$tgl_mulai, $tgl_akhir])
    							->orderBy("hris.hrpembukud_det.hrpembukud_periode","asc")
          				->paginate($page_limit);
    							// ->get();
    if (count($pieces) > 0) {
        return response()->json([
            'success' => true,
            'message' => 'Potongan berhasil ditampilkan',
            'data' => $pieces
        ], 200);
    }else{
        return response()->json([
            'success' => false,
            'message' => 'Data Potongan Kosong',
            'data' => []
        ], 200);
    }
  }

  public function bpjs(Request $request, $id)
  {
  	// validation
    $this->validate($request, [
        'start_date'		=> 'required',
        'end_date' 	=> 'required'
    ]);

  	$tgl_mulai = date("d/m/Y",strtotime($request->input('start_date', "01/".date('m/Y'))));
    $tgl_akhir = date("d/m/Y",strtotime($request->input('end_date', date('d/m/Y'))));
    $page_limit   = $request->input('page_limit', 25);

    $pieces = DB::table("hris.hrpembuku_mstr")
    							->join("hris.hrpembukud_det", "hris.hrpembuku_mstr.hrpembuku_code", "=", "hris.hrpembukud_det.hrpembukud_hrpembuku_code")
    							->join("hris.hrperiode_mstr", "hris.hrpembukud_det.hrpembukud_periode", "=", "hris.hrperiode_mstr.hrperiode_code")
    							->select(
    								"hris.hrpembukud_det.hrpembukud_periode",
			              DB::Raw("round(hris.hrpembukud_det.hrpembukud_jumlah) as hrpembukud_jumlah")
    							)
    							->where("hris.hrpembuku_mstr.hrpembuku_emp_id" , $id)
    							->whereBetween("hris.hrperiode_mstr.hrperiode_end_date", [$tgl_mulai, $tgl_akhir])
    							->orderBy("hris.hrpembukud_det.hrpembukud_periode","asc")
          				->paginate($page_limit);
    							// ->get();
    if (count($pieces) > 0) {
        return response()->json([
            'success' => true,
            'message' => 'Potongan berhasil ditampilkan',
            'data' => $pieces
        ], 200);
    }else{
        return response()->json([
            'success' => false,
            'message' => 'Data Potongan Kosong',
            'data' => []
        ], 200);
    }
  }
}
