<?php

namespace App\Http\Controllers;

use App\Models\PaymentInformation;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\ClubPoint;
use App\Models\RefundRequest;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Wallet;
use App\Models\User;
use App\Utility\EmailUtility;
use Artisan;
use Auth;

class RefundRequestController extends Controller
{
    public function __construct()
    {
        // Staff Permission Check
        $this->middleware(['permission:view_refund_requests'])->only('admin_index');
        $this->middleware(['permission:view_approved_refund_requests'])->only('paid_index');
        $this->middleware(['permission:view_rejected_refund_requests'])->only('rejected_index');
        $this->middleware(['permission:refund_request_configuration'])->only('refund_config');
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    //Store Customer Refund Request
    public function request_store(Request $request, $id)
    {
        $user = auth()->user();
        $order_detail = OrderDetail::where('id', $id)->first();
        $refund = new RefundRequest;
        $refund->user_id = $user->id;
        $refund->order_id = $order_detail->order_id;
        $refund->order_detail_id = $order_detail->id;
        $refund->seller_id = $order_detail->seller_id;
        $refund->seller_approval = 0;
        $refund->reason = $request->reason;
        $refund->images = $request->images;
        $refund->admin_approval = 0;
        $refund->preferred_payment_channel = 'wallet';
        $refund->admin_seen = 0;
        if(is_numeric($order_detail->gst_amount)){
             $refund->refund_amount = round($order_detail->price + get_gst_by_price_and_rate($order_detail->price, $order_detail->gst_rate), 2);
        }else{
            $refund->refund_amount = $order_detail->price + $order_detail->tax;
        }
        
        $refund->refund_status = 0;
        if (addon_is_activated('offline_payment') && addon_is_activated(identifier: 'refund_request')) {
            $refund->preferred_payment_channel = $request->preferred_payment_channel;
            if ($request->preferred_payment_channel == 'wallet') {
                $refund->payment_information_id = null;
            } else {
                $refund->payment_information_id = $request->payment_information_id;
            }
        }
        if ($refund->save()) {

            // Refund Request email to admin, Seller, customer
            $admin = get_admin();
            $emailIdentifiers = array('refund_request_email_to_admin');
            if ($order_detail->order->user->email != null) {
                array_push($emailIdentifiers, 'refund_request_email_to_customer');
            }
            if ($order_detail->order->seller_id != $admin->id) {
                array_push($emailIdentifiers, 'refund_request_email_to_seller');
            }

            EmailUtility::refundEmail($emailIdentifiers, $refund);

            flash(translate("Refund Request has been sent successfully"))->success();
            return redirect()->route('purchase_history.index');
        } else {
            flash(translate("Something went wrong"))->error();
            return back();
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function vendor_index_old()
    {
        $refunds = RefundRequest::where('seller_id', Auth::user()->id)->latest()->paginate(10);
        return view('refund_request.frontend.recieved_refund_request.index', compact('refunds'));
    }

    public function vendor_index(Request $request)
    {
        $sort_search = null;
        $refund_tabs = ['All Refunds', 'Pending', 'Approved', 'Rejected'];

        $refunds = RefundRequest::where('seller_id', Auth::user()->id)->with(['order', 'orderDetail.product'])->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $sort_search = $request->search;

            $refunds->where(function ($query) use ($sort_search) {

                $query->whereHas('order', function ($q) use ($sort_search) {
                    $q->where('code', 'like', '%' . $sort_search . '%');
                })
                ->orWhereHas('orderDetail.product', function ($q) use ($sort_search) {
                    $q->where('name', 'like', '%' . $sort_search . '%');
                });
            });
        }

        $refunds = $refunds->paginate(15);

        return view('refund_request.frontend.recieved_refund_request.index', compact('refunds', 'sort_search', 'refund_tabs'));
    }

    public function seller_filter(Request $request)
    {
        // Log::info('Filter All Customer Request: ', $request->all());
        $refund_requests = RefundRequest::where('seller_id', Auth::user()->id)->with(['order', 'orderDetail.product'])->orderBy('created_at', 'desc');
        $sort_search = null;

        if ($request->refund_request_status == "approved") {
            $refund_requests = $refund_requests->where('seller_approval', 1);

        } else if ($request->refund_request_status == 'rejected') {
            $refund_requests = $refund_requests->where('seller_approval', 2);

        } else if ($request->refund_request_status == 'pending') {
            $refund_requests = $refund_requests
                ->where('seller_approval', 0)
                ->where('admin_approval', 0);
        }

        if ($request->search != null) {
            $sort_search = $request->search;
            $refund_requests->where(function ($query) use ($sort_search) {

                $query->whereHas('order', function ($q) use ($sort_search) {
                    $q->where('code', 'like', '%' . $sort_search . '%');
                })

                ->orWhereHas('orderDetail.product', function ($q) use ($sort_search) {
                    $q->where('name', 'like', '%' . $sort_search . '%');
                });
            });
        }

        $refund_requests = $refund_requests->paginate(15);
        $view = view(
            'refund_request.frontend.recieved_refund_request.table',
            compact('refund_requests', 'sort_search')
        )->render();
        return response()->json(['html' => $view]);
    }

    public function seller_refund_configuration()
    {
        return view('refund_request.frontend.configuration');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function customer_index()
    {
        $refunds = RefundRequest::where('user_id', Auth::user()->id)->latest()->paginate(10);
        return view('refund_request.frontend.refund_request.index', compact('refunds'));
    }

    //Set the Refund configuration
    public function refund_config()
    {
        return view('refund_request.config');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function refund_time_update(Request $request)
    {
        $business_settings = BusinessSetting::where('type', $request->type)->first();
        if ($business_settings != null) {
            $business_settings->value = $request->value;
            $business_settings->save();
        } else {
            $business_settings = new BusinessSetting;
            $business_settings->type = $request->type;
            $business_settings->value = $request->value;
            $business_settings->save();
        }
        Artisan::call('cache:clear');
        flash(translate("Refund Request sending time has been updated successfully"))->success();
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function refund_sticker_update(Request $request)
    {
        $business_settings = BusinessSetting::where('type', $request->type)->first();
        if ($business_settings != null) {
            $business_settings->value = $request->logo;
            $business_settings->save();
        } else {
            $business_settings = new BusinessSetting;
            $business_settings->type = $request->type;
            $business_settings->value = $request->logo;
            $business_settings->save();
        }
        Artisan::call('cache:clear');
        flash(translate("Refund Sticker has been updated successfully"))->success();
        return back();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function admin_index(Request $request)
    {
        $sort_search = null;
        $refund_tabs = ['All Refunds', 'Admin Refunds', 'Seller Refunds', 'Pending', 'Approved', 'Rejected', 'Wallet'];
        if (addon_is_activated('offline_payment')) {
            $refund_tabs[] = 'Offline';
        }

        $refunds = RefundRequest::with(['order', 'seller', 'user', 'orderDetail.product'])->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $sort_search = $request->search;

            $refunds->where(function ($query) use ($sort_search) {

                $query->whereHas('order', function ($q) use ($sort_search) {
                    $q->where('code', 'like', '%' . $sort_search . '%');
                })
                ->orWhereHas('seller', function ($q) use ($sort_search) {
                    $q->where('name', 'like', '%' . $sort_search . '%');
                })
                ->orWhereHas('orderDetail.product', function ($q) use ($sort_search) {
                    $q->where('name', 'like', '%' . $sort_search . '%');
                });
            });
        }

        $refunds = $refunds->paginate(15);

        return view('refund_request.index', compact('refunds', 'sort_search', 'refund_tabs'));
    }

    public function filter_refund_request(Request $request)
    {
        // Log::info('Filter All Customer Request: ', $request->all());
        $refund_requests = RefundRequest::with(['order', 'seller', 'user', 'orderDetail.product'])->orderBy('created_at', 'desc');
        $sort_search = null;

        if ($request->refund_request_status == "approved") {
            $refund_requests = $refund_requests->where('refund_status', 1);
        } else if ($request->refund_request_status == 'rejected') {
            $refund_requests = $refund_requests->where('refund_status', 2);
        } else if ($request->refund_request_status == 'wallet') {
            $refund_requests = $refund_requests->where('preferred_payment_channel', 'wallet')->orWhereNull('preferred_payment_channel');
        } else if ($request->refund_request_status == 'offline') {
            $refund_requests = $refund_requests->where('preferred_payment_channel', 'offline');
        } else if ($request->refund_request_status == 'pending') {
            $refund_requests = $refund_requests->where('refund_status', 0);
        } else if ($request->refund_request_status == 'admin_refunds') {
            $refund_requests = $refund_requests->whereHas('seller', function ($q) {
                $q->where('user_type', 'admin');
            });
        }else if ($request->refund_request_status == 'seller_refunds') {
            $refund_requests = $refund_requests->whereHas('seller', function ($q) {
                $q->where('user_type', 'seller');
            });
        }

        if ($request->search != null) {
            $sort_search = $request->search;
            $refund_requests->where(function ($query) use ($sort_search) {

                $query->whereHas('order', function ($q) use ($sort_search) {
                    $q->where('code', 'like', '%' . $sort_search . '%');
                })

                ->orWhereHas('seller', function ($q) use ($sort_search) {
                    $q->where('name', 'like', '%' . $sort_search . '%');
                })
                ->orWhereHas('orderDetail.product', function ($q) use ($sort_search) {
                    $q->where('name', 'like', '%' . $sort_search . '%');
                });
            });
        }

        $refund_requests = $refund_requests->paginate(15);
        $view = view(
            'refund_request.table',
            compact('refund_requests', 'sort_search')
        )->render();
        return response()->json(['html' => $view]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function paid_index(Request $request)
    {
        $sort_search = null;
        $refund_tabs = [ 'All Refunds', 'Admin Refunds', 'Seller Refunds', 'Pending', 'Approved', 'Rejected', 'Wallet'];
        if (addon_is_activated('offline_payment')) {
            $refund_tabs[] = 'Offline';
        }
        $refunds = RefundRequest::with([ 'order', 'seller', 'user', 'orderDetail.product' ])->where('refund_status', 1)->latest();

        if ($request->filled('search')) {
            $search = $request->search;

            $refunds->where(function ($query) use ($search) {
                $query->whereHas('order', function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%");
                })
                    ->orWhereHas('seller', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('orderDetail.product', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $refunds = $refunds->paginate(15);
        $active_tab = 'approved'; 
        return view( 'refund_request.index', compact('refunds', 'sort_search', 'refund_tabs', 'active_tab'));
    }

    public function rejected_index(Request $request)
    {
        $sort_search = null;
        $refund_tabs = [ 'All Refunds', 'Admin Refunds', 'Seller Refunds', 'Pending', 'Approved', 'Rejected', 'Wallet'];
        if (addon_is_activated('offline_payment')) {
            $refund_tabs[] = 'Offline';
        }
        $refunds = RefundRequest::with([ 'order', 'seller', 'user', 'orderDetail.product' ])->where('refund_status', 2)->latest();

        if ($request->filled('search')) {
            $search = $request->search;

            $refunds->where(function ($query) use ($search) {
                $query->whereHas('order', function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%");
                })
                    ->orWhereHas('seller', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('orderDetail.product', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $refunds = $refunds->paginate(15);
        $active_tab = 'rejected'; 
        return view( 'refund_request.index', compact('refunds', 'sort_search', 'refund_tabs', 'active_tab'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function request_approval_vendor(Request $request)
    {
        $authUser = auth()->user();
        $refund = RefundRequest::findOrFail($request->el);
        $refund->seller_approval = 1;

        if ($refund->save()) {
            // Refund Request Approval mail to admin and seller
            $emailIdentifiers = array('refund_accepted_by_seller_email_to_admin', 'refund_accepted_by_seller_email_to_seller');
            EmailUtility::refundEmail($emailIdentifiers, $refund);

            return 1;
        } else {
            return 0;
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function refund_pay(Request $request)
    {
        $refund = RefundRequest::findOrFail($request->refund_id);

        if ($refund->seller_approval == 1) {
            $seller = Shop::where('user_id', $refund->seller_id)->first();
            if ($seller != null) {
                $seller->admin_to_pay -= $refund->refund_amount;
            }
            $seller->save();
        }

        $refund_amount = $refund->refund_amount;

        // Club Point conversion check
        if (addon_is_activated('club_point')) {
            $club_point = ClubPoint::where('order_id', $refund->order_id)->first();
            if ($club_point != null) {
                $club_point_details = $club_point->club_point_details->where('product_id', $refund->orderDetail->product->id)->first();

                if ($club_point->convert_status == 1) {
                    $refund_amount -= $club_point_details->converted_amount;
                } else {
                    $club_point_details->refunded = 1;
                    $club_point_details->save();
                }
            }
        }

        $wallet = new Wallet;
        $wallet->user_id = $refund->user_id;
        $wallet->amount = $refund_amount;
        $wallet->payment_method = 'Refund';
        $wallet->payment_details = 'Product Money Refund';
        $wallet->save();

        $user = User::findOrFail($refund->user_id);
        $user->balance += $refund_amount;
        $user->save();
        if (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff') {
            $refund->admin_approval = 1;
            $refund->refund_status = 1;
        }

        if ($refund->save()) {

            // Refund request approved email send to admin, seller and Customer
            $admin = get_admin();
            $order_detail =  $refund->orderDetail;
            $emailIdentifiers = array('refund_accepted_by_admin_email_to_admin');
            if ($order_detail->order->user->email != null) {
                array_push($emailIdentifiers, 'refund_request_accepted_email_to_customer');
            }
            if ($order_detail->order->seller_id != $admin->id) {
                array_push($emailIdentifiers, 'refund_accepted_by_admin_email_to_seller');
            }

            EmailUtility::refundEmail($emailIdentifiers, $refund);

            return 1;
        } else {
            return response()->json(['success' => false, 'message' => translate('Something went wrong.')]);
        }
    }

    public function refund_offline_pay(Request $request)
    {
        $refund = RefundRequest::findOrFail($request->refund_id);

        if ($refund->seller_approval == 1) {
            $seller = Shop::where('user_id', $refund->seller_id)->first();
            if ($seller != null) {
                $seller->admin_to_pay -= $refund->refund_amount;
            }
            $seller->save();
        }

        // Club Point conversion check
        if (addon_is_activated('club_point')) {
            $club_point = ClubPoint::where('order_id', $refund->order_id)->first();
            if ($club_point != null) {
                $club_point_details = $club_point->club_point_details->where('product_id', $refund->orderDetail->product->id)->first();
                $club_point_details->refunded = 1;
                $club_point_details->save();
            }
        }

        if (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff') {
            $refund->admin_approval = 1;
            $refund->refund_status = 1;
        }

        $refund->transaction_id = $request->trx_id;
        $refund->photo = $request->photo;

        if ($refund->save()) {
            // Refund request approved email send to admin, seller and Customer
            $admin = get_admin();
            $order_detail =  $refund->orderDetail;
            $emailIdentifiers = array('refund_accepted_by_admin_email_to_admin');
            if ($order_detail->order->user->email != null) {
                array_push($emailIdentifiers, 'refund_request_accepted_email_to_customer');
            }
            if ($order_detail->order->seller_id != $admin->id) {
                array_push($emailIdentifiers, 'refund_accepted_by_admin_email_to_seller');
            }

            EmailUtility::refundEmail($emailIdentifiers, $refund);

            flash(translate('Refund has been sent successfully.'))->success();
        } else {
            flash(translate('Something went wrong.'))->error();
        }
        return back();
    }
    
    public function reject_refund_request(Request $request)
    {
        $authUserType = auth()->user()->user_type;
        $refund = RefundRequest::findOrFail($request->refund_id);
        if ($authUserType == 'admin' ||  $authUserType == 'staff') {
            $refund->admin_approval = 2;
            $refund->refund_status  = 2;
            $refund->admin_reject_reason  = $request->reject_reason;
        } else {
            $refund->seller_approval = 2;
            $refund->reject_reason  = $request->reject_reason;
        }

        if ($refund->save()) {
            // Refund request denied email send to admin, seller and Customer
            $admin = get_admin();
            $order_detail =  $refund->orderDetail;
            if ($authUserType == 'admin' ||  $authUserType == 'staff') {
                $emailIdentifiers = array('refund_denied_by_admin_email_to_admin');
                if ($order_detail->order->user->email != null) {
                    array_push($emailIdentifiers, 'refund_request_denied_email_to_customer');
                }
                if ($order_detail->order->seller_id != $admin->id) {
                    array_push($emailIdentifiers, 'refund_denied_by_admin_email_to_seller');
                }
            } else {
                $emailIdentifiers = array('refund_denied_by_seller_email_to_admin', 'refund_denied_by_seller_email_to_seller');
            }
            EmailUtility::refundEmail($emailIdentifiers, $refund);

            flash(translate('Refund request rejected successfully.'))->success();
            return back();
        } else {
            return back();
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function refund_request_send_page($id)
    {
        $payment_information_id = 0;
        $user_id = Auth::user()->id;
        $payment_informations = PaymentInformation::where('user_id', $user_id)->get();
        if (count($payment_informations)) {
            $payment_information = $payment_informations->toQuery()->first();
            $payment_information_id = $payment_information->id;
            $default_payment_information = $payment_informations->toQuery()->where('set_default', 1)->first();
            if ($default_payment_information != null) {
                $payment_information_id = $default_payment_information->id;
            }
        }
        $order_detail = OrderDetail::findOrFail($id);
        if ($order_detail->product != null) {
            return view('refund_request.frontend.refund_request.create', compact('order_detail', 'payment_information_id'));
        } else {
            return back();
        }
    }

    /**
     * Show the form for view the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //Shows the refund reason
    public function reason_view($id)
    {
        $user = auth()->user();
        $refund = RefundRequest::findOrFail($id);
        if ($user->user_type == 'admin' || $user->user_type == 'staff') {
            if ($refund->orderDetail != null) {
                $refund->admin_seen = 1;
                $refund->save();
                return view('refund_request.reason', compact('refund'));
            }
        } else {
            return view('refund_request.frontend.refund_request.reason', compact('refund'));
        }
    }

    public function reject_reason_view($id)
    {
        $authUserType = auth()->user()->user_type;
        $refund = RefundRequest::findOrFail($id);
        if ($authUserType == 'customer') {
            $html = '
                <div class="mb-2">
                    <p>'.($refund->admin_reject_reason ?? 'N/A').'</p>
                </div>
            ';
        }else{
            $html = '
                <div class="mb-2">
                    <strong>Reason By Admin:</strong>
                    <p>'.($refund->admin_reject_reason ?? 'N/A').'</p>
                </div>
                <div>
                    <strong>Reason By Seller:</strong>
                    <p>'.($refund->reject_reason ?? 'N/A').'</p>
                </div>
            ';
        }
        return $html;
    }

    public function categoriesWiseProductRefund(Request $request)
    {
        $sort_search = null;
        $categories = Category::orderBy('order_level', 'desc');
        if ($request->has('search')) {
            $sort_search = $request->search;
            $categories = $categories->where('name', 'like', '%' . $sort_search . '%');
        }
        $categories = $categories->paginate(15);
        return view('backend.product.category_wise_refund.set_refund', compact('categories', 'sort_search'));
    }

    public function sellerCategoriesWiseProductRefund(Request $request)
    {
        $sort_search = null;
        $categories = Category::orderBy('order_level', 'desc');
        if ($request->has('search')) {
            $sort_search = $request->search;
            $categories = $categories->where('name', 'like', '%' . $sort_search . '%');
        }
        $categories = $categories->paginate(15);
        return view('refund_request.frontend.recieved_refund_request.category_wise_refund', compact('categories', 'sort_search'));
    }

    public function updateRefundSettings(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:categories,id',
            'refund_request_time' => 'nullable|integer|min:0',
        ]);

        $categoryId = $request->id;
        $refundTime = $request->refund_request_time ?? 0;

        $category = Category::findOrFail($categoryId);

        $childCategoryIds = $this->getAllChildCategoryIds($category->id);

        $allCategoryIds = array_merge($childCategoryIds, [$category->id]);

        Category::whereIn('id', $allCategoryIds)->update([
            'refund_request_time' => $refundTime,
        ]);

        return response()->json([
            'message' => 'Refund settings updated successfully for category and all its children!',
            'success' => true,
        ]);
    }

    private function getAllChildCategoryIds($parentId)
    {
        $childIds = [];

        $children = Category::where('parent_id', $parentId)->pluck('id');

        foreach ($children as $childId) {
            $childIds[] = $childId;
            $childIds = array_merge($childIds, $this->getAllChildCategoryIds($childId));
        }

        return $childIds;
    }

    public function checkRefundableCategory(Request $request)
    {
        try {
            $request->validate([
                'category_id' => 'required|exists:categories,id'
            ]);

            $category = Category::findOrFail($request->category_id);

            $isRefundable = $category->refund_request_time > 0;

            return response()->json([
                'status' => 'success',
                'is_refundable' => $isRefundable,
                'message' => $isRefundable
                    ? 'Category is refundable.'
                    : 'Category is not refundable.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not verify category refund status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkSellerRefundableCategory(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id'
        ]);

        $category = Category::findOrFail($request->category_id);

        $isRefundable = $category->refund_request_time > 0;

        return response()->json([
            'status' => 'success',
            'is_refundable' => $isRefundable,
            'message' => $isRefundable
                ? 'Category is refundable.'
                : 'Category is not refundable.'
        ]);
    }

    public function order_details_update()
    {
        $refund_request_time = get_setting('refund_request_time');

        $refundable_product_ids = Product::where('refundable', 1)->pluck('id'); 

        if ($refundable_product_ids->isNotEmpty()) {
            OrderDetail::whereIn('product_id', $refundable_product_ids)->update([
                    'refund_days' => $refund_request_time,
                ]);
        }
    }

    public function refund_offline_modal(Request $request)
    {
        return view('refund_request.modal', [
            'refund_id' => $request->refund_id
        ]);
    }

    public function payment_info_modal(Request $request)
    {
        $refund = RefundRequest::with('paymentInformation')->findOrFail($request->refund_id);

        $paymentInfo = $refund->paymentInformation;

        return view('refund_request.payment_info_modal', compact('refund', 'paymentInfo'));
    }

    public function reject_refund_modal(Request $request)
    {
        return view('refund_request.reject_refund_modal', [
            'refund_id' => $request->refund_id,
            'order_code' => $request->order_code
        ]);
    }

    public function receipt_show($id)
    {
        $refund = RefundRequest::findOrFail($id);
        $receipt = json_decode($refund->photo);
        return view('refund_request.frontend.refund_request.modal', compact('receipt'));
    }
}
