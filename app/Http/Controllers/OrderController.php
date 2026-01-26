<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        // $order = Order::orderby('name', 'asc')->get();
        // return view('admin.order.index', compact('order'));
    }
}
