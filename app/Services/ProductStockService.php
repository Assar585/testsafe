<?php

namespace App\Services;

use AizPackages\CombinationGenerate\Services\CombinationService;
use App\Models\ProductStock;
use App\Utility\ProductUtility;
use Illuminate\Support\Facades\Log;

class ProductStockService
{
    public function store(array $data, $product)
    {
        //Log::info('Product Stock Request:', $data);
        $collection = collect($data);

        $options = ProductUtility::get_attribute_options($collection);
        
        //Generates the combinations of customer choice options
        $combinations = (new CombinationService())->generate_combination($options);
        
        $variant = '';
        if (count($combinations) > 0) {
            $product->variant_product = 1;
            $product->save();
            foreach ($combinations as $key => $combination) {
                $str = ProductUtility::get_combination_string($combination, $collection);
                $product_stock = new ProductStock();
                $product_stock->product_id = $product->id;
                $product_stock->variant = $str;
                
                // Use request() helper as fallback if missing in $data, but ideally it should be in $data
                $req = request();
                $product_stock->price = $req['price_' . str_replace('.', '_', $str)] ?? $collection->get('unit_price', 0);
                $product_stock->sku = $req['sku_' . str_replace('.', '_', $str)] ?? null;
                $product_stock->qty = $req['qty_' . str_replace('.', '_', $str)] ?? $collection->get('current_stock', 0);
                $product_stock->image = $req['img_' . str_replace('.', '_', $str)] ?? null;
                $product_stock->save();
            }
        } else {
            $product->variant_product = 0;
            $product->save();
            
            $qty = $collection->get('current_stock', 0);
            $price = $collection->get('unit_price', 0);
            
            // Clean up to avoid issues in create()
            $filtered_data = $collection->except(['colors_active', 'colors', 'choice_no', 'current_stock', 'unit_price'])->toArray();

            $data_to_create = array_merge($filtered_data, [
                'variant' => '',
                'qty' => $qty,
                'price' => $price
            ]);
            
            ProductStock::create($data_to_create);
        }
    }

    public function product_duplicate_store($product_stocks , $product_new)
    {
        foreach ($product_stocks as $key => $stock) {
            $product_stock              = new ProductStock;
            $product_stock->product_id  = $product_new->id;
            $product_stock->variant     = $stock->variant;
            $product_stock->price       = $stock->price;
            $product_stock->sku         = null;
            $product_stock->qty         = $stock->qty;
            $product_stock->save();
        }
    }
}
