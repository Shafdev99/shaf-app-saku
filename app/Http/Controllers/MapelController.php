<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KelompokMapel;
use App\Models\KurikulumMapel;
use App\Models\Mapel;

class MapelController extends Controller
{
    /** 
     * Mapel 
     */
    //======== Index Mapel ========//
    public function mapel()
    {
        return view('master.mapel', [
            'mapel' => Mapel::select('id', 'mapel', 'kkm')->get(),
            'kelma' => KelompokMapel::select('id', 'kelompok_mapel')->get()
        ]);
    }

    //======== Add Data Mapel ========//
    public function addMapel()
    {
        $data = request()->validate([
            'mapel'  => 'required|unique:mapel|min:1|max:150',
            'kkm'    => 'required|min:2|max:3'
        ], [
            'required'       => ':attribute wajib diisi!',
            'mapel.max'      => 'Data mapel diisi maksimal :max karakter!',
            'mapel.min'      => 'Data mapel diisi minimal :min karakter!',
            'kkm.min'        => ':attribute disini minimal :min karakter!',
            'kkm.max'        => ':attribute disini maksimal :max karakter!',
            'mapel.unique'   => 'Nama mapel tersebut sudah digunakan!'
        ]);

        Mapel::create($data);

        // Alihkan ke halaman mapel
        return redirect()->route('mapel')->with('sukses', 'Data Mapel berhasil ditambahkan!');
    }

    //======== Update Data mapel ========//
    public function editMapel()
    {
        $data = request()->validate([
            'mapel'  => 'required|unique:mapel,mapel,' . request()->mapel_id . '|min:1|max:150',
            'kkm'    => 'required|min:2|max:3'
        ], [
            'required'       => ':attribute wajib diisi!',
            '.mapel.max'     => 'Data mapel diisi maksimal :max karakter!',
            'mapel.min'      => 'Data mapel diisi minimal :min karakter!',
            'kkm.min'        => ':attribute disini minimal :min karakter!',
            'kkm.max'        => ':attribute disini maksimal :max karakter!',
            'mapel.unique'   => 'Nama mapel tersebut sudah digunakan!'
        ]);

        mapel::where('id', request()->mapel_id)->update($data);

        // Alihkan ke halaman mapel
        return redirect()->route('mapel')->with('sukses', 'Data mapel berhasil diperbarui!');
    }

    public function editMapelKkm()
    {
        for ($i = 0; $i < count(request()->mapel_id); $i++) {
            Mapel::where('id', request()->mapel_id[$i])->update([
                'kkm' => request()->kkm[$i]
            ]);
        }

        // Alihkan ke halaman mapel
        return redirect()->route('mapel')->with('sukses', 'Data kkm mapel berhasil diperbarui!');
    }

    public function getMapelDalamKurlumMapel($mapel_id)
    {
        // Ambil data kurikulum mapel dari database
        $KurikulumMapel = KurikulumMapel::select('mapel_id')->where('mapel_id', $mapel_id)->count();

        // ubah data menjadi json
        return response()->json($KurikulumMapel);
    }

    //======== Delete Data mapel ========//
    public function delMapel()
    {
        mapel::destroy(request()->mapel_id);

        // Alihkan ke halaman mapel
        return redirect()->route('mapel')->with('sukses', 'Data mapel berhasil dihapus!');
    }


    /** 
     * KelompokMapel 
     */
    //======== Add Data KelompokMapel ========//
    public function addKelompokMapel()
    {
        $data = request()->validate([
            'kelompok_mapel'  => 'required|unique:kelompok_mapel|min:1|max:150'
        ], [
            'required'          => ':attribute wajib diisi!',
            'max'               => 'Data kelompok mapel diisi maksimal :max karakter!',
            'min'               => 'Data kelompok mapel diisi minimal :min karakter!',
            'kelompok_mapel.unique' => 'Nama kelompok mapel tersebut sudah digunakan!'
        ]);

        KelompokMapel::create($data);

        // Alihkan ke halaman KelompokMapel
        return redirect()->route('mapel')->with('sukses', 'Data kelompok mapel berhasil ditambahkan!');
    }

    //======== Update Data KelompokMapel ========//
    public function editKelompokMapel()
    {
        $data = request()->validate([
            'kelompok_mapel'  => 'required|unique:kelompok_mapel,kelompok_mapel,' . request()->kelompok_mapel_id . '|min:1|max:150'
        ], [
            'required'          => ':attribute wajib diisi!',
            'max'               => 'Data kelompok mapel diisi maksimal :max karakter!',
            'min'               => 'Data kelompok mapel diisi minimal :min karakter!',
            'kelompok_mapel.unique' => 'Nama KelompokMapel tersebut sudah digunakan!'
        ]);

        KelompokMapel::where('id', request()->kelompok_mapel_id)->update($data);

        // Alihkan ke halaman KelompokMapel
        return redirect()->route('mapel')->with('sukses', 'Data kelompok mapel berhasil diperbarui!');
    }

    //======== Send Data Kelompok Mapel Dalam Kurikulum Mapel To Json ========//
    public function getKelmaDalamKurlumMapel($kelompok_mapel_id)
    {
        // Ambil data kurikulum mapel dari database
        $KurikulumMapel = KurikulumMapel::select('kelompok_mapel_id')->where('kelompok_mapel_id', $kelompok_mapel_id)->count();

        // ubah data menjadi json
        return response()->json($KurikulumMapel);
    }

    //======== Delete Data KelompokMapel ========//
    public function delKelompokMapel()
    {
        KelompokMapel::destroy(request()->kelompok_mapel_id);

        // Alihkan ke halaman KelompokMapel
        return redirect()->route('mapel')->with('sukses', 'Data kelompok mapel berhasil dihapus!');
    }
}
