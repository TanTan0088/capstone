<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart Water Discharge Monitoring Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            background: #bfbfbf;
            min-height: 100vh;
            padding: 25px;
        }

        .title {
            text-align: center;
            font-size: 28px;
            letter-spacing: 1px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .dashboard-container {
            max-width: 1100px;
            margin: auto;
            background: #f5fff1;
            border: 2px solid #222;
            border-radius: 15px;
            padding: 18px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 15px;
            margin-bottom: 15px;
        }

        .logout-btn {
            background: #222;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            cursor: pointer;
        }

        .user-type {
            font-weight: bold;
        }

        .grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 18px;
        }

        .left-top {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 18px;
        }

        .card {
            background: white;
            border: 2px solid #222;
            border-radius: 15px;
            overflow: hidden;
        }

        .card-header {
            padding: 10px;
            text-align: center;
            border-bottom: 2px solid #222;
            font-weight: bold;
            font-size: 18px;
        }

        .card-body {
            padding: 20px;
            text-align: center;
        }

        .big-value {
            font-size: 34px;
            font-weight: 500;
        }

        .status-normal {
            font-size: 34px;
            color: #222;
        }

        .check-icon {
            color: #333;
            font-size: 30px;
        }

        .chart-card {
            padding: 15px;
        }

        .chart-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .table-card {
            margin-top: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-size: 17px;
        }

        th {
            border-bottom: 2px solid #222;
            padding: 8px;
        }

        td {
            padding: 14px 8px;
        }

        .right-column {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .connection-card {
            background: white;
            border: 2px solid #222;
            border-radius: 15px;
            padding: 12px;
        }

        .connection-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cloud-status {
            border: 2px solid #222;
            border-radius: 12px;
            overflow: hidden;
            margin-top: 10px;
        }

        .cloud-header {
            border-bottom: 2px solid #222;
            padding: 8px;
            text-align: center;
            font-weight: bold;
            font-size: 18px;
        }

        .cloud-body {
            padding: 12px;
            text-align: center;
            font-size: 13px;
        }

        .schedule-card {
            background: white;
            border: 2px solid #222;
            border-radius: 15px;
            padding: 15px;
        }

        .schedule-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 17px;
        }

        .next-schedule {
            font-size: 15px;
            margin-bottom: 12px;
        }

        .ack-btn {
            background: #67c46a;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 9px 25px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 15px;
        }

        .schedule-table {
            width: 100%;
            font-size: 13px;
        }

        .schedule-table td {
            padding: 6px;
            border-bottom: 1px solid #ccc;
        }

        .icon {
            font-size: 28px;
        }

        @media (max-width: 900px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .left-top {
                grid-template-columns: 1fr;
            }

            .title {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>

    <h1 class="title">SMART WATER DISCHARGE MONITORING DASHBOARD</h1>

    <div class="dashboard-container">

        <div class="top-bar">
            <div>
                Today: <span id="currentDateTime"></span>
            </div>

            <div class="user-type">
                @if(Auth::check())
                    Logged in as: {{ Auth::user()->name }}
                @elseif(session('guest_user'))
                    Logged in as: Guest
                @endif
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="logout-btn" type="submit">Logout</button>
            </form>
        </div>

        <div class="grid">

            <div class="left-column">

                <div class="left-top">
                    <div class="card">
                        <div class="card-header">Water Flow Rate</div>
                        <div class="card-body">
                            <div class="big-value">{{ $dashboardData['flow_rate'] }} L/min</div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">Current Status</div>
                        <div class="card-body">
                            <span class="status-normal">{{ $dashboardData['status'] }}</span>
                            <span class="check-icon">✔</span>
                        </div>
                    </div>
                </div>

                <div class="card chart-card">
                    <div class="chart-title">Water Flow Over Time</div>
                    <canvas id="flowChart" height="110"></canvas>
                </div>

                <div class="card table-card">
                    <div class="card-header">Previous Water Flow Rate</div>

                    <table>
                        <thead>
                            <tr>
                                <th>DateTime</th>
                                <th>Water Flow Rate</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dashboardData['previous_records'] as $record)
                                <tr>
                                    <td>{{ $record['datetime'] }}</td>
                                    <td>{{ $record['flow_rate'] }}</td>
                                    <td>{{ $record['status'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>

            <div class="right-column">

                <div class="connection-card">
                    <div class="connection-title">
                        <span class="icon">◔</span>
                        {{ $dashboardData['wifi_status'] }}
                    </div>

                    <div class="cloud-status">
                        <div class="cloud-header">☁ Cloud Database Status</div>
                        <div class="cloud-body">
                            <h3>{{ strtoupper($dashboardData['cloud_status']) }}</h3>
                            <p>Data sent to cloud database.</p>
                            <p>Last Updated: {{ $dashboardData['last_updated'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="schedule-card">
                    <div class="schedule-title">
                        <span class="icon">🗓</span>
                        Scheduled Watering Recommendation
                    </div>

                    <div class="next-schedule">
                        <strong>Next Watering Schedule:</strong><br>
                        {{ $dashboardData['next_schedule'] }}
                    </div>

                    <center>
                        <button class="ack-btn">ACKNOWLEDGE</button>
                    </center>

                    <table class="schedule-table">
                        <tbody>
                            @foreach($dashboardData['schedules'] as $schedule)
                                <tr>
                                    <td>{{ $schedule['date'] }}</td>
                                    <td>{{ $schedule['time'] }}</td>
                                    <td>✓</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>

    <script>
        function updateDateTime() {
            const now = new Date();
            const options = {
                month: 'long',
                day: 'numeric',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };

            document.getElementById('currentDateTime').textContent =
                now.toLocaleString('en-US', options);
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);

        const ctx = document.getElementById('flowChart');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($dashboardData['chart_labels']),
                datasets: [{
                    label: 'Flow Rate (L/min)',
                    data: @json($dashboardData['chart_values']),
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

</body>
</html>