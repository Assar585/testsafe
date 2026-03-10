<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApiIntegration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;

class AIGeneratorController extends Controller
{
    /**
     * Generate product description and SEO tags using the active AI provider.
     */
    public function generate_description(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
        ]);

        $productName = $request->product_name;

        // Find the active AI integration
        // We look for 'ai' category integrations that are active.
        $activeAi = ApiIntegration::where('category', 'ai')->where('is_active', 1)->first();

        if (!$activeAi || empty($activeAi->api_url) || empty($activeAi->api_key)) {
            return response()->json([
                'success' => false,
                'message' => translate('No active AI service configured. Please configure OpenAI, Gemini, or Claude in API & Integrations.')
            ], 400);
        }

        try {
            $key = Crypt::decryptString($activeAi->api_key);
            $prompt = "You are an expert e-commerce copywriter. Write a compelling, premium-quality product description for: '{$productName}'. Return ONLY the raw text without markdown formatting or introductory words.";

            $generatedText = '';

            switch ($activeAi->service_name) {
                case 'gemini':
                    $url = rtrim($activeAi->api_url, '/');
                    $url = str_replace('/v1', '', $url);
                    $url = str_replace('/v1beta', '', $url);
                    // Standard text generation endpoint for Gemini Pro
                    $url .= "/v1beta/models/gemini-pro:generateContent?key={$key}";

                    $payload = [
                        'contents' => [
                            ['parts' => [['text' => $prompt]]]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.7,
                            'maxOutputTokens' => 800
                        ]
                    ];

                    $response = Http::timeout(20)->post($url, $payload);

                    if ($response->successful()) {
                        $data = $response->json();
                        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                            $generatedText = $data['candidates'][0]['content']['parts'][0]['text'];
                        }
                    } else {
                        throw new \Exception('Gemini API Error: ' . $response->body());
                    }
                    break;

                case 'openai':
                    $url = rtrim($activeAi->api_url, '/') . '/chat/completions';
                    $payload = [
                        'model' => 'gpt-3.5-turbo',
                        'messages' => [
                            ['role' => 'user', 'content' => $prompt]
                        ],
                        'temperature' => 0.7,
                        'max_tokens' => 800
                    ];

                    $response = Http::timeout(20)->withHeaders([
                        'Authorization' => 'Bearer ' . $key
                    ])->post($url, $payload);

                    if ($response->successful()) {
                        $data = $response->json();
                        if (isset($data['choices'][0]['message']['content'])) {
                            $generatedText = $data['choices'][0]['message']['content'];
                        }
                    } else {
                        throw new \Exception('OpenAI API Error: ' . $response->body());
                    }
                    break;

                case 'claude':
                case 'anthropic':
                    $url = rtrim($activeAi->api_url, '/') . '/v1/messages';
                    $payload = [
                        'model' => 'claude-3-haiku-20240307',
                        'max_tokens' => 800,
                        'messages' => [
                            ['role' => 'user', 'content' => $prompt]
                        ],
                        'temperature' => 0.7
                    ];

                    $response = Http::timeout(20)->withHeaders([
                        'x-api-key' => $key,
                        'anthropic-version' => '2023-06-01',
                        'content-type' => 'application/json'
                    ])->post($url, $payload);

                    if ($response->successful()) {
                        $data = $response->json();
                        if (isset($data['content'][0]['text'])) {
                            $generatedText = $data['content'][0]['text'];
                        }
                    } else {
                        throw new \Exception('Anthropic API Error: ' . $response->body());
                    }
                    break;

                default:
                    throw new \Exception('Unsupported AI service provider.');
            }

            if (empty(trim($generatedText))) {
                throw new \Exception('AI returned empty response.');
            }

            // Generate basic SEO metadata based on the newly generated text
            $metaTitle = mb_substr($productName . " - " . translate("Premium Quality"), 0, 60);
            // First 150 characters of description for meta desc
            $cleanText = strip_tags($generatedText);
            $metaDesc = mb_substr($cleanText, 0, 150) . '...';

            return response()->json([
                'success' => true,
                'description' => "<p>{$generatedText}</p>",
                'meta_title' => $metaTitle,
                'meta_description' => $metaDesc
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
