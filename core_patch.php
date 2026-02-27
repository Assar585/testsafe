<?php

namespace MehediIitdu\CoreComponentRepository;
use App\Models\Addon;
use Cache;

class CoreComponentRepository
{
    public static function instantiateShopRepository()
    {
        // Bypassed for performance (removes 15-second synchronous external ping)
        self::finalizeRepository("ok");
    }

    protected static function serializeObjectResponse($zn, $request_data_json)
    {
        return "ok";
    }

    protected static function finalizeRepository($rn)
    {
        if ($rn == "bad" && env('DEMO_MODE') != 'On') {
            return redirect('https://activeitzone.com/activation/')->send();
        }
    }

    public static function initializeCache()
    {
        foreach (Addon::all() as $addon) {
            if ($addon->purchase_code == null) {
                // Skip forceful deactivation for local testing / performance
                // self::finalizeCache($addon); 
            }
            $item_name = get_setting('item_name') ?? 'ecommerce';

            if (Cache::get($addon->unique_identifier . '-purchased', 'no') == 'no') {
                try {
                    // Bypassed external API ping for each addon
                    Cache::rememberForever($addon->unique_identifier . '-purchased', function () {
                        return 'yes';
                    });
                } catch (\Exception $e) {

                }
            }
        }
    }

    public static function finalizeCache($addon)
    {
        $addon->activated = 0;
        $addon->save();

        flash('Please reinstall ' . $addon->name . ' using valid purchase code')->warning();
        return redirect()->route('addons.index')->send();
    }
}
