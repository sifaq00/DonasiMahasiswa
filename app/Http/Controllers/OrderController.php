<?php

namespace App\Http\Controllers;

use Midtrans\Snap;
use App\Models\Order;
use Illuminate\Http\Request;
use Midtrans\Config;

class OrderController extends Controller
 {
    public function index(){
        return view('home');
    }
    public function checkout(Request $request){
        $request->request->add(['total_price'=>$request->qty * 100000, 'status' => 'Unpaid']);
        $order = Order::create($request->all());

// Set your Merchant Server Key
Config::$serverKey = config('midtrans.server_key');
// Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
\Midtrans\Config::$isProduction = false;
// Set sanitization on (default)
\Midtrans\Config::$isSanitized = true;
// Set 3DS transaction for credit card to true
\Midtrans\Config::$is3ds = true;

$params = array(
    'transaction_details' => array(
        'order_id' => $order->id,
        'gross_amount' => $order->total_price,
    ),
    'customer_details' => array(
        'name' => $request->name,
        'phone' => $request->phone,
    ),
);

$snapToken = \Midtrans\Snap::getSnapToken($params);
return view('checkout', compact('snapToken', 'order'));

    }
public function callback(Request $request){
    $serverKey = config('midtrans.server_key');
    $hashed = hash("sha512", $request->order_id.$request->status_code.$request->gross_amount.$serverKey);
    if($hashed == $request->signature_key){
        if($request->transaction_status == 'capture'){
            $order = Order::find($request->order_id);
            $order->update(['status' => 'Paid']);
        }
    }
}
 }
