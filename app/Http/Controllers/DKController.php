<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\barang;
use App\Models\Kondisi;
use App\Models\statusbarang;
use App\Models\TransaksiUpdate;
use Illuminate\Support\Facades\Auth;
use DB;

class DKController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $clause = $this->Checkrole();

        $data = DB::select('SELECT br.Code, br.IdBarang, br.Name barang, ru.Name ruangan, bs.Status, brd.Remark, brd.IdBarangDetail FROM gatebk g LEFT JOIN barang br ON br.IdBarang = g.IdBarang LEFT JOIN barangdetail brd ON brd.IdBarangDetail = g.IdKondisi AND brd.IdBarang = g.IdBarang LEFT JOIN barangstatus bs ON bs.id = brd.`Status` LEFT JOIN ruangan ru ON ru.IdRuangan = br.IdRuangan where ('.$clause.') and brd.IdBarangDetail is not null and brd.Status != 3 and brd.Status != 4');
        $kondisi = statusbarang::Pluck('status', 'id');
        return view('pages.Kondisi.Kview',compact('data', 'kondisi'))-> with ('i', (request()->input('page', 1) - 1) * 100);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //// menampilkan halaman create
        $clause = $this->Checkrole();

        $data = DB::select('SELECT br.IdBarang, br.Name FROM barang br left join gatebk g on br.IdBarang = g.IdBarang left join barangdetail brd on brd.IdBarangDetail = g.IdKondisi LEFT JOIN ruangan ru ON ru.IdRuangan = br.IdRuangan where ('.$clause.') and (brd.IdBarang is NULL or brd.Status = 3)');

        $kondisi = statusbarang::Pluck('status', 'id');
        return view('pages.Kondisi.Kadd', compact('data', 'kondisi'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /// membuat validasi untuk title dan content wajib diisi
        $request->validate([
            'Remark' => 'required',
            'Kondisi' => 'required'
        ]);

        $this -> addKondisi($request);
        
        /// redirect jika sukses menyimpan data
        return redirect()->route('Kondisi.index')
                        ->with('success','Post created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        /// dengan menggunakan resource, kita bisa memanfaatkan model sebagai parameter

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        /// dengan menggunakan resource, kita bisa memanfaatkan model sebagai parameter
        /// berdasarkan id yang dipilih
        /// href="{{ route('item.edit',$item->id) }}
        $item = Kondisi::where('IdBarangDetail',$id)->first();
        return view('pages.Kondisi.bedit',compact('item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $data)
    {   
     
        $data = Kondisi::where('IdBarangDetail', $data)->first();
        $request->merge(['IdBarang' => $data->IdBarang]);
        $this -> addKondisi($request);

        /// setelah berhasil mengubah data
        return redirect()->route('Kondisi.index')
                        ->with('success','Post updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($item)
    {
        /// melakukan hapus data berdasarkan parameter yang dikirimkan
        $del = Kondisi::where('IdBarangDetail',$item)->first();
  
        if ($item != null) {
            $del->delete();
            return redirect()->route('Barang.index')->with('success','Barang berhasil di hapus');
        }
        return redirect()->route('Barang.index')->with('success','Gagal');
    }

    public function Checkrole(){
        $userid = Auth::user()->id;
        $data = DB::select('SELECT IdRuangan FROM userrole WHERE userid =(?)',array($userid));
        $flag = true;
        $clause = "";
        for ($i=0; $i < count($data) ; $i++) {
            if ($flag) {
                $clause = "ru.IdRuangan = ".$data[$i]->IdRuangan;
                $flag = false;
            } 
            $clause .= " Or ru.IdRuangan = ".$data[$i]->IdRuangan;
        }
        return $clause;
    }

    public function addKondisi($request){
        if(empty(Kondisi::latest('Counter')->first()->$request->IdBarang)){
            $lastid = 1;
        } else {
            $lastid = (Kondisi::latest('Counter')->first()->$request->IdBarang)+1;
        }
        
        $item = new Kondisi();
        $item -> IdBarang = $request->IdBarang;
        $item -> Status = $request->Kondisi;
        $item -> Kondisi = $request->Kondisi;
        $item -> Remark = $request->Remark;
        $item -> Pelapor = Auth::user()->name;
        $item -> Counter = $lastid;
        $item -> save();

        DB::table('gateBK')->where('IdBarang',$request->IdBarang)->update(['IdKondisi'=> $item->IdBarangDetail]);
    }

    public function BarangRusak()
    {
        $clause = $this->Checkrole();

        $data = DB::select('SELECT br.Code, br.IdBarang, br.Name barang, ru.Name ruangan, bs.Status, brd.Remark, brd.IdBarangDetail FROM gatebk g LEFT JOIN barang br ON br.IdBarang = g.IdBarang LEFT JOIN barangdetail brd ON brd.IdBarangDetail = g.IdKondisi AND brd.IdBarang = g.IdBarang LEFT JOIN barangstatus bs ON bs.id = brd.`Status` LEFT JOIN ruangan ru ON ru.IdRuangan = br.IdRuangan where ('.$clause.') and brd.IdBarangDetail is not null and brd.Status = 4');

        return view('pages.Kondisi.KRusak',compact('data'))-> with ('i', (request()->input('page', 1) - 1) * 100);
    }


}
