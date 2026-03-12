<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductTranslation;
use Artisan;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuctionService
{
    public function store(Request $request){
        $product                  = new Product;
        $product->name            = $request->name;
        $product->added_by        = $request->added_by;

        if(Auth::user()->user_type == 'seller'){
            $product->user_id = Auth::user()->id;
            if(get_setting('product_approve_by_admin') == 1) {
                $product->approved = 0;
            }
        }
        else{
            $product->user_id = \App\Models\User::where('user_type', 'admin')->first()->id;
        }

        $product->auction_product = 1;
        $product->category_id     = $request->category_id;
        $product->brand_id        = $request->brand_id;
        $product->weight          = $request->weight;
        $product->barcode         = $request->barcode;
        $product->starting_bid    = $request->starting_bid;

        if (addon_is_activated('refund_request')) {
            if ($request->refundable != null) {
                $product->refundable = 1;
            }
            else {
                $product->refundable = 0;
            }
        }
        $product->photos = $request->photos;
        $product->thumbnail_img = $request->thumbnail_img;

        $tags = array();
        if (isset($request->tags) && $request->tags != null) {
            $raw_tags = is_array($request->tags) ? ($request->tags[0] ?? null) : $request->tags;
            if ($raw_tags != null) {
                $decoded_tags = json_decode($raw_tags);
                if (is_array($decoded_tags)) {
                    foreach ($decoded_tags as $key => $tag) {
                        if (isset($tag->value)) {
                            array_push($tags, $tag->value);
                        }
                    }
                } elseif (is_array($raw_tags)) {
                    foreach ($raw_tags as $tag) {
                        array_push($tags, $tag);
                    }
                } else {
                    $tags = explode(',', (string)$raw_tags);
                }
            }
        }
        $product->tags = implode(',', $tags);

        $product->description = $request->description;
        $product->video_provider = $request->video_provider;
        $product->video_link = $request->video_link;

        if ($request->auction_date_range != null) {
            $date_var               = explode(" to ", $request->auction_date_range);
            $product->auction_start_date = strtotime($date_var[0]);
            $product->auction_end_date   = strtotime($date_var[1]);
        }

        $product->shipping_type = $request->shipping_type;
        $product->est_shipping_days  = $request->est_shipping_days;

        if (addon_is_activated('club_point')) {
            if($request->earn_point) {
                $product->earn_point = $request->earn_point;
            }
        }
         if ($request->has('gst_rate') && addon_is_activated('gst_system')) {
            $product->taxes()->delete();
            $product->gst_rate = $request->gst_rate ? $request->gst_rate : 0.00 ;
            $product->hsn_code = $request->hsn_code ? $request->hsn_code : null;
        }

        if ($request->has('shipping_type')) {
            if($request->shipping_type == 'free'){
                $product->shipping_cost = 0;
            }
            elseif ($request->shipping_type == 'flat_rate') {
                $product->shipping_cost = $request->flat_shipping_cost;
            }
            elseif ($request->shipping_type == 'product_wise') {
                $product->shipping_cost = json_encode($request->shipping_cost);
            }
        }

        $product->meta_title = $request->meta_title;
        $product->meta_description = $request->meta_description;

        if($request->has('meta_img')){
            $product->meta_img = $request->meta_img;
        } else {
            $product->meta_img = $product->thumbnail_img;
        }

        if($product->meta_title == null) {
            $product->meta_title = $product->name;
        }

        if($product->meta_description == null) {
            $product->meta_description = strip_tags($product->description);
        }

        if($product->meta_img == null) {
            $product->meta_img = $product->thumbnail_img;
        }

        if($request->hasFile('pdf')){
            $product->pdf = $request->pdf->store('uploads/products/pdf');
        }

        $product->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->name)).'-'.Str::random(5);


        $product->colors = json_encode(array());
        $product->attributes = json_encode(array());
        $product->choice_options = json_encode(array(), JSON_UNESCAPED_UNICODE);

        if ($request->has('cash_on_delivery')) {
            $product->cash_on_delivery = 1;
        }
        if ($request->has('todays_deal')) {
            $product->todays_deal = 1;
        }
        $product->cash_on_delivery = 0;
        if ($request->cash_on_delivery) {
            $product->cash_on_delivery = 1;
        }

        $product->save();
        $request->merge(['product_id' => $product->id]);

        //Product categories
        $product->categories()->attach($request->category_ids);

        // VAT & Tax
        if ($request->tax_id) {
            (new ProductTaxService)->store($request->only([
                'tax_id', 'tax', 'tax_type', 'product_id'
            ]), $product);
        }

        flash(translate('Auction Product has been inserted successfully'))->success();

        Artisan::call('cache:clear');

        return redirect()->route('auction_products.index');
    }

    public function update(Request $request, $id){
        $product                  = Product::findOrFail($id);
        if($request->lang == env("DEFAULT_LANGUAGE")){
            $product->name            = $request->name;
            $product->description     = $request->description;
        }
        $product->category_id     = $request->category_id;
        $product->brand_id        = $request->brand_id;
        $product->weight          = $request->weight;
        $product->barcode         = $request->barcode;
        $product->starting_bid    = $request->starting_bid;

        if (addon_is_activated('refund_request')) {
            if ($request->refundable != null) {
                $product->refundable = 1;
            }
            else {
                $product->refundable = 0;
            }
        }
        $product->photos = $request->photos;
        $product->thumbnail_img = $request->thumbnail_img;

        $tags = array();
        if (isset($request->tags) && $request->tags != null) {
            $raw_tags = is_array($request->tags) ? ($request->tags[0] ?? null) : $request->tags;
            if ($raw_tags != null) {
                $decoded_tags = json_decode($raw_tags);
                if (is_array($decoded_tags)) {
                    foreach ($decoded_tags as $key => $tag) {
                        if (isset($tag->value)) {
                            array_push($tags, $tag->value);
                        }
                    }
                } elseif (is_array($raw_tags)) {
                    foreach ($raw_tags as $tag) {
                        array_push($tags, $tag);
                    }
                } else {
                    $tags = explode(',', (string)$raw_tags);
                }
            }
        }
        $product->tags = implode(',', $tags);

        $product->video_provider = $request->video_provider;
        $product->video_link = $request->video_link;

        if ($request->auction_date_range != null) {
            $date_var               = explode(" to ", $request->auction_date_range);
            $product->auction_start_date = strtotime($date_var[0]);
            $product->auction_end_date   = strtotime($date_var[1]);
        }

        $product->shipping_type = $request->shipping_type;
        $product->est_shipping_days  = $request->est_shipping_days;

        if (addon_is_activated('club_point')) {
            if($request->earn_point) {
                $product->earn_point = $request->earn_point;
            }
        }
        
        if ($request->has('gst_rate') && addon_is_activated('gst_system')) {
            $product->taxes()->delete();
            $product->gst_rate = $request->gst_rate ? $request->gst_rate : 0.00 ;
            $product->hsn_code = $request->hsn_code ? $request->hsn_code : null;
        }

        if ($request->has('shipping_type')) {
            if($request->shipping_type == 'free'){
                $product->shipping_cost = 0;
            }
            elseif ($request->shipping_type == 'flat_rate') {
                $product->shipping_cost = $request->flat_shipping_cost;
            }
            elseif ($request->shipping_type == 'product_wise') {
                $product->shipping_cost = json_encode($request->shipping_cost);
            }
        }

        $product->meta_title = $request->meta_title;
        $product->meta_description = $request->meta_description;

        if($request->has('meta_img')){
            $product->meta_img = $request->meta_img;
        } else {
            $product->meta_img = $product->thumbnail_img;
        }

        if($product->meta_title == null) {
            $product->meta_title = $product->name;
        }

        if($product->meta_description == null) {
            $product->meta_description = strip_tags($product->description);
        }

        if($product->meta_img == null) {
            $product->meta_img = $product->thumbnail_img;
        }

        if($request->hasFile('pdf')){
            $product->pdf = $request->pdf->store('uploads/products/pdf');
        }

        if ($request->has('cash_on_delivery')) {
            $product->cash_on_delivery = 1;
        }
        if ($request->has('todays_deal')) {
            $product->todays_deal = 1;
        }
        $product->cash_on_delivery = 0;
        if ($request->cash_on_delivery) {
            $product->cash_on_delivery = 1;
        }

        $product->save();

        //Category
        $product->categories()->sync($request->category_ids);

        //VAT & Tax
        if ($request->tax_id) {
            (new ProductTaxService)->store($request->only([
                'tax_id', 'tax', 'tax_type', 'product_id'
            ]), $product);
        }

        $product_translation = ProductTranslation::firstOrNew(['lang' => $request->lang, 'product_id' => $product->id]);
        $product_translation->name = $request->name;
        $product_translation->description = $request->description;
        $product_translation->save();

        flash(translate('Auction Product has been updated successfully'))->success();

        Artisan::call('cache:clear');

        return back();
    }

    public function destroy($id){
        $product = Product::findOrFail($id);
        $product->product_translations()->delete();
        $product->categories()->detach();
        $product->stocks()->delete();
        $product->taxes()->delete();

        if(Product::destroy($id)){
            flash(translate('Auction Product has been deleted successfully'))->success();
            Artisan::call('cache:clear');
            return redirect()->route('auction_products.index');
        }

        flash(translate('Something went wrong'))->error();
        return back();
    }
}
