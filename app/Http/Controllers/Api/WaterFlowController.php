<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WaterFlowController extends Controller
{
    public function store(Request $request)
    {
        $expectedDeviceKey = (string) config('services.esp32.device_key');
        $receivedDeviceKey = (string) $request->header('X-Device-Key');

        if (
            empty($expectedDeviceKey) ||
            empty($receivedDeviceKey) ||
            !hash_equals($expectedDeviceKey, $receivedDeviceKey)
        ) {
            return response()->json([
                'message' => 'Unauthorized device.',
            ], 401);
        }

        $data = $request->validate([
            'device_id' => 'nullable|string|max:50',
            'flow_rate' => 'required|numeric|min:0|max:100000',
        ]);

        $flowRate = (float) $data['flow_rate'];

        // Temporary rule-based values muna.
        // Papalitan natin ito later with real Fuzzy Logic rules.
        $status = $this->determineStatus($flowRate);
        $recommendation = $this->generateRecommendation($status);

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->asJson()
                ->withHeaders([
                    'apikey' => config('services.supabase.secret_key'),
                    'Authorization' => 'Bearer ' . config('services.supabase.secret_key'),
                    'Prefer' => 'return=representation',
                ])
                ->post(
                    rtrim(config('services.supabase.url'), '/') .
                    '/rest/v1/water_flow_readings',
                    [
                        'device_id' => $data['device_id'] ?? 'ESP32-001',
                        'flow_rate' => $flowRate,
                        'status' => $status,
                        'recommendation' => $recommendation,
                    ]
                );

            $response->throw();

            return response()->json([
                'message' => 'Water flow reading saved successfully.',
                'status' => $status,
                'recommendation' => $recommendation,
                'data' => $response->json(),
            ], 201);

        } catch (\Throwable $error) {
            Log::error('Failed to save ESP32 water flow reading: ' . $error->getMessage());

            return response()->json([
                'message' => 'Could not save the reading to Supabase.',
            ], 502);
        }
    }

    private function determineStatus(float $flowRate): string
    {
        // Temporary thresholds only.
        // Adjust these after sensor testing and NIA validation.
        if ($flowRate < 8.0) {
            return 'Low';
        }

        if ($flowRate > 18.0) {
            return 'High';
        }

        return 'Normal';
    }

    private function generateRecommendation(string $status): string
    {
        return match ($status) {
            'Low' => 'Low discharge detected. Check the water source, valve, or possible canal blockage.',
            'High' => 'High discharge detected. Reduce the water release and inspect for possible overflow.',
            default => 'Water discharge is within the normal range. Continue regular irrigation monitoring.',
        };
    }
}