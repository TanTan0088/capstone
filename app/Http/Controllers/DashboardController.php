<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\FuzzyLogicService;


class DashboardController extends Controller
{
    private const SAMPLE_DURATION_SECONDS = 10;

    // Temporary calibration value for simulation only.
    // Later, this will be replaced by the actual value from your water test.
    private const CALIBRATION_CONSTANT = 0.0833;

    public function index(Request $request)
    {
        $readings = [];
        $cloudStatus = 'Disconnected';

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->withHeaders([
                    'apikey' => config('services.supabase.secret_key'),
                    'Authorization' => 'Bearer ' . config('services.supabase.secret_key'),
                ])
                ->get(
                    rtrim(config('services.supabase.url'), '/') . '/rest/v1/water_flow_readings',
                    [
                        'select' => 'id,device_id,flow_rate,status,recommendation,recorded_at,pulse_count,sample_duration_seconds,rpm,device_status,is_simulated',
                        'order' => 'recorded_at.desc',
                        'limit' => 20,
                    ]
                );

            $response->throw();

            $readings = $response->json();
            $cloudStatus = 'Connected';

        } catch (\Throwable $error) {
            Log::error('Supabase dashboard fetch failed: ' . $error->getMessage());
        }

        $latestReading = $readings[0] ?? null;

        $chartReadings = collect($readings)
            ->reverse()
            ->values();

        $dashboardData = [
            'flow_rate' => $latestReading
                ? number_format((float) $latestReading['flow_rate'], 1)
                : '0.0',

            'status' => $latestReading['status'] ?? 'Offline',

            'device_status' => $latestReading['device_status'] ?? 'Offline',

            'pulse_count' => $latestReading['pulse_count'] ?? 0,

            'sample_duration_seconds' => $latestReading['sample_duration_seconds']
                ?? self::SAMPLE_DURATION_SECONDS,

            'rpm' => $latestReading
                ? number_format((float) ($latestReading['rpm'] ?? 0), 1)
                : '0.0',

            'cloud_status' => $cloudStatus,

            'last_updated' => $latestReading
                ? Carbon::parse($latestReading['recorded_at'])
                    ->timezone(config('app.timezone'))
                    ->format('F d, Y - h:i:s A')
                : 'No data received yet',

            'recommendation' => $latestReading['recommendation']
                ?? 'No irrigation recommendation available yet.',

            'is_simulated' => $latestReading['is_simulated'] ?? false,

            'previous_records' => collect($readings)
                ->take(8)
                ->map(function ($reading) {
                    return [
                        'datetime' => Carbon::parse($reading['recorded_at'])
                            ->timezone(config('app.timezone'))
                            ->format('F d, Y; h:i:s A'),

                        'flow_rate' => number_format((float) $reading['flow_rate'], 1) . ' L/min',

                        'rpm' => number_format((float) ($reading['rpm'] ?? 0), 1) . ' RPM',

                        'pulse_count' => $reading['pulse_count'] ?? 0,

                        'status' => $reading['status'] ?? 'Offline',
                    ];
                })
                ->all(),

            'chart_labels' => $chartReadings
                ->map(function ($reading) {
                    return Carbon::parse($reading['recorded_at'])
                        ->timezone(config('app.timezone'))
                        ->format('h:i:s A');
                })
                ->all(),

            'chart_values' => $chartReadings
                ->map(function ($reading) {
                    return (float) $reading['flow_rate'];
                })
                ->all(),

            'simulation_active' => $request->session()->get('simulation_active', false),

            'simulation_started_at' => $request->session()->get('simulation_started_at'),

            'samples_collected' => $request->session()->get('simulation_samples', 0),
        ];

