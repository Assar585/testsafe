<?php

namespace App\Http\Controllers\Preorder;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SearchController;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Faq;
use App\Models\PreorderProduct;
use App\Models\PreorderProductCategory;
use App\Models\Tax;
use App\Services\PreorderService;
use Illuminate\Http\Request;
use Artisan;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class PreorderProductController extends Controller
{
    protected $preorderService;

    public function __construct(PreorderService $preorderService)
    {
        $this->middleware(['permission:view_all_preorder_products'])->only('index');
        $this->middleware(['permission:add_preorder_product'])->only('create');
        $this->middleware(['permission:edit_preorder_product'])->only('edit');
        $this->middleware(['permission:delete_preorder_product'])->only('destroy', 'bulkProductDestroy');

        $this->preorderService = $preorderService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $adminId = get_admin()->id;
        $products = PreorderProduct::with(['category', 'preorder_sample_order', 'preorder_prepayment']);
        $type = $request->user_type != null ? $request->user_type : 'all';
        $col_name = null;
        $query = null;
        $seller_id = null;
        $sort_search = null;

        
        if ($request->type != null) {
            $var = explode(",", $request->type);
            $col_name = $var[0];
            $query = $var[1];
            $products = $products->orderBy($col_name, $query);
            $sort_type = $request->type;
        }

        if ($request->search != null) {
            $sort_search = $request->search;
            $products = $products->where('product_name', 'like', '%' . $sort_search . '%')
                ->orWhereHas('preorder_product_translations', function ($q) use ($sort_search) {
                    $q->where('product_name', 'like', '%' . $sort_search . '%');
                });
        }
        $today = strtotime(date('d-m-Y'));
        $published_products = PreorderProduct::where('is_published', 1);
        $unpublished_products = PreorderProduct::where('is_published', '!=', 1);
        $discounted_products =  PreorderProduct::where('discount', '!=', null)
        ->where('discount_start_date', '<=', $today)
        ->where('discount_end_date', '>=', $today)
        ->get();

        if($type != 'all'){
            $products = $type == 'in_house' ? $products->where('user_id', $adminId) : $products->where('user_id','!=', $adminId);
            $published_products = $type == 'in_house' ? $published_products->where('user_id', $adminId) : $published_products->where('user_id','!=', $adminId);
            $unpublished_products = $type == 'in_house' ? $unpublished_products->where('user_id', $adminId) : $unpublished_products->where('user_id','!=', $adminId);
            $discounted_products  = $type == 'in_house' ? $discounted_products->where('user_id', $adminId) : $discounted_products->where('user_id','!=', $adminId);
        }
        $products = $products->orderBy('created_at', 'desc')->paginate(10);

        $data['inHouseProductCount'] = PreorderProduct::where('user_id', $adminId)->count();
        $data['sellerProductCount'] = PreorderProduct::where('user_id', '!=', $adminId)->count();
        $data['publishedProductCount'] = $published_products->count();
        $data['unpublishedProductCount'] = $unpublished_products->count();
        $data['discountedProductCount'] = $discounted_products->count();
       
        return view('preorder.backend.products.index', $data, compact('products', 'type', 'col_name', 'query', 'seller_id', 'sort_search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (addon_is_activated('gst_system')) {
            $business_info = admin_business_info();
            if ( empty($business_info) || !is_array($business_info) || empty($business_info['gstin'])) {
                flash(translate('Please Update Your GST Information'))->warning();
                return back();
            }
        }
        $data['categories'] = Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();
        $data['brands'] = Brand::all();
        $data['taxes'] = Tax::where('tax_status', 1)->get();
        return view('preorder.backend.products.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $this->validate($request, [
            'product_name' => ['required'],
            'category_id' => ['required'],
            'unit' => ['required'],
            'unit_price' => ['required'],
            'coupon_amount' => ['nullable','numeric', function ($attribute, $value, $fail) use ($request) {
                                if ($request->coupon_type == 'percent' && $value > 100) {
                                    $fail(__('The coupon amount must not exceed 100% when the coupon type is percent.'));
                                }
                                if ($request->coupon_type == 'flat' && $value >= $request->unit_price) {
                                    $fail(__('The coupon amount must be less than the unit price for flat type.'));
                                }
                            },
                        ],
            'discount' => ['nullable','numeric', function ($attribute, $value, $fail) use ($request) {
                                if ($request->discount_type == 'percent' && $value > 100) {
                                    $fail(__('The discount must not exceed 100% when the discount type is percent.'));
                                }
                                if ($request->discount_type != 'percent' && $value >= $request->unit_price) {
                                    $fail(__('The discount must be less than the unit price.'));
                                }
                            },
                        ],
            'prepayment_amount' => $request->is_prepayment ? 'required|lt:unit_price' : 'sometimes',
            'date_range' => $request->date_range ? ['required','string','regex:/^(\d{2}-\d{2}-\d{4} \d{2}:\d{2}:\d{2}) to (\d{2}-\d{2}-\d{4} \d{2}:\d{2}:\d{2})$/'] : 'nullable',
        ]);

        // Product Store
        $this->preorderService->productStore($request);

        flash(translate('Preorder Product Info Stored Successfully'))->success();
        return redirect()->route('preorder-product.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(PreorderProduct $preorderProduct)
    {
        // 
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $preorderProduct = PreorderProduct::findOrFail($id);
        if (addon_is_activated('gst_system')) {
            if($preorderProduct->added_by=='admin'){
                $business_info = admin_business_info();
                if ( empty($business_info) || !is_array($business_info) || empty($business_info['gstin'])) {
                    flash(translate('Please Update Your GST Information'))->warning();
                    return back();
                }
            }else{
                $shop = $preorderProduct->user->shop;
                if ($shop && !$shop->gst_verification) {
                    flash(translate('GST verification is pending for This Seller'))->warning();
                    return back();
                }
            }
        }
        $data['categories'] = Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();
        $data['product'] = $preorderProduct;
        $data['brands'] = Brand::all();
        $data['prepayment'] = $preorderProduct->preorder_prepayment;
        $data['sample_order'] = $preorderProduct->preorder_sample_order;
        $data['coupon'] = $preorderProduct->preorder_coupon;
        $data['refund'] = $preorderProduct->preorder_refund;
        $data['cod'] = $preorderProduct->preorder_cod;
        $data['discount'] = $preorderProduct->preorder_discount;
        $data['shipping'] = $preorderProduct->preorder_shipping;
        $data['stock'] = $preorderProduct->preorder_stock;
        $data['taxes'] = $preorderProduct->preorder_product_taxes;
        $data['lang'] = $request->lang;
        return view('preorder.backend.products.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PreorderProduct $preorderProduct)
    {
        // dd($request->all());

        $this->validate($request, [
            'product_name' => ['required'],
            'category_id' => ['required'],
            'unit' => ['required'],
            'unit_price' => ['required'],
            'coupon_amount' => ['nullable', 'numeric', function ($attribute, $value, $fail) use ($request) {
                                if ($request->coupon_type == 'percent' && $value > 100) {
                                    $fail(__('The coupon amount must not exceed 100% when the coupon type is percent.'));
                                }
                                if ($request->coupon_type == 'flat' && $value >= $request->unit_price) {
                                    $fail(__('The coupon amount must be less than the unit price for flat type.'));
                                }
                            },
                        ],
            'discount' => ['nullable','numeric',function ($attribute, $value, $fail) use ($request) {
                            if ($request->discount_type == 'percent' && $value > 100) {
                                $fail(__('The discount must not exceed 100% when the discount type is percent.'));
                            }
                            if ($request->discount_type != 'percent' && $value >= $request->unit_price) {
                                $fail(__('The discount must be less than the unit price.'));
                            }
                        },
                    ],
            'prepayment_amount' => $request->is_prepayment ? 'required|lt:unit_price' : 'sometimes',
            'date_range' => $request->date_range ? ['required','string','regex:/^(\d{2}-\d{2}-\d{4} \d{2}:\d{2}:\d{2}) to (\d{2}-\d{2}-\d{4} \d{2}:\d{2}:\d{2})$/'] : 'nullable',
        ]);

        // Product Update
        $this->preorderService->productUpdate($request, $preorderProduct);

        flash(translate('Preorder product has been updated successfully'))->success();
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        (new PreorderService)->productdestroy($id);

        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        flash(translate('Preorder Product has been deleted successfully'))->success();
        return back();
    }

    public function bulkProductDestroy(Request $request)
    {
        if ($request->product_ids) {
            foreach ($request->product_ids  as $product_id) {
                (new PreorderService)->productdestroy($product_id);
            }
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
        }
        return 1;
    }


    public function product_search(Request $request)
    {
        $auth_user      = auth()->user();
        $products       = PreorderProduct::query();
        $products =    $products->where('is_published', 1)
        ->where(function ($query) {
            $query->whereHas('user', function ($q) {
                $q->where('user_type', 'admin');
            })->orWhereHas('user.shop', function ($q) {
                $q->where('verification_status', 1);
            });
        });
    
        if($request->category != null ) {
            $category = Category::with('childrenCategories')->find($request->category);
            $products = $category->preorderProducts();
        }
        
        $products = in_array($auth_user->user_type, ['admin', 'staff']) ? 
                    $products->where('preorder_products.user_id', get_admin()->id) : 
                    $products->where('preorder_products.user_id', $auth_user->id);
        $products->whereIsPublished(1);
        
        if ($request->search_key != null) {
            $search_key = $request->search_key;
            $products->where('product_name', 'like', '%' . $search_key . '%')
                ->orWhereHas('preorder_product_translations', function ($q) use ($search_key) {
                    $q->where('product_name', 'like', '%' . $search_key . '%');
                });
        }    

        $products =  $products->limit(20)->get();

        return view('preorder.common.pre_order_product_search', compact('products'));
    }

    public function get_selected_products(Request $request)
    {
        $products = PreorderProduct::whereIn('id', $request->product_ids)->get();

        return  view('preorder.common.pre_order_selected_product', compact('products'));
    }

    public function preorder_product_published(Request $request)
    {
        $product = PreorderProduct::findOrFail($request->id);

        if (addon_is_activated('gst_system')) {
            if($product->added_by=='admin'){
                $business_info = admin_business_info();
                if ( empty($business_info) || !is_array($business_info) || empty($business_info['gstin'])) {
                   return 3;
                }
                if($product->gst_rate==''|| $product->gst_rate==null || $product->hsn_code=='' || $product->hsn_code==null){
                    return 4;
                }
            }else{
                $shop = $product->user->shop;
                if ($shop && !$shop->gst_verification) {
                    return 3;
                }
                if($product->gst_rate==''|| $product->gst_rate==null || $product->hsn_code=='' || $product->hsn_code==null){
                    return 4;
                }
            }
        }
        $product->is_published = $request->status;
        $product->save();

        Artisan::call('view:clear');
        Artisan::call('cache:clear');
        return 1;
    }

    public function preorder_product_featured(Request $request)
    {
        $product = PreorderProduct::findOrFail($request->id);
        $product->is_featured = $request->status;
        if ($product->save()) {
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            return 1;
        }
        return 0;
    }

    public function preorder_product_show_on_homepage(Request $request)
    {
        $product = PreorderProduct::findOrFail($request->id);
        $product->is_show_on_homepage = $request->status;
        if ($product->save()) {
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            return 1;
        }
        return 0;
    }

   public function all_preorder_products(Request $request, $category_id = null, $brand_id = null)
    {
        session(['preorder_all_page' => 'preorder_product']);
        return redirect()->route('search', $request->all());
    }


    public function listingByCategory(Request $request, $category_slug)
    {
        $category = Category::where('slug', $category_slug)->first();
        if ($category != null) {
            return $this->all_preorder_products($request, $category->id);

        }
        abort(404);
    }
    public function how_to_preorder(Request $request)
    {
        $faqs = Faq::all();
        return view('preorder.frontend.how_to_preorder', compact('faqs'));
    }

    public function categoryProductCount($category, $productCounts, $childrenKey = 'childrenCategories')
    {

        $category->products_count = $productCounts[$category->id] ?? 0;

        // If children exist, loop recursively
        if (!empty($category->{$childrenKey})) {
            foreach ($category->{$childrenKey} as $child) {
                $this->categoryProductCount($child, $productCounts, $childrenKey);
            }
        }
    }
}
