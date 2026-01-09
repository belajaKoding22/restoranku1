<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        // pengecekan query parameter 'meja'
        $tableNumber = $request->query('meja');
        if ($tableNumber) {
            Session::put('table_number', $tableNumber);
        }

        // mengambil data item yang aktif dari database
        $items = Item::where('is_active', 1)->orderBy('name', 'asc')->get();

        // mengirim data item dan nomor meja ke view
        return view('costumer.menu', compact('items', 'tableNumber'));
    }


    public function cart()
    {
        $cart = Session::get('cart');
        return view('costumer.cart', compact('cart'));
    }

    public function addToCart(Request $request)
    {
        // Validasi input
        $menuId = $request->input('id');
        $menu = Item::findOrFail($menuId);
        
        // Cek jika menu ditemukan
        if (!$menu) {
            return response()->json([
                'status' => 'error',
                'error' => 'Menu item tidak ditemukan'
            ], 404);
        } 

        // Ambil keranjang dari session atau inisialisasi jika belum ada
        $cart = Session::get('cart');

        // Tambahkan item ke keranjang
        if (isset($cart[$menuId])) {
            $cart[$menuId]['qty'] += 1;
        } else {
            $cart[$menuId] = [
                'id' => $menu->id,
                'name' => $menu->name,
                'price' => $menu->price,
                'image' => $menu->img,
                'qty' => 1
            ];
        }

        // Simpan keranjang ke session
        Session::put('cart', $cart);

        // Kembalikan respon sukses
        return response()->json([
            'status' => 'success',
            'message' => 'Item berhasil ditambahkan ke keranjang',
            'cart' => $cart
        ]);
        
    }
}