        return view('dashboard', compact('dashboardData'));
    }

    public function startSimulation(Request $request)
    {
        $request->session()->put([
            'simulation_active' => true,
            'simulation_started_at' => now()->toIso8601String(),
            'simulation_samples' => 0,
           'simulation_last_rpm' => random_int(70, 230),
        ]);

        return response()->json([
            'message' => 'Simulation test started.',
            'started_at' => $request->session()->get('simulation_started_at'),
        ]);
    }

    public function generateSimulationReading(
    Request $request,
    FuzzyLogicService $fuzzyLogic
)
    {
        if (!$request->session()->get('simulation_active', false)) {
            return response()->json([
                'message' => 'Simulation is not active.',
            ], 409);
        }

        $lastRpm = (float) $request->session()->get(
            'simulation_last_rpm',
            random_int(120, 160)
        );

        // Creates a smooth, changing simulated propeller speed.
      $rpm = max(0, min(250, $lastRpm + random_int(-35, 35)));

        // Occasionally simulate no water flow.
        if (random_int(1, 25) === 1) {
            $rpm = 0;
        }

        $sampleDuration = self::SAMPLE_DURATION_SECONDS;

        // One magnet = one pulse = one propeller rotation.
        $pulseCount = (int) round(($rpm / 60) * $sampleDuration);

        $flowRate = round($rpm * self::CALIBRATION_CONSTANT, 1);

        $fuzzyResult = $fuzzyLogic->analyze($flowRate);

$status = $fuzzyResult['status'];
$deviceStatus = $rpm <= 0 ? 'No Water Flow' : 'Live Monitoring';
$recommendation = $fuzzyResult['recommendation'];

        $payload = [
            'device_id' => 'SIM-ESP32-001',
            'flow_rate' => $flowRate,
            'status' => $status,
            'recommendation' => $recommendation,
            'pulse_count' => $pulseCount,
            'sample_duration_seconds' => $sampleDuration,
            'rpm' => round($rpm, 1),
            'device_status' => $deviceStatus,
            'is_simulated' => true,
        ];

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
                    rtrim(config('services.supabase.url'), '/') . '/rest/v1/water_flow_readings',
                    $payload
                );

            $response->throw();

            $savedData = $response->json();
            $savedReading = is_array($savedData) && isset($savedData[0])
                ? $savedData[0]
                : $payload;

            $request->session()->put('simulation_last_rpm', $rpm);
            $request->session()->increment('simulation_samples');

            $recordedAt = Carbon::parse(
                $savedReading['recorded_at'] ?? now()
            )->timezone(config('app.timezone'));

            return response()->json([
                'message' => 'Simulated reading saved successfully.',
                'samples_collected' => $request->session()->get('simulation_samples', 0),
                'reading' => [
                    'flow_rate' => (float) $flowRate,
                    'rpm' => (float) $rpm,
                    'pulse_count' => $pulseCount,
                    'sample_duration_seconds' => $sampleDuration,
                    'status' => $status,
                    'device_status' => $deviceStatus,
                    'recommendation' => $recommendation,
                    'is_simulated' => true,
                    'recorded_at' => $recordedAt->toIso8601String(),
                    'time_label' => $recordedAt->format('h:i:s A'),
                    'datetime_label' => $recordedAt->format('F d, Y; h:i:s A'),
                ],
            ]);

        } catch (\Throwable $error) {
            Log::error('Simulation data save failed: ' . $error->getMessage());

            return response()->json([
                'message' => 'Could not save the simulated reading to Supabase.',
            ], 502);
        }
    }

    public function stopSimulation(Request $request)
    {
        $request->session()->put('simulation_active', false);

        return response()->json([
            'message' => 'Simulation test stopped.',
        ]);
    }

    private function determineStatus(float $flowRate): string
    {
        if ($flowRate <= 0) {
            return 'Offline';
        }

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
            'Low' => 'Low discharge detected. Check the water source, canal flow, or possible blockage.',
            'High' => 'High discharge detected. Inspect for possible overflow and reduce water release if needed.',
            'Offline' => 'No water movement detected during this sample period.',
            default => 'Water discharge is within the normal range. Continue regular irrigation monitoring.',
        };
    }
}

