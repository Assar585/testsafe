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
        $integration->is_active = $request->has('is_active') ? 1 : ($integration->is_active ?? 0);

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
     * Test the connection to an API endpoint dynamically.
     */
    public function test_connection(ApiIntegration $apiIntegration)
    {
        if (empty($apiIntegration->api_url)) {
            return response()->json(['success' => false, 'message' => translate('No API URL configured.')]);
        }

        try {
            $key = $apiIntegration->api_key ? Crypt::decryptString($apiIntegration->api_key) : null;
            $url = rtrim($apiIntegration->api_url, '/');
            $headers = [];
            $method = 'get';

            // Dynamic routing based on the service name
            switch ($apiIntegration->service_name) {
                case 'openai':
                    $url .= '/models';
                    if ($key)
                        $headers['Authorization'] = 'Bearer ' . $key;
                    break;

                case 'gemini':
                    // Gemini uses query parameter for API key, not headers
                    $url = str_replace('/v1', '', $url); // Strip trailing /v1 if accidentally included
                    $url = str_replace('/v1beta', '', $url);
                    $url .= '/v1beta/models' . ($key ? "?key={$key}" : '');
                    break;

                case 'claude':
                case 'anthropic':
                    $url .= '/v1/models';
                    if ($key) {
                        $headers['x-api-key'] = $key;
                        $headers['anthropic-version'] = '2023-06-01'; // Required by Anthropic
                    }
                    break;

                case 'stripe':
                    $url .= '/charges';
                    // Stripe uses Basic Auth with key as username
                    if ($key)
                        $headers['Authorization'] = 'Basic ' . base64_encode($key . ':');
                    break;

                default:
                    // Generic fallback: Assume Bearer token on base URL
                    if ($key)
                        $headers['Authorization'] = 'Bearer ' . $key;
                    break;
            }

            $response = Http::timeout(10)->withHeaders($headers);

            if ($method === 'get') {
                $response = $response->get($url);
            } else {
                $response = $response->post($url);
            }

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => translate('Connection successful! Status: ') . $response->status()
                ]);
            }

            // Helpful message for 401/403/404s
            $errorMsg = translate('Connection failed. Status: ') . $response->status();
            if ($response->status() === 401 || $response->status() === 403) {
                $errorMsg .= ' - ' . translate('Invalid API Key or Secret/Token.');
            }
            if ($response->status() === 404) {
                $errorMsg .= ' - ' . translate('Endpoint not found. Check the API URL.');
            }

            return response()->json(['success' => false, 'message' => $errorMsg]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => translate('Connection error: ') . $e->getMessage()]);
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
