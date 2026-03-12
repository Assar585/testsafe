<?php

namespace App\Services;

use App\Models\FrequentlyBoughtProduct;
use DB;

class FrequentlyBoughtProductService
{
    public function store(array $data)
    {
        $collection = collect($data);
        
        $selection_type = $collection->get('frequently_bought_selection_type');
        $product_id = $collection->get('product_id');

        if($selection_type == 'product' && $collection->get('fq_bought_product_ids')) {
            foreach($collection->get('fq_bought_product_ids') as $fq_product){
                FrequentlyBoughtProduct::insert([
                    'product_id' => $product_id,
                    'frequently_bought_product_id' => $fq_product,
                ]);
            }
        }
        elseif($selection_type == 'category' && $collection->get('fq_bought_product_category_id')) {
            FrequentlyBoughtProduct::insert([
                'product_id' => $product_id,
                'category_id' => $collection->get('fq_bought_product_category_id'),
            ]);
        }
        
    }

    public function product_duplicate_store($frequently_bought_products, $product_new)
    {
        foreach ($frequently_bought_products as $fqb_product) {
            FrequentlyBoughtProduct::insert([
                'product_id' => $product_new->id,
                'frequently_bought_product_id' => $fqb_product->frequently_bought_product_id,
                'category_id' => $fqb_product->category_id,
            ]);
        }
    }
}