<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\CombinedOrder;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Models\CustomerPackage;
use App\Models\SellerPackage;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\SellerPackageController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\CheckoutController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class CybersourceController extends Controller
{

    public function pay(Request $request)
    {

        $amount = 0;
        $user = User::find($request->user_id);
        $data['transactionUuid'] = self::generateUniqueId();
        $data['currency'] = \App\Models\Currency::findOrFail(get_setting('system_default_currency'))->code;


        if ($request->payment_type) {

            $paymentType = $request->payment_type;
            $data['transaction_type'] = $request->payment_type;
            $data['user'] = $user;

            if ($paymentType == 'cart_payment') {
                $combined_order = CombinedOrder::findOrFail($request->combined_order_id);
                $data['combined_order'] = $combined_order;
                $data['amount'] = round($combined_order->grand_total);
                $data['reference_number'] = $paymentType . '-' . $user->id . '-' . $combined_order->id . '-' . 'app';
            } elseif ($paymentType == 'order_re_payment') {
                $order = Order::findOrFail($request->order_id);
                $data['amount'] = round($order->grand_total);
                $data['reference_number'] = $paymentType . '-' . $user->id . '-' . $order->id . '-' . 'app';
            } elseif ($paymentType == 'wallet_payment') {
       
                $data['amount'] = round($request->amount);
                $data['reference_number'] = $paymentType . '-' . $user->id . '-' . 'app';
            } elseif ($paymentType == 'customer_package_payment') {
                $customer_package = CustomerPackage::findOrFail($request->package_id);
                $data['customer_package'] = $customer_package;
                $data['amount'] = round($customer_package->amount);
                $data['reference_number'] = $paymentType . '-' . $user->id . '-' . $request->package_id . '-' . 'app';
            } elseif ($paymentType == 'seller_package_payment') {
                $seller_package = SellerPackage::findOrFail($request->package_id);
                $data['seller_package'] = $seller_package;
                $data['amount'] = round($seller_package->amount);
                $data['reference_number'] = $paymentType . '-' . $user->id . '-' . $request->package_id . '-' . 'app';
            }
        }

        return view('cybersource.cyber_form', $data);
    }


    public function process(Request $request)
    {

        $data = $request->all();
        $HMAC_SHA256 =  'sha256';
        $secret_key = env('CYBERSOURCE_SECRET_KEY');

        return view('cybersource.cyber_confirmation', compact('data', 'secret_key'));
    }


    public function callback(Request $request)
    {

        $payment_details = $request->all();
        $parts = explode('-', $payment_details['req_reference_number']);
        $paymentType = $parts[0];
        Auth::loginUsingId($parts[1]);
        $payment = json_encode($request->all());

        if ($payment_details['decision'] == 'ACCEPT') {
            if ($paymentType == 'cart_payment') {
                checkout_done($parts[2], json_encode($payment));
            } elseif ($paymentType == 'order_re_payment') {
                order_re_payment_done($parts[2], 'Cybersource', json_encode($payment));
            } elseif ($paymentType == 'wallet_payment') {
                wallet_payment_done($parts[1], $request->amount, 'Cybersource', json_encode($payment));
            } elseif ($paymentType == 'seller_package_payment') {
                seller_purchase_payment_done($parts[1], $parts[2], 'Cybersource', json_encode($payment));
            } elseif ($paymentType == 'customer_package_payment') {
                customer_purchase_payment_done($parts[1], $parts[2], 'Cybersource', json_encode($payment));
            }

            return response()->json(['result' => true, 'message' => translate("Payment is successful")]);
        } else {
            return response()->json(['result' => false, 'message' => translate("Payment is failed")]);
        }
    }


    public function webhook(Request $request)
    {

        $payment = json_encode($request);

        if (Session::has('payment_type')) {
            $paymentType = Session::get('payment_type');
            $paymentData = $request->session()->get('payment_data');
            if ($paymentType == 'cart_payment') {
                return (new CheckoutController)->checkout_done(Session::get('combined_order_id'), $payment);
            } elseif ($paymentType == 'order_re_payment') {
                return (new CheckoutController)->orderRePaymentDone($paymentData, $payment);
            } elseif ($paymentType == 'wallet_payment') {
                return (new WalletController)->wallet_payment_done($paymentData, $payment);
            } elseif ($paymentType == 'customer_package_payment') {
                return (new CustomerPackageController)->purchase_payment_done($paymentData, $payment);
            } elseif ($paymentType == 'seller_package_payment') {
                return (new SellerPackageController)->purchase_payment_done($paymentData, $payment);
            }
        }
    }


    public static function generateUniqueId()
    {
        $randomNumber = str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        return 'WEB' . $randomNumber . 'TP' . substr(Str::uuid()->toString(), -8);
    }
}
