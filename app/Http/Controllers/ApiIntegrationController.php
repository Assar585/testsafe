<?php

namespace App\Http\Controllers;

use App\Models\ApiIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class ApiIntegrationController extends Controller
{
    /**
     * List all integrations grouped by category.
     */
    public function index()
    {
        $integrations = ApiIntegration::orderBy('category')->orderBy('service_name')->get()->groupBy('category');
        return view('backend.api_integrations.index', compact('integrations'));
    }

    /**
     * Save or update an integration.
     */
    public function store(Request $request)
    {
        $request->validate([
            'service_name' => 'required|string|max:100',
            'category' => 'required|string|max:50',
        ]);

        $integration = ApiIntegration::firstOrNew([
            'service_name' => $request->service_name,
            'category' => $request->category,
        ]);

        $integration->label = $request->label ?? $request->service_name;
        $integration->api_url = $request->api_url;
        $integration->extra_data = $request->extra_data;
        $integration->is_active = $request->has('is_active') ? 1 : $integration->is_active;

        // Only update key if a new non-empty/non-masked value was submitted
        if ($request->filled('api_key') && $request->api_key !== '••••••••') {
            $integration->api_key = Crypt::encryptString($request->api_key);
        }
        if ($request->filled('api_secret') && $request->api_secret !== '••••••••') {
            $integration->api_secret = Crypt::encryptString($request->api_secret);
        }

        $integration->save();

        flash(translate('Integration saved successfully.'))->success();
        return back();
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggle(ApiIntegration $apiIntegration)
    {
        $apiIntegration->is_active = !$apiIntegration->is_active;
        $apiIntegration->save();
        return response()->json(['status' => $apiIntegration->is_active]);
    }

    /**
     * Test the connection to an API endpoint.
     */
    public function test_connection(ApiIntegration $apiIntegration)
    {
        if (empty($apiIntegration->api_url)) {
            return response()->json(['success' => false, 'message' => translate('No API URL configured.')]);
        }
        try {
            $key = $apiIntegration->api_key ? Crypt::decryptString($apiIntegration->api_key) : null;
            $response = Http::timeout(10)->withHeaders(
                $key ? ['Authorization' => 'Bearer ' . $key] : []
            )->get($apiIntegration->api_url);

            if ($response->successful()) {
                return response()->json(['success' => true, 'message' => translate('Connection successful! Status: ') . $response->status()]);
            }
            return response()->json(['success' => false, 'message' => translate('Connection failed. Status: ') . $response->status()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Delete an integration.
     */
    public function destroy(ApiIntegration $apiIntegration)
    {
        $apiIntegration->delete();
        flash(translate('Integration removed.'))->success();
        return back();
    }
}
