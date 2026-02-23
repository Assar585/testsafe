<?php

namespace App\Http\Controllers\Cybersource;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class CybersourceSettingController extends Controller
{
    public function configuration(){
        $payment_method = PaymentMethod::where('addon_identifier', 'cybersource')->first();
        return view('cybersource.configuration', compact('payment_method'));
    }
}
