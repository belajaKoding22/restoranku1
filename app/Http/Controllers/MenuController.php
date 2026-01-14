<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

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

    public function updateCart(Request $request)
    {
        $itemId = $request->input('id');
        $newQty = $request->input('qty');

        if ($newQty < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah item harus minimal 1.'
            ]);
        }

        $cart = Session::get('cart');
        if (isset($cart[$itemId])) {
            $cart[$itemId]['qty'] = $newQty;
            Session::put('cart', $cart);
            Session::flash('success', 'Jumlah item berhasil diperbarui.');

            return response()->json(['success' => true ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Item tidak ditemukan di keranjang.'
        ]);
    }

    public function removeCart(Request $request)
    {
        $itemId = $request->input('id');

        $cart = Session::get('cart');

        if (isset($cart[$itemId])) {
            unset($cart[$itemId]);
            Session::put('cart', $cart);

            Session::flash('success', 'Item berhasil dihapus dari keranjang.');

            return response()->json(['success' => true ]);
        }
    }

    public function clearCart()
    {
        Session::forget('cart');
        Session::flash('success', 'Keranjang berhasil dikosongkan.');
        return redirect()->route('cart');
    }

    public function checkout()
    {
        $cart = Session::get('cart');
        if (empty($cart)) {
            return redirect()->route('cart')->with('error', 'Keranjang anda masih kosong saat ini.');
        }

        $tableNumber = Session::get('table_number');
        
        return view('costumer.checkout', compact('cart', 'tableNumber'));
    }

    public function storeOrder(Request $request)
    {
       $cart = Session::get('cart');
       $tableNumber = Session::get('table_number');

       if(empty($cart)) {
           return redirect()->route('cart')->with('error', 'Keranjang anda masih kosong saat ini.');
       }

       $validasi = Validator::make($request->all(), [
           'fullname' => 'required|string|max:50', //diambil dari name input nama lengkap
           'phone' => 'required|string|max:15', //diambil dari name input nomor whatsapp
       ]);

       if($validasi->fails()){
            return redirect()->route('checkout')
                ->withErrors($validasi);
       }

       $total = 0;
       foreach($cart as $item) {
           $total += $item['price'] * $item['qty'];
        }

        
        $totalAmount = 0;
        foreach ($cart as $item) {
            $totalAmount += $item['price'] * $item['qty'];

            // di ambil dari orderItem
            $itemDetails[] = [
                'id' => $item['id'],
                'price' => (int) $item['price'] + ($item['price'] * 0.1), // termasuk pajak 10%
                'quantity' => $item['qty'],
                'name' => substr($item['name'], 0, 50), //panjang string dari 0 sampai 50
            ];
        }

        // membuat atau mendapatkan user berdasarkan nama dan telepon
        $user = User::firstOrCreate([
            'fullname' => $request->input('fullname'),
            'phone' => $request->input('phone'),
            'role_id' => 4 // role_id 4 untuk costumer
        ]);

        // menyimpan data order ke database
        $order = Order::create([
            'order_code' => 'ORD' . $tableNumber.'-'. time(),
            'user_id' => $user->id,
            'sub_total' => $totalAmount,
            'tax' => $totalAmount * 0.1, // pajak 10%
            'grand_total' => $totalAmount + ($totalAmount * 0.1),
            'status' => 'pending',
            'table_number' => $tableNumber,
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
        ]);

        // menyimpan data order item ke database
        foreach ($cart as $itemId => $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'item_id' => $item['id'],
                'quantity' => $item['qty'],
                'price' => (int) ($item['price'] + $item['qty']),
                'tax' => ($item['price'] * 0.1) * $item['qty'],
                'total_price' => ($item['price'] * $item['qty']) + (($item['price'] * 0.1) * $item['qty']),
            ]);

        }

        Session::forget('cart');

        return redirect()->route('order.success', ['orderId' => $order->order_code])->with('success', 'Pesanan anda telah diterima. Terima kasih!');
    }

    public function orderSuccess($orderId)
    {
        $order = Order::where('order_code', $orderId)->first();

        if (!$order) {
            return redirect()->route('menu')->with('error', 'Pesanan tidak ditemukan.');
        }

        $orderItems = OrderItem::where('order_id', $order->id)->get();

        // update status jika metode pembayaran qris
        if ($order->payment_method == 'qris') {
            $order->status = 'settlement';
            $order->save();
        }

        return view('costumer.success', compact('order', 'orderItems'));
    }
}
