<?php

namespace App\Http\Controllers;

use AizPackages\CombinationGenerate\Services\CombinationService;
use App\Http\Requests\ProductDraftRequest;
use App\Http\Requests\ProductRequest;
use App\Models\BusinessSetting;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Category;
use App\Models\AttributeValue;
use App\Models\Cart;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use App\Models\Review;
use App\Models\Wishlist;
use App\Models\User;
use App\Notifications\ShopProductNotification;
use Carbon\Carbon;
use CoreComponentRepository;
use Artisan;
use Cache;
use App\Services\ProductService;
use App\Services\ProductTaxService;
use App\Services\ProductFlashDealService;
use App\Services\ProductStockService;
use App\Services\FrequentlyBoughtProductService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Str;

class ProductController extends Controller
{
    protected $productService;
    protected $productTaxService;
    protected $productFlashDealService;
    protected $productStockService;
    protected $frequentlyBoughtProductService;

    public function __construct(
        ProductService $productService,
        ProductTaxService $productTaxService,
        ProductFlashDealService $productFlashDealService,
        ProductStockService $productStockService,
        FrequentlyBoughtProductService $frequentlyBoughtProductService
    ) {
        $this->productService = $productService;
        $this->productTaxService = $productTaxService;
        $this->productFlashDealService = $productFlashDealService;
        $this->productStockService = $productStockService;
        $this->frequentlyBoughtProductService = $frequentlyBoughtProductService;

        // Staff Permission Check
        $this->middleware(['permission:add_new_product'])->only('create');
        $this->middleware(['permission:show_all_products'])->only('all_products');
        $this->middleware(['permission:show_in_house_products'])->only('admin_products');
        $this->middleware(['permission:show_seller_products'])->only('seller_products');
        $this->middleware(['permission:product_edit'])->only('admin_product_edit', 'seller_product_edit');
        $this->middleware(['permission:product_duplicate'])->only('duplicate');
        $this->middleware(['permission:product_delete'])->only('destroy');
        $this->middleware(['permission:set_category_wise_discount'])->only('categoriesWiseProductDiscount');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function all_products(Request $request)
    {
        $seller_type = 'all';
        $product_types = ['All Products', 'Inhouse Products', 'Seller Products', 'Digital Products', 'Physical Products', 'Drafts'];
        $brand_id = $request->brand_id;
        $category_id = $request->category_id;
        $back_to = $request->back_to;

        return view('backend.product.products.index', compact('seller_type', 'product_types', 'brand_id', 'category_id', 'back_to'));
    }

    public function admin_products(Request $request)
    {
        $seller_type = 'admin';
        $product_types = ['All Products', 'Physical Products', 'Digital Products', 'Drafts'];
        $brand_id = $request->brand_id;
        $category_id = $request->category_id;
        $back_to = $request->back_to;

        return view('backend.product.products.index', compact('seller_type', 'product_types', 'brand_id', 'category_id', 'back_to'));
    }

    public function seller_products(Request $request, $product_type)
    {
        $seller_type = 'seller';
        $product_types = ['All Products', 'Physical Products', 'Digital Products', 'Drafts', 'Not Approved'];
        $brand_id = $request->brand_id;
        $category_id = $request->category_id;
        $back_to = $request->back_to;

        return view('backend.product.products.index', compact('seller_type', 'product_types', 'brand_id', 'category_id', 'back_to'));
    }

    public function get_filter_products(Request $request)
    {
        $col_name = null;
        $query = null;
        $sort_search = null;
        $products = Product::where('auction_product', 0)->where('wholesale_product', 0);
        if ($request->product_type == 'drafts') {
            $products = $products->where('draft', 1)->where('added_by', 'admin');
        } else {
            $products = $products->where('draft', 0);
            if ($request->seller_type == 'admin') {
                $products = $products->where('added_by', 'admin');
            } elseif ($request->seller_type == 'seller') {
                $products = $products->where('added_by', 'seller');
                if ($request->user_id != null) {
                    $products = $products->where('user_id', $request->user_id);
                }
            }
            if ($request->product_type != 'drafts') {
                if ($request->product_type == 'digital_products') {
                    $products = $products->where('digital', 1);
                } else if ($request->product_type == 'physical_products') {
                    $products = $products->where('digital', 0);
                } else if ($request->product_type == 'not_approved') {
                    $products = $products->where('approved', 0);
                } else if ($request->product_type == 'pos_product_list') {
                    $products = $products->where('pos', 1);
                }
            }
        }

        if ($request->search != null) {
            $sort_search = $request->search;
            $products = $products
                ->where(function ($q) use ($sort_search) {
                    $q->where('name', 'like', '%' . $sort_search . '%')
                      ->orWhereHas('stocks', function ($sq) use ($sort_search) {
                          $sq->where('sku', 'like', '%' . $sort_search . '%');
                      });
                });
        }
        if ($request->type != null) {
            $var = explode(",", $request->type);
            $col_name = $var[0];
            $query = $var[1];
            $products = $products->orderBy($col_name, $query);
        }

        $filters = $request->selected_filter ?? [];
        if (!empty($filters)) {
            if (in_array('low-stock', $filters)) {
                $products->where(function ($query) {
                    $query->whereRaw("
                        (
                            SELECT CASE
                                WHEN products.variant_product = 1 
                                    THEN (SELECT SUM(qty) FROM product_stocks WHERE product_stocks.product_id = products.id)
                                ELSE 
                                    (SELECT qty FROM product_stocks WHERE product_stocks.product_id = products.id LIMIT 1)
                            END
                        ) <= products.low_stock_quantity
                    ");
                });
            }
            if (in_array('all-discount', $filters)) {
                $products->where('discount', '>', 0);
            }
            if (in_array('all-publish', $filters)) {
                $products->where('published', 1);
            }
        }
        if ($request->filled('brand_id')) {
            $products = $products->where('brand_id', $request->brand_id);
        }
        if ($request->filled('category_id')) {
            $products = $products->whereHas('categories', function ($query) use ($request) {
                $query->where('categories.id', $request->category_id);
            });
        }

        if ($request->product_type == 'pos_product_list') {
            $products = $products->orderBy('updated_at', 'desc')->paginate(15);
        } else {
            $products = $products->orderBy('created_at', 'desc')->paginate(15);
        }

        $type = $request->seller_type;
        $ptoduct_type = $request->product_type;

        $view = view(
            'backend.product.products.products_table',
            compact('products', 'type', 'col_name', 'query', 'sort_search', 'ptoduct_type')
        )->render();

        return response()->json(['html' => $view]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        CoreComponentRepository::instantiateShopRepository();

        if (addon_is_activated('seller_subscription')) {
            if (auth()->user()->user_type == 'seller' && !seller_package_validity_check()) {
                flash(translate('Please upgrade your package.'))->warning();
                return redirect()->route('seller.seller_packages_list');
            }
        }

        $categories = Category::where('parent_id', 0)
            ->with('childrenCategories')
            ->get();
        return view('backend.product.products.create', compact('categories'));
    }

    public function add_more_choice_option(Request $request)
    {
        $all_attribute_values = AttributeValue::with('attribute')->where('attribute_id', $request->attribute_id)->get();

        $html = '';

        foreach ($all_attribute_values as $row) {
            $html .= '<option value="' . $row->value . '">' . $row->value . '</option>';
        }

        echo json_encode($html);
    }

    public function hs_code_search(Request $request)
    {
        try {
            $q = strtolower(trim($request->get('q', '')));
            $debug_msg = "HS Code Search START [Admin] q: $q\n";
            @file_put_contents(public_path('debug_log.txt'), $debug_msg, FILE_APPEND);
            
            // Avoid failing due to log permissions
            // \Log::info("HS Code Search START [Admin]", ['q' => $q]);

            $results = [];
            $seen = [];
            $limit = 5;

            $jsonPaths = [
                public_path('assets/data/hs_codes_un.json'),
                base_path('resources/data/hs_codes_un.json'),
                public_path('assets/data/hs_codes.json'),
                base_path('resources/data/hs_codes.json'),
                '/app/public/assets/data/hs_codes_un.json',
                '/app/public/assets/data/hs_codes.json',
            ];

            foreach ($jsonPaths as $path) {
                if (count($results) >= $limit)
                    break;

                if (file_exists($path)) {
                    @file_put_contents(public_path('debug_log.txt'), "Checking path: $path\n", FILE_APPEND);
                    $jsonContent = @file_get_contents($path);
                    if ($jsonContent === false) {
                        @file_put_contents(public_path('debug_log.txt'), "Failed to read file: $path\n", FILE_APPEND);
                        continue;
                    }

                    // Remove BOM
                    $jsonContent = preg_replace('/^[\xEF\xBB\xBF\xFE\xFF\xFF\xFE]*/', '', $jsonContent);

                    $data = json_decode($jsonContent, true);
                    if ($data === null) {
                        @file_put_contents(public_path('debug_log.txt'), "JSON Decode Failed for $path: " . json_last_error_msg() . "\n", FILE_APPEND);
                        continue;
                    }

                    $items = isset($data['results']) ? $data['results'] : $data;
                    if (!is_array($items)) {
                        @file_put_contents(public_path('debug_log.txt'), "Data is not an array in $path\n", FILE_APPEND);
                        continue;
                    }

                    foreach ($items as $item) {
                        if (count($results) >= $limit)
                            break;

                        $id = $item['id'] ?? $item['code'] ?? '';
                        $text = $item['text'] ?? $item['desc'] ?? '';

                        if (empty($id) || isset($seen[$id]))
                            continue;

                        $id_lower = strtolower((string) $id);
                        $text_lower = strtolower((string) $text);

                        if (empty($q) || strpos($id_lower, $q) !== false || strpos($text_lower, $q) !== false) {
                            $results[] = [
                                'id' => $id,
                                'text' => $id . ' - ' . $text
                            ];
                            $seen[$id] = true;
                        }
                    }
                }
            }
            @file_put_contents(public_path('debug_log.txt'), "HS Code Search END. Found: " . count($results) . "\n", FILE_APPEND);
            return response()->json($results, 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            @file_put_contents(public_path('debug_log.txt'), "HS Code Search EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        try {
            @file_put_contents(public_path('debug_log.txt'), "Product store START\n", FILE_APPEND);
            
            // Ensure fields exist to avoid "Undefined array key"
            $request->merge([
                'flash_deal_id' => $request->flash_deal_id ?? null,
                'flash_discount' => $request->flash_discount ?? 0,
                'flash_discount_type' => $request->flash_discount_type ?? 'amount',
                'category_ids' => $request->category_ids ?? [],
                'description' => $request->description ?? '',
            ]);

            // Use except to be safe and include all fields
            $product = $this->productService->store($request->except([
                '_token',
                'sku',
                'choice',
                'tax_id',
                'tax',
                'tax_type',
                'flash_deal_id',
                'flash_discount',
                'flash_discount_type'
            ]));

            @file_put_contents(public_path('debug_log.txt'), "Product store: Product created with ID: " . $product->id . "\n", FILE_APPEND);
            
            $request->merge(['product_id' => $product->id]);

            //Product categories
            if ($request->has('category_ids')) {
                $product->categories()->attach($request->category_ids);
            }

            //VAT & Tax
            if ($request->tax_id) {
                $this->productTaxService->store($request->only([
                    'tax_id',
                    'tax',
                    'tax_type',
                    'product_id'
                ]));
            }

            // Delete other Taxes if GST Rate is updated
            if ($request->has('gst_rate') && addon_is_activated('gst_system')) {
                $product->taxes()->delete();
            }

            //Flash Deal
            $this->productFlashDealService->store($request->only([
                'flash_deal_id',
                'flash_discount',
                'flash_discount_type'
            ]), $product);

            //Product Stock
            $this->productStockService->store($request->only([
                'colors_active',
                'colors',
                'choice_no',
                'unit_price',
                'sku',
                'current_stock',
                'product_id'
            ]), $product);

            // Frequently Bought Products
            $this->frequentlyBoughtProductService->store($request->only([
                'product_id',
                'frequently_bought_selection_type',
                'fq_bought_product_ids',
                'fq_bought_product_category_id'
            ]));

            // Product Translations
            $request->merge(['lang' => $request->lang ?: (env('DEFAULT_LANGUAGE') ?: config('app.locale', 'en'))]);
            ProductTranslation::create($request->only([
                'lang',
                'name',
                'unit',
                'description',
                'product_id'
            ]));

            flash(translate('Product has been inserted successfully'))->success();
            @file_put_contents(public_path('debug_log.txt'), "Product store SUCCESS\n", FILE_APPEND);

            Artisan::call('cache:clear');

            return response()->json([
                'success' => true,
                'message' => translate('Product has been inserted successfully'),
                'redirect' => route('products.all')
            ]);
        } catch (\Exception $e) {
            @file_put_contents(public_path('debug_log.txt'), "Product store EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
            return response()->json([
                'success' => false,
                'message' => translate('Something went wrong: ') . $e->getMessage()
            ], 500);
        }
    }

    public function store_as_draft(ProductDraftRequest $request)
    {
        try {
            if (isset($request->id)) {
                $product = Product::find($request->id);
                if ($product) {
                    $product = $this->productService->update($request->except(['_token']), $product);
                }
            } else {
                $product = $this->productService->storeOrUpdateDraft($request->except(['_token']));
            }

            return response()->json([
                'success' => true,
                'product_id' => $product->id,
                'message' => translate('Product has been saved as draft successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => translate('Something went wrong: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function admin_product_edit(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        if ($product->digital == 1) {
            return redirect()->route('digitalproducts.edit', ['digitalproduct' => $id, 'lang' => $request->lang]);
        }

        CoreComponentRepository::instantiateShopRepository();
        
        $lang = $request->lang;
        $tags = json_decode($product->tags);
        $categories = Category::where('parent_id', 0)
            ->with('childrenCategories')
            ->get();
        $type = 'admin';
        return view('backend.product.products.edit', compact('product', 'categories', 'tags', 'lang', 'type'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function seller_product_edit(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        if ($product->digital == 1) {
            return redirect()->route('seller.digitalproducts.edit', ['id' => $id, 'lang' => $request->lang]);
        }

        if ($product->added_by == 'admin') {
            flash(translate('This product is added by admin. You can not edit this product.'))->warning();
            return redirect()->route('products.all');
        }
        $lang = $request->lang;
        $tags = json_decode($product->tags);
        $categories = Category::where('parent_id', 0)
            ->with('childrenCategories')
            ->get();
        $type = 'seller';
        return view('backend.product.products.edit', compact('product', 'categories', 'tags', 'lang', 'type'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductRequest $request, $id)
    {
        @file_put_contents(public_path('debug_log.txt'), "Product update START id: $id\n", FILE_APPEND);
        @file_put_contents(public_path('debug_log.txt'), "Request data: " . json_encode($request->all()) . "\n", FILE_APPEND);
        
        $product = Product::findOrFail($id);
        $product = $this->productService->update($request->except([
            '_token',
            'sku',
            'choice',
            'tax_id',
            'tax',
            'tax_type',
            'flash_deal_id',
            'flash_discount',
            'flash_discount_type'
        ]), $product);

        //Product categories
        $product->categories()->sync($request->category_ids);

        //VAT & Tax
        if ($request->tax_id) {
            $this->productTaxService->store($request->only([
                'tax_id',
                'tax',
                'tax_type',
                'product_id'
            ]));
        }

        //Flash Deal
        $this->productFlashDealService->store($request->only([
            'flash_deal_id',
            'flash_discount',
            'flash_discount_type'
        ]), $product);

        //Product Stock
        $this->productStockService->store($request->only([
            'colors_active',
            'colors',
            'choice_no',
            'unit_price',
            'sku',
            'current_stock',
            'product_id'
        ]), $product);

        // Frequently Bought Products
        $this->frequentlyBoughtProductService->store($request->only([
            'product_id',
            'frequently_bought_selection_type',
            'fq_bought_product_ids',
            'fq_bought_product_category_id'
        ]));

        try {
            $lang = $request->lang ?: (env('DEFAULT_LANGUAGE') ?: config('app.locale', 'en'));
            $product_translation = ProductTranslation::firstOrNew(['lang' => $lang, 'product_id' => $product->id]);
            $product_translation->name = $request->name;
            $product_translation->unit = $request->unit;
            $product_translation->description = $request->description;
            $product_translation->save();

            flash(translate('Product has been updated successfully'))->success();

            Artisan::call('cache:clear');

            return response()->json([
                'success' => true,
                'message' => translate('Product has been updated successfully'),
                'redirect' => route('products.all')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => translate('Something went wrong: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->productService->destroy($id);

        flash(translate('Product has been deleted successfully'))->success();

        Artisan::call('cache:clear');

        return 1;
    }

    public function bulk_product_delete(Request $request)
    {
        if ($request->id) {
            foreach ($request->id as $product_id) {
                $this->productService->destroy($product_id);
            }
        }

        flash(translate('Products has been deleted successfully'))->success();
        Artisan::call('cache:clear');
        return 1;
    }

    public function updatePublished(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->published = $request->status;
        $product->save();

        Artisan::call('cache:clear');

        return 1;
    }

    public function updateFeatured(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->featured = $request->status;
        $product->save();

        Artisan::call('cache:clear');

        return 1;
    }

    public function updateTodaysDeal(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->todays_deal = $request->status;
        $product->save();

        Artisan::call('cache:clear');

        return 1;
    }

    public function duplicate(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            $product_new = $this->productService->product_duplicate_store($product);

            Artisan::call('cache:clear');

            return response()->json([
                'success' => true,
                'message' => translate('Product has been duplicated successfully'),
                'redirect' => $product_new->added_by == 'admin' ? route('products.all') : route('seller.products.all')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => translate('Something went wrong: ') . $e->getMessage()
            ], 500);
        }
    }

    public function get_selected_products(Request $request)
    {
        $product_ids = $request->product_ids;
        $products = Product::whereIn('id', $product_ids)->get();
        return view('backend.product.products.selected_products', compact('products'));
    }

    public function product_search(Request $request)
    {
        $products = $this->productService->product_search($request->all());
        return view('backend.product.products.product_list', compact('products'));
    }

    public function categoriesWiseProductDiscount(Request $request)
    {
        $categories = Category::where('parent_id', 0)
            ->with('childrenCategories')
            ->get();
        return view('backend.product.products.category_wise_discount', compact('categories'));
    }

    public function categoriesWiseProductDiscountSet(Request $request)
    {
        $result = $this->productService->setCategoryWiseDiscount($request->all());
        if ($result == 1) {
            flash(translate('Category wise discount has been set successfully'))->success();
        } else {
            flash(translate('Something went wrong'))->error();
        }
        return redirect()->route('products.all');
    }

    public function update_cash_on_delivery(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->cash_on_delivery = $request->status;
        if ($product->save()) {
            return 1;
        }
        return 0;
    }

    public function sku_combination(Request $request)
    {
        $options = array();
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        } else {
            $colors_active = 0;
        }

        $unit_price = $request->unit_price;
        $product_name = $request->name;

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_' . $no;
                $data = array();
                foreach ($request[$name] as $key => $item) {
                    array_push($data, $item);
                }
                array_push($options, $data);
            }
        }

        $base_sku = $request->sku;
        $combinations = (new CombinationService())->generate_combination($options);
        return view('backend.product.products.sku_combinations', compact('combinations', 'unit_price', 'colors_active', 'product_name', 'base_sku'));
    }

    public function sku_combination_edit(Request $request)
    {
        $product = Product::findOrFail($request->id);

        $options = array();
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        } else {
            $colors_active = 0;
        }

        $product_name = $request->name;
        $unit_price = $request->unit_price;

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_' . $no;
                $data = array();
                foreach ($request[$name] as $key => $item) {
                    array_push($data, $item);
                }
                array_push($options, $data);
            }
        }

        $base_sku = $request->sku;
        $combinations = (new CombinationService())->generate_combination($options);
        return view('backend.product.products.sku_combinations_edit', compact('combinations', 'unit_price', 'colors_active', 'product_name', 'product', 'base_sku'));
    }

    public function check_sku_availability(Request $request)
    {
        $sku = $request->sku;
        $product_id = $request->product_id;

        $query = \App\Models\ProductStock::where('sku', $sku);
        if ($product_id) {
            $query->where('product_id', '!=', $product_id);
        }

        $exists = $query->exists();

        return response()->json(['exists' => $exists]);
    }
}

