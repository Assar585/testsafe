<?php

namespace App\Http\Controllers\Seller;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductTax;
use App\Models\ProductTranslation;
use App\Models\Upload;
use App\Models\User;
use App\Notifications\ShopProductNotification;
use App\Services\ProductService;
use App\Services\ProductStockService;
use App\Services\ProductTaxService;
use App\Services\FrequentlyBoughtProductService;
use Artisan;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class DigitalProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $products = Product::where('user_id', Auth::user()->id)->where('digital', 1)->orderBy('created_at', 'desc')->paginate(10);
        return view('seller.product.digitalproducts.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (addon_is_activated('seller_subscription')) {
            if (!seller_package_validity_check()) {
                flash(translate('Please upgrade your package.'))->warning();
                return back();
            }
        }
        if (addon_is_activated('gst_system')) {
            $shop = Auth::user()->shop;
            if ($shop && !$shop->gst_verification) {
                flash(translate('GST verification is pending for your account.'))->warning();
                return back();
            }
        }
        $categories = Category::where('parent_id', 0)
            ->where('digital', 1)
            ->with('childrenCategories')
            ->get();
        return view('seller.product.digitalproducts.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        if (addon_is_activated('seller_subscription')) {
            if (!seller_package_validity_check()) {
                flash(translate('Please upgrade your package.'))->warning();
                return redirect()->route('seller.digitalproducts');
            }
        }

        if (addon_is_activated('gst_system')) {
            $shop = Auth::user()->shop;
            if ($shop && !$shop->gst_verification) {
                flash(translate('GST verification is pending for your account.'))->warning();
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
        $lang = $request->lang ?? env('DEFAULT_LANGUAGE', 'en');
        $request->merge(['lang' => $lang]);
        ProductTranslation::create($request->only([
            'lang',
            'name',
            'description',
            'product_id'
        ]));

        if (get_setting('product_approve_by_admin') == 1) {
            $users = User::findMany(User::where('user_type', 'admin')->first()->id);
            $data = array();
            $data['product_type'] = 'digital';
            $data['status'] = 'pending';
            $data['product'] = $product;
            $data['notification_type_id'] = get_notification_type('seller_product_upload', 'type')->id;

            Notification::send($users, new ShopProductNotification($data));
        }

        try {
            flash(translate('Digital Product has been inserted successfully'))->success();
            Artisan::call('view:clear');
            Artisan::call('cache:clear');

            return response()->json([
                'success' => true,
                'message' => translate('Digital Product has been inserted successfully'),
                'redirect' => route('seller.digitalproducts')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => translate('Something went wrong: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $categories = Category::where('digital', 1)->get();
        $lang = $request->lang;
        $product = Product::find($id);
        return view('seller.product.digitalproducts.edit', compact('categories', 'product', 'lang'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(ProductRequest $request, Product $product)
    {
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
        $lang = $request->lang ?? env('DEFAULT_LANGUAGE', 'en');
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
                'redirect' => route('seller.digitalproducts')
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
            $decrypted_id = decrypt($request->id);
            \Log::info("Digital Download attempt", [
                'user_id' => Auth::user()->id,
                'decrypted_id' => $decrypted_id
            ]);

            $product = Product::findOrFail($decrypted_id);
            \Log::info("Product found", [
                'product_id' => $product->id,
                'product_user_id' => $product->user_id,
                'digital' => $product->digital,
                'file_name' => $product->file_name
            ]);

            if (Auth::user()->id == $product->user_id) {
                if (empty($product->file_name)) {
                    \Log::error("Product file_name is empty", ['product_id' => $product->id]);
                    abort(404, "Product file not associated.");
                }

                $upload = Upload::findOrFail($product->file_name);
                \Log::info("Upload record found", [
                    'upload_id' => $upload->id,
                    'file_name' => $upload->file_name,
                    'driver' => env('FILESYSTEM_DRIVER')
                ]);

                if (env('FILESYSTEM_DRIVER') == "s3") {
                    return \Storage::disk('s3')->download($upload->file_name, $upload->file_original_name . "." . $upload->extension);
                } else {
                    $full_path = base_path('public/' . $upload->file_name);
                    \Log::info("Checking local file", ['path' => $full_path]);
                    if (file_exists($full_path)) {
                        return response()->download($full_path);
                    } else {
                        \Log::error("File does not exist on server", ['path' => $full_path]);
                        abort(404, "Physical file missing.");
                    }
                }
            } else {
                \Log::warning("Ownership mismatch", [
                    'auth_user' => Auth::user()->id,
                    'product_owner' => $product->user_id
                ]);
                abort(404, "Product does not belong to user.");
            }
        } catch (\Exception $e) {
            \Log::error("Download method exception", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            abort(404, "Exception in download.");
        }
    }

}
