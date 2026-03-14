<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductTax;
use App\Models\ProductTranslation;
use App\Models\Upload;
use App\Services\ProductService;
use App\Services\ProductTaxService;
use App\Services\ProductStockService;
use App\Services\FrequentlyBoughtProductService;
use Artisan;
use Log;


class DigitalProductController extends Controller
{
    public function __construct()
    {
        // Staff Permission Check
        $this->middleware(['permission:show_digital_products'])->only('index');
        $this->middleware(['permission:add_digital_product'])->only('create');
        $this->middleware(['permission:edit_digital_product'])->only('edit');
        $this->middleware(['permission:delete_digital_product'])->only('destroy');
        $this->middleware(['permission:download_digital_product'])->only('download');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search = null;
        $products = Product::query();
        $products->where('added_by', 'admin');
        if ($request->has('search')) {
            $sort_search = $request->search;
            $products = $products->where('name', 'like', '%' . $sort_search . '%');
        }
        $products = $products->where('digital', 1)->orderBy('created_at', 'desc')->paginate(10);
        $type = 'Admin';
        return view('backend.product.digital_products.index', compact('products', 'sort_search', 'type'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::where('parent_id', 0)
            ->where('digital', 1)
            ->with('childrenCategories')
            ->get();

        if (addon_is_activated('gst_system')) {
            $business_info = admin_business_info();
            if (empty($business_info) || !is_array($business_info) || empty($business_info['gstin'])) {
                flash(translate('Please Update Your GST Information'))->warning();
                return back();
            }
        }


        return view('backend.product.digital_products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        if (addon_is_activated('gst_system')) {
            $business_info = admin_business_info();
            if (empty($business_info) || !is_array($business_info) || empty($business_info['gstin'])) {
                flash(translate('Please Update Your GST Information'))->warning();
                return back();
            }
        }

        // Product Store
        $product = (new ProductService)->store($request->except([
            '_token',
            'tax_id',
            'tax',
            'tax_type'
        ]));

        $request->merge(['product_id' => $product->id, 'current_stock' => 0]);

        //Product categories
        $product->categories()->attach($request->category_ids);

        //Product Stock
        (new ProductStockService)->store($request->only([
            'unit_price',
            'current_stock',
            'product_id'
        ]), $product);

        //VAT & Tax
        if ($request->tax_id) {
            (new ProductTaxService)->store($request->only([
                'tax_id',
                'tax',
                'tax_type',
                'product_id'
            ]));
        }

        // Frequently Bought Products
        (new FrequentlyBoughtProductService)->store($request->only([
            'product_id',
            'frequently_bought_selection_type',
            'fq_bought_product_ids',
            'fq_bought_product_category_id'
        ]));

        // Product Translations
        $lang = $request->lang ?: (env('DEFAULT_LANGUAGE') ?: config('app.locale', 'en'));
        $request->merge(['lang' => $lang]);
        ProductTranslation::create($request->only([
            'lang',
            'name',
            'description',
            'product_id'
        ]));

        try {
            flash(translate('Product has been inserted successfully'))->success();

            Artisan::call('view:clear');
            Artisan::call('cache:clear');

            return response()->json([
                'success' => true,
                'message' => translate('Product has been inserted successfully'),
                'redirect' => route('digitalproducts.index')
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
    public function edit(Request $request, $id)
    {
        if (addon_is_activated('gst_system')) {
            $business_info = admin_business_info();
            if (empty($business_info) || !is_array($business_info) || empty($business_info['gstin'])) {
                flash(translate('Please Update Your GST Information'))->warning();
                return back();
            }
        }

        $lang = $request->lang;
        $product = Product::findOrFail($id);
        $categories = Category::where('parent_id', 0)
            ->where('digital', 1)
            ->with('childrenCategories')
            ->get();
        return view('backend.product.digital_products.edit', compact('product', 'lang', 'categories'));
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
        if (addon_is_activated('gst_system')) {
            $business_info = admin_business_info();
            if (empty($business_info) || !is_array($business_info) || empty($business_info['gstin'])) {
                flash(translate('Please Update Your GST Information'))->warning();
                return back();
            }
        }

        $product = Product::findOrFail($id);

        //Product Update
        $product = (new ProductService)->update($request->except([
            '_token',
            'tax_id',
            'tax',
            'tax_type'
        ]), $product);

        //Product Stock
        foreach ($product->stocks as $key => $stock) {
            $stock->delete();
        }

        $request->merge(['product_id' => $product->id, 'current_stock' => 0]);

        //Product categories
        $product->categories()->sync($request->category_ids);

        (new ProductStockService)->store($request->only([
            'unit_price',
            'current_stock',
            'product_id'
        ]), $product);

        //VAT & Tax
        if ($request->tax_id) {
            ProductTax::where('product_id', $product->id)->delete();
            (new ProductTaxService)->store($request->only([
                'tax_id',
                'tax',
                'tax_type',
                'product_id'
            ]));
        }

        // Frequently Bought Products
        $product->frequently_bought_products()->delete();
        (new FrequentlyBoughtProductService)->store($request->only([
            'product_id',
            'frequently_bought_selection_type',
            'fq_bought_product_ids',
            'fq_bought_product_category_id'
        ]));

        // Product Translations
        $lang = $request->lang ?: (env('DEFAULT_LANGUAGE') ?: config('app.locale', 'en'));
        ProductTranslation::updateOrCreate(
            ['lang' => $lang, 'product_id' => $product->id],
            $request->only(['name', 'description'])
        );

        try {
            flash(translate('Product has been updated successfully'))->success();

            Artisan::call('view:clear');
            Artisan::call('cache:clear');

            return response()->json([
                'success' => true,
                'message' => translate('Product has been updated successfully'),
                'redirect' => route('digitalproducts.index')
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
        (new ProductService)->destroy($id);

        flash(translate('Product has been deleted successfully'))->success();
        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        return back();
    }


    public function download(Request $request)
    {
        try {
            $id = decrypt($request->id);
            Log::info('Digital Download attempt (Admin)', ['decrypted_id' => $id]);

            $product = Product::findOrFail($id);
            Log::info('Product found', ['product_id' => $product->id, 'file_name_id' => $product->file_name]);

            if (empty($product->file_name)) {
                Log::error('Product file_name ID is empty', ['product_id' => $product->id]);
                flash(translate('Product file not associated.'))->error();
                return back();
            }

            $upload = Upload::find($product->file_name);
            if (!$upload) {
                Log::error('Upload record not found', ['upload_id' => $product->file_name]);
                flash(translate('Upload record not found.'))->error();
                return back();
            }

            Log::info('Upload record found', ['upload_id' => $upload->id, 'file_name' => $upload->file_name]);

            if (env('FILESYSTEM_DRIVER') == "s3") {
                return \Storage::disk('s3')->download($upload->file_name, $upload->file_original_name . "." . $upload->extension);
            } else {
                $path = base_path('public/' . $upload->file_name);
                Log::info('Checking local file', ['path' => $path]);
                if (file_exists($path)) {
                    return response()->download($path);
                } else {
                    Log::error('File does not exist on server', ['path' => $path]);
                    flash(translate('Physical file missing on server: ') . $upload->file_name)->error();
                    return back();
                }
            }
        } catch (\Exception $e) {
            Log::error('Download error: ' . $e->getMessage());
            flash(translate('Download failed: ') . $e->getMessage())->error();
            return back();
        }
    }
}
