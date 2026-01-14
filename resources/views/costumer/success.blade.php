@extends('costumer.layouts.master')

@section('title', 'Pesanan Berhasil')

@section('content')
    <div class="container-fluid py-5 d-flex justify-content-center">
        <div class="receipt border p-4 bg-white shadow" style="width: 450px; margin-top: 5rem">
            <h5 class="text-center mb-2">Pesanan Berhasil dibuat</h5>
            @if ($order->payment_method = 'cash' && $order->status == 'pending')
                <p class="text-center"><span class="badge bg-danger">menunggu pembayaran</span></p>
            @elseif ($order->payment_method = 'qris' && $order->status == 'pending')
                <p class="text-center"><span class="badge bg-warning">Sedang menunggu konfirmasi pembayaran</span></p>
            @else
                <p class="text-center"><span class="badge bg-success">Pembayaran anda telah berhasil. Pesanan anda sedang di proses</span></p>
            @endif

            <hr>
            <h4 class="fw-bold text-center">Kode Bayar <br> <span class="text-primary">{{ $order->order_code}}</span></h4>
            <hr>
            <h5 class="mb-3 text-center">Detail Pesanan</h5>
            <table class="table table-borderless">
                <tbody>
                    @foreach ($orderItems as $orderItem)
                        <tr>
                            <td>{{ Str::limit($orderItem->item->name, 25) }} {{ $orderItem->quantity}}</td>
                            <td class="text-end">{{ 'Rp'. number_format($orderItem->price, 0, ',','.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <hr>
            <table class="table table-borderless">
                <tbody>
                    <tr>
                        <th scope="row">Subtotal</th>
                        <td class="text-end">{{ 'Rp'. number_format($order->sub_total, 0, ',','.') }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Pajak (10%)</th>
                        <td class="text-end">{{ 'Rp'. number_format($order->tax, 0, ',','.') }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Total</th>
                        <td class="text-end fw-bold">{{ 'Rp'. number_format($order->grand_total, 0, ',','.') }}</td>
                    </tr>
                </tbody>
            </table>
            <hr>
            @if ($order->payment_method == 'tunai')
                <p class="small text-center">Tunjakkan kode bayar ini ke kasir untuk menyelesaikan pembayaran. Jangan lupa senyum ya!!</p>
            @elseif ($order->payment_method == 'qris')
                <p class="small text-center">yea!! pembayaran berhasil. Silahkan duduk manis pesanan anda sedang di proses</p>
            @endif
            <hr>
            <a href="{{ route('menu') }}" class="btn btn-primary w-100">Kembali Ke Menu</a>
        </div>
    </div>
@endsection