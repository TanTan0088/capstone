<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $readings = [];
        $latestReading = null;
        $connectionStatus = 'Disconnected';

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
                        'select' => 'id,device_id,flow_rate,status,recommendation,recorded_at',
                        'order' => 'recorded_at.desc',
                        'limit' => 12,
                    ]
                );

            $response->throw();

            $readings = $response->json();
            $latestReading = $readings[0] ?? null;
            $connectionStatus = 'Connected';

        } catch (\Throwable $error) {
            Log::error('Supabase connection failed: ' . $error->getMessage());
        }

        $chartReadings = collect($readings)->reverse()->values();

        $dashboardData = [
            'flow_rate' => $latestReading
                ? number_format((float) $latestReading['flow_rate'], 1)
                : '0.0',

            'status' => $latestReading['status'] ?? 'Offline',

            'wifi_status' => 'Wi-Fi Network Connected',

            'cloud_status' => $connectionStatus,

            'last_updated' => $latestReading
                ? Carbon::parse($latestReading['recorded_at'])
                    ->timezone(config('app.timezone'))
                    ->format('F d, Y - h:i A')
                : 'No data received yet',

          'recommendation' => $latestReading['recommendation']
    ?? 'No irrigation recommendation available yet.',

'next_schedule' => 'No scheduled watering recommendation yet.',

            'previous_records' => collect($readings)
                ->take(5)
                ->map(function ($reading) {
                    return [
                        'datetime' => Carbon::parse($reading['recorded_at'])
                            ->timezone(config('app.timezone'))
                            ->format('F d, Y; h:i A'),

                        'flow_rate' => number_format((float) $reading['flow_rate'], 1) . ' L/min',

                        'status' => $reading['status'],
                    ];
                })
                ->all(),

            'schedules' => [
                [
                    'date' => 'Pending',
                    'time' => 'Fuzzy logic schedule will appear here.',
                ],
            ],

            'chart_labels' => $chartReadings
                ->map(function ($reading) {
                    return Carbon::parse($reading['recorded_at'])
                        ->timezone(config('app.timezone'))
                        ->format('h:i A');
                })
                ->all(),

            'chart_values' => $chartReadings
                ->map(function ($reading) {
                    return (float) $reading['flow_rate'];
                })
                ->all(),
        ];

        return view('dashboard', compact('dashboardData'));
    }
}