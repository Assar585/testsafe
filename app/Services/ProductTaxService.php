<?php

namespace App\Services;

use App\Models\ProductTax;

class ProductTaxService
{
    public function store(array $data)
    {
        $collection = collect($data);

        $tax_ids = $collection->get('tax_id');
        if (is_array($tax_ids)) {
            $taxes = $collection->get('tax', []);
            $tax_types = $collection->get('tax_type', []);
            $product_id = $collection->get('product_id');

            foreach ($tax_ids as $key => $val) {
                $product_tax = new ProductTax();
                $product_tax->tax_id = $val;
                $product_tax->product_id = $product_id;
                $product_tax->tax = $taxes[$key] ?? 0;
                $product_tax->tax_type = $tax_types[$key] ?? 'amount';
                $product_tax->save();
            }
        }

    }

    public function product_duplicate_store($product_taxes , $product_new)
    {
         foreach ($product_taxes as $key => $tax) {
            $product_tax = new ProductTax;
            $product_tax->product_id = $product_new->id;
            $product_tax->tax_id = $tax->tax_id;
            $product_tax->tax = $tax->tax;
            $product_tax->tax_type = $tax->tax_type;
            $product_tax->save();
        }
    }

}
