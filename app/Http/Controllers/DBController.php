<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\barang;
use App\Models\Ruangan;
use App\Models\view_Barang;
use App\Models\TransaksiUpdate;

class DBController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        $Ruangan = Ruangan::Pluck('Name', 'IdRuangan');
        $item = view_Barang::latest()->paginate(5);
        return view('pages.barang',compact('item', 'Ruangan'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //// menampilkan halaman create
        return view('Barang.create');
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
            'Name' => 'required',
            'Code' => 'required',
            'IdRuangan' => 'required'
        ]);
         
        /// insert setiap request dari form ke dalam database via model
        /// jika menggunakan metode ini, maka nama field dan nama form harus sama
        
        barang::create($request->all());
        
        // TransaksiUpdate::create();
        if(empty(TransaksiUpdate::latest('IdTrans')->first()->IdTrans)){
            $lastid = 1;
        } else {
            $lastid = (TransaksiUpdate::latest('IdTrans')->first()->IdTrans)+1;
        }

        $item = new TransaksiUpdate();
        $item -> IdBarang = barang::latest('IdBarang')->first()->IdBarang;
        $item -> IdRuangan = $request->IdRuangan;
        $item -> Trans = "TR-" .$lastid;
        $item -> Remark = "New";
        $item -> save();

        /// redirect jika sukses menyimpan data
        return redirect()->route('Barang.index')
                        ->with('success','Post created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(barang $item)
    {
        /// dengan menggunakan resource, kita bisa memanfaatkan model sebagai parameter
        /// berdasarkan id yang dipilih
        /// href="{{ route('item.show',$post->id) }}
        return view('pages.bedit',compact('item'));
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
        $item = barang::where('IdBarang',$id)->first();
        return view('pages.bedit',compact('item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $item)
    {
        /// membuat validasi untuk title dan content wajib diisi
        $request->validate([
            'Name' => 'required',
        ]);
         
        // var_dump($item);
        /// mengubah data berdasarkan request dan parameter yang dikirimkan
        $update = barang::where('IdBarang',$item)->first();
        $update->update($request->all());
         
        /// setelah berhasil mengubah data
        return redirect()->route('Barang.index')
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
        $del = barang::where('IdBarang',$item)->first();
  
        if ($item != null) {
            $del->delete();
            return redirect()->route('Barang.index')->with('success','Barang berhasil di hapus');
        }
        return redirect()->route('Barang.index')->with('success','Gagal');
    }
    
    public function dropdownAdd(Request $req){
        $Ruangan = Ruangan::Pluck('Name', 'IdRuangan');
        return view('pages.badd',compact('Ruangan'));
    }

}
