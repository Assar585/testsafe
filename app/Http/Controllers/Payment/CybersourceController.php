<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\CombinedOrder;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Models\CustomerPackage;
use App\Models\SellerPackage;
use App\Models\Address;
use App\Models\Country;
use App\Models\City;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\SellerPackageController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Auth;

class CybersourceController extends Controller
{

    public function pay(Request $request)
    {

        $amount = 0;
        $user = auth()->user();
        $addresses = Address::where('user_id', $user->id)->get();
        if(count($addresses) > 0){
            $address = $addresses->toQuery()->first();
            $address_text = $address->address;
            $address_id = $address->id;
            $country_id = $address->country_id;
            $city_id = $address->city_id;
            $postal_code = $address->postal_code;
            $phone = $address->phone;
            $default_address =$addresses->toQuery()->where('set_default', 1)->first();
            if($default_address != null){
                $address_text = $default_address->address;
                $address_id = $default_address->id;
                $country_id = $default_address->country_id;
                $city_id = $default_address->city_id;
                $postal_code = $default_address->postal_code;
                $phone = $default_address->phone;
            }
            $city = City::where('id', $city_id)->get()->toQuery()->first()->name;
            $country = Country::where('id', $country_id)->get()->toQuery()->first()->code;
            $user->address = $address_text;
            $user->city = $city;
            $user->country = $country;
            $user->postal_code = $postal_code;
            $user->phone = $phone;
        }

        

        $data['transactionUuid'] = self::generateUniqueId();
        $data['currency'] = \App\Models\Currency::findOrFail(get_setting('system_default_currency'))->code;
        if (Session::has('payment_type')) {
            $paymentType = Session::get('payment_type');
            $paymentData = Session::get('payment_data');
            $data['transaction_type'] = Session::get('payment_type');
            $data['user'] = $user;
            if ($paymentType == 'cart_payment') {
                $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));
                $data['combined_order'] = $combined_order;
                $data['amount'] = round($combined_order->grand_total);
                $data['reference_number'] = $paymentType . '-' . $user->id . '-' . $combined_order->id . '-' . 'web';
            } elseif ($paymentType == 'order_re_payment') {
                $order = Order::findOrFail($paymentData['order_id']);
                $data['amount'] = round($order->grand_total);
                $data['reference_number'] = $paymentType . '-' . $user->id . '-' . $order->id . '-' . 'web';
            } elseif ($paymentType == 'wallet_payment') {
                $data['amount'] = round($paymentData['amount']);
                $data['reference_number'] = $paymentType . '-' . $data['amount'] .  '-' . $user->id . '-' . 'web';
            } elseif ($paymentType == 'customer_package_payment') {
                $customer_package = CustomerPackage::findOrFail($paymentData['customer_package_id']);
                $data['customer_package'] = $customer_package;
                $data['amount'] = round($customer_package->amount);
                $data['reference_number'] = $paymentType . '-' . $user->id . '-' . $customer_package->id . '-' . 'web';
            } elseif ($paymentType == 'seller_package_payment') {
                $seller_package = SellerPackage::findOrFail($paymentData['seller_package_id']);
                $data['seller_package'] = $seller_package;
                $data['amount'] = round($seller_package->amount);
                $data['reference_number'] = $paymentType . '-' . $user->id . '-' . $seller_package->id . '-' . 'web';
            }
        }

        $parts = explode('-', $data['reference_number']);
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

        if (!isset($payment_details['decision']) || $payment_details['decision'] != 'ACCEPT') {
            flash(translate('Payment failed'))->error(); 
            return redirect()->route('home');
        }


        $parts = explode('-', $payment_details['req_reference_number']);

        

        if($parts[3] == 'web'){
            $paymentType = $parts[0];
            if ($paymentType == 'cart_payment') {         
                Auth::loginUsingId($parts[1]);
                Session::put('combined_order_id', $parts[2]);
                return redirect()->route('order_confirmed');
            } elseif ($paymentType == 'order_re_payment') {         
                Auth::loginUsingId($parts[1]);
                return redirect()->route('purchase_history.details', $parts[2]);
            } elseif ($paymentType == 'wallet_payment') {
                Auth::loginUsingId($parts[2]);
                return redirect()->route('wallet_recharge_success');
            } elseif ($paymentType == 'customer_package_payment') {
                Auth::loginUsingId($parts[1]);
                return redirect()->route('dashboard');
            } elseif ($paymentType == 'seller_package_payment') {         
                Auth::loginUsingId($parts[1]);
                return redirect()->route('dashboard');
            }
        }
        if($parts[3] == 'app'){
            self::app_payments($payment_details);
        }
        
    }

    public function webhook(Request $request)
    {

        $payment_details = $request->all();

        if (!isset($payment_details['decision']) || $payment_details['decision'] != 'ACCEPT') {
            flash(translate('Payment failed'))->error(); 
            return redirect()->route('home');
        }

        $parts = explode('-', $payment_details['req_reference_number']);
        if($parts[3] == 'web'){
            self::web_payments($payment_details);
        }
        if($parts[3] == 'app'){
            self::app_payments($payment_details);
        }

    }


    public static function web_payments($payment_details){
        $parts = explode('-', $payment_details['req_reference_number']);
        $paymentType = $parts[0];
        Auth::loginUsingId($parts[2]);
        $payment = json_encode($payment_details);
        if ($paymentType) {
            $paymentData = $payment_details;
            $paymentData['payment_method'] =  'Cybersource';
            if ($paymentType == 'cart_payment') {
                return (new CheckoutController)->checkout_done1($parts[2], $payment);
            } elseif ($paymentType == 'order_re_payment') {
                $paymentData['order_id'] =  $parts[2];
                return (new CheckoutController)->orderRePaymentDone1($paymentData, $payment);
            } elseif ($paymentType == 'wallet_payment') {
                $paymentData['amount'] =  $payment_details['req_amount'];
                $WalletController = new WalletController;
                return $WalletController->wallet_payment_done1($paymentData, $payment);
            } elseif ($paymentType == 'customer_package_payment') {
                $paymentData['customer_package_id'] =  $parts[2];
                return (new CustomerPackageController)->purchase_payment_done1($paymentData, $payment);
            } elseif ($paymentType == 'seller_package_payment') {
                $paymentData = [
                    'seller_package_id' => $parts[2],
                    'payment_method' => 'Cybersource',
                ];
                Session::put('payment_data', $paymentData);
                $paymentData['seller_package_id'] =  $parts[2];
                return (new SellerPackageController)->purchase_payment_done($paymentData, $payment);
            }
        }
    }

    public static function app_payments($payment_details){

        $parts = explode('-', $payment_details['req_reference_number']);
        $paymentType = $parts[0];
        Auth::loginUsingId($parts[1]);
        $payment = json_encode($payment_details);

        if ($payment_details['decision'] == 'ACCEPT') {
            if ($paymentType == 'cart_payment') {
                checkout_done($parts[2], json_encode($payment));
            } elseif ($paymentType == 'order_re_payment') {
                order_re_payment_done($parts[2], 'Cybersource', json_encode($payment));
            } elseif ($paymentType == 'wallet_payment') {
                wallet_payment_done($parts[1], $payment_details['req_amount'], 'Cybersource', json_encode($payment));
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


    public static function generateUniqueId()
    {
        $randomNumber = str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        return 'WEB' . $randomNumber . 'TP' . substr(Str::uuid()->toString(), -8);
    }
}
