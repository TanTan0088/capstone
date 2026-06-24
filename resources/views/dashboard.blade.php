<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart Water Discharge Monitoring Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    * {
        box-sizing: border-box;
        font-family: Arial, sans-serif;
    }

    body {
        margin: 0;
        min-height: 100vh;
        background: #dfe8e2;
        padding: 25px;
        color: #1f2933;
    }

    .title {
        text-align: center;
        font-size: 28px;
        letter-spacing: 1px;
        margin: 0 0 15px;
        font-weight: 700;
    }

    .dashboard-container {
        max-width: 1200px;
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
        gap: 12px;
        font-size: 14px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }

    .user-type {
        font-weight: bold;
    }

    .logout-btn,
    .start-btn,
    .stop-btn {
        border: none;
        border-radius: 8px;
        padding: 9px 14px;
        cursor: pointer;
        font-weight: bold;
    }

    .logout-btn {
        background: #222;
        color: white;
    }

    .start-btn {
        background: #4f9d69;
        color: white;
    }

    .stop-btn {
        background: #d9534f;
        color: white;
    }

    .start-btn:disabled,
    .stop-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .mode-banner {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        border: 2px solid #222;
        border-radius: 12px;
        padding: 12px 15px;
        margin-bottom: 18px;
        background: white;
        flex-wrap: wrap;
    }

    .mode-badge {
        display: inline-block;
        font-size: 13px;
        font-weight: bold;
        padding: 7px 10px;
        border-radius: 20px;
        border: 1px solid #222;
    }

    .mode-simulation {
        background: #fff1a8;
    }

    .mode-live {
        background: #bcecc9;
    }

    .mode-offline {
        background: #e7e7e7;
    }

    .grid {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 18px;
    }

    .left-top {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
        margin-bottom: 18px;
    }

    .test-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-bottom: 18px;
    }

    .card,
    .connection-card,
    .control-card,
    .recommendation-card {
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
        font-size: 17px;
    }

    .card-body {
        padding: 20px;
        text-align: center;
    }

    .big-value {
        font-size: 32px;
        font-weight: 600;
    }

    .status-value {
        font-size: 28px;
        font-weight: bold;
    }

    .metric-card {
        background: white;
        border: 2px solid #222;
        border-radius: 12px;
        padding: 14px;
        text-align: center;
    }

    .metric-label {
        font-size: 13px;
        font-weight: bold;
        margin-bottom: 8px;
    }

    .metric-value {
        font-size: 23px;
        font-weight: bold;
    }

    .metric-small {
        font-size: 12px;
        margin-top: 5px;
        color: #5f6c72;
    }

    .chart-card {
        padding: 15px;
    }

    .chart-title {
        text-align: center;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .table-card {
        margin-top: 18px;
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        text-align: center;
        font-size: 13px;
    }

    th {
        border-bottom: 2px solid #222;
        padding: 9px 7px;
    }

    td {
        padding: 11px 7px;
        border-bottom: 1px solid #e0e0e0;
    }

    .empty-row {
        padding: 18px;
        color: #68737b;
    }

    .right-column {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .connection-card,
    .control-card,
    .recommendation-card {
        padding: 15px;
    }

    .connection-title,
    .control-title,
    .recommendation-title {
        font-size: 17px;
        font-weight: bold;
        margin-bottom: 14px;
    }

    .cloud-status {
        border: 2px solid #222;
        border-radius: 12px;
        overflow: hidden;
    }

    .cloud-header {
        border-bottom: 2px solid #222;
        padding: 8px;
        text-align: center;
        font-weight: bold;
    }

    .cloud-body {
        padding: 12px;
        text-align: center;
        font-size: 13px;
    }

    .cloud-body h3 {
        margin: 0 0 8px;
    }

    .cloud-body p {
        margin: 6px 0;
    }

    .test-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin: 14px 0;
    }

    .test-info-box {
        border: 1.5px solid #222;
        border-radius: 10px;
        padding: 10px;
        text-align: center;
    }

    .test-info-label {
        display: block;
        font-size: 11px;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .test-info-value {
        font-size: 16px;
        font-weight: bold;
    }

    .button-group {
        display: flex;
        gap: 10px;
    }

    .button-group button {
        flex: 1;
    }

    .recommendation-text {
        line-height: 1.6;
        margin: 0;
        font-size: 14px;
    }

    .status-normal {
        color: #237a3b;
    }

    .status-low {
        color: #c27c00;
    }

    .status-high {
        color: #c43d3d;
    }

    .status-offline {
        color: #5f6c72;
    }

    .notification {
        display: none;
        margin-bottom: 15px;
        padding: 11px 13px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: bold;
    }

    .notification.success {
        display: block;
        background: #d9f5df;
        color: #1d6a32;
        border: 1px solid #77b687;
    }

    .notification.error {
        display: block;
        background: #ffe1e1;
        color: #9a2929;
        border: 1px solid #d58b8b;
    }

    @media (max-width: 950px) {
        .grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 650px) {
        body {
            padding: 12px;
        }

        .title {
            font-size: 21px;
        }

        .left-top,
        .test-grid {
            grid-template-columns: 1fr;
        }

        .big-value {
            font-size: 28px;
        }

        .top-bar {
            justify-content: center;
            text-align: center;
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

    <div id="notification" class="notification"></div>

    <div class="mode-banner">
        <div>
            <strong id="monitoringLabel">Monitoring Mode:</strong>
            <span id="modeBadge"
                class="mode-badge {{ $dashboardData['simulation_active'] ? 'mode-simulation' : 'mode-offline' }}">
                {{ $dashboardData['simulation_active'] ? 'SIMULATION TEST ACTIVE' : 'WAITING TO START TEST' }}
            </span>
        </div>

        <div>
            Latest source:
            <strong id="dataSource">
                {{ $dashboardData['is_simulated'] ? 'Simulated Data' : 'Device / Stored Data' }}
            </strong>
        </div>
    </div>

    <div class="grid">

        <div class="left-column">

            <div class="left-top">
                <div class="card">
                    <div class="card-header">Estimated Water Flow Rate</div>
                    <div class="card-body">
                        <div class="big-value" id="flowRateValue">
                            {{ $dashboardData['flow_rate'] }} L/min
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">Current Flow Status</div>
                    <div class="card-body">
                        <span id="statusValue"
                            class="status-value status-{{ strtolower($dashboardData['status']) }}">
                            {{ $dashboardData['status'] }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="test-grid">
                <div class="metric-card">
                    <div class="metric-label">PROP. SPEED</div>
                    <div class="metric-value" id="rpmValue">{{ $dashboardData['rpm'] }} RPM</div>
                    <div class="metric-small">Propeller rotations per minute</div>
                </div>

                <div class="metric-card">
                    <div class="metric-label">PULSE COUNT</div>
                    <div class="metric-value" id="pulseValue">{{ $dashboardData['pulse_count'] }}</div>
                    <div class="metric-small">
                        For <span id="sampleDurationValue">{{ $dashboardData['sample_duration_seconds'] }}</span> seconds
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-label">DEVICE STATUS</div>
                    <div class="metric-value" id="deviceStatusValue">
                        {{ $dashboardData['device_status'] }}
                    </div>
                    <div class="metric-small">Current monitoring condition</div>
                </div>
            </div>

            <div class="card chart-card">
                <div class="chart-title">Water Flow Rate Over Time</div>
                <canvas id="flowChart" height="120"></canvas>
            </div>

            <div class="card table-card">
                <div class="card-header">Recent Water Flow Readings</div>

                <table>
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Flow Rate</th>
                            <th>RPM</th>
                            <th>Pulses</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody id="readingsTableBody">
                        @forelse($dashboardData['previous_records'] as $record)
                            <tr>
                                <td>{{ $record['datetime'] }}</td>
                                <td>{{ $record['flow_rate'] }}</td>
                                <td>{{ $record['rpm'] }}</td>
                                <td>{{ $record['pulse_count'] }}</td>
                                <td>{{ $record['status'] }}</td>
                            </tr>
                        @empty
                            <tr id="emptyTableRow">
                                <td colspan="5" class="empty-row">
                                    No water-flow reading has been recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        <div class="right-column">

            <div class="connection-card">
                <div class="connection-title">☁ Cloud Database Connection</div>

                <div class="cloud-status">
                    <div class="cloud-header">Supabase Database Status</div>

                    <div class="cloud-body">
                        <h3 id="cloudStatusValue">{{ strtoupper($dashboardData['cloud_status']) }}</h3>
                        <p>Monitoring readings are saved in the cloud database.</p>
                        <p>
                            Last Updated:
                            <strong id="lastUpdatedValue">{{ $dashboardData['last_updated'] }}</strong>
                        </p>
                    </div>
                </div>
            </div>

            <div class="control-card">
                <div class="control-title">🧪 Simulation Test Control</div>

                <p style="font-size: 13px; line-height: 1.5; margin-top: 0;">
                    Use this while the ESP32 device is not yet connected. The system will generate
                    simulated propeller pulses, RPM, and estimated flow-rate readings every
                    {{ $dashboardData['sample_duration_seconds'] }} seconds.
                </p>

                <div class="test-info">
                    <div class="test-info-box">
                        <span class="test-info-label">TEST STATUS</span>
                        <span class="test-info-value" id="testStatusValue">
                            {{ $dashboardData['simulation_active'] ? 'Measuring' : 'Idle' }}
                        </span>
                    </div>

                    <div class="test-info-box">
                        <span class="test-info-label">SESSION TIME</span>
                        <span class="test-info-value" id="sessionTimer">00:00:00</span>
                    </div>

                    <div class="test-info-box">
                        <span class="test-info-label">SAMPLE INTERVAL</span>
                        <span class="test-info-value">
                            {{ $dashboardData['sample_duration_seconds'] }} seconds
                        </span>
                    </div>

                    <div class="test-info-box">
                        <span class="test-info-label">SAMPLES SAVED</span>
                        <span class="test-info-value" id="samplesCollectedValue">
                            {{ $dashboardData['samples_collected'] }}
                        </span>
                    </div>
                </div>

                <div class="button-group">
                    <button id="startTestBtn" type="button" class="start-btn">
                        Start Test
                    </button>

                    <button id="stopTestBtn" type="button" class="stop-btn">
                        Stop Test
                    </button>
                </div>
            </div>

            <div class="recommendation-card">
                <div class="recommendation-title">💡 Irrigation Recommendation</div>

                <p id="recommendationValue" class="recommendation-text">
                    {{ $dashboardData['recommendation'] }}
                </p>
            </div>

        </div>

    </div>
</div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const startSimulationUrl = @json(route('dashboard.simulation.start'));
    const generateSimulationUrl = @json(route('dashboard.simulation.generate'));
    const stopSimulationUrl = @json(route('dashboard.simulation.stop'));

    const sampleDurationSeconds = Number(@json($dashboardData['sample_duration_seconds']));
    const maxChartPoints = 20;
    const maxTableRows = 8;

    let simulationActive = @json($dashboardData['simulation_active']);
    let simulationStartedAt = @json($dashboardData['simulation_started_at']);
    let samplesCollected = Number(@json($dashboardData['samples_collected']));
    let simulationInterval = null;

    const startTestBtn = document.getElementById('startTestBtn');
    const stopTestBtn = document.getElementById('stopTestBtn');
    const modeBadge = document.getElementById('modeBadge');
    const dataSource = document.getElementById('dataSource');
    const testStatusValue = document.getElementById('testStatusValue');
    const sessionTimer = document.getElementById('sessionTimer');
    const samplesCollectedValue = document.getElementById('samplesCollectedValue');

    const flowRateValue = document.getElementById('flowRateValue');
    const statusValue = document.getElementById('statusValue');
    const rpmValue = document.getElementById('rpmValue');
    const pulseValue = document.getElementById('pulseValue');
    const deviceStatusValue = document.getElementById('deviceStatusValue');
    const recommendationValue = document.getElementById('recommendationValue');
    const lastUpdatedValue = document.getElementById('lastUpdatedValue');
    const readingsTableBody = document.getElementById('readingsTableBody');

    const notification = document.getElementById('notification');

    function updateDateTime() {
        const now = new Date();

        document.getElementById('currentDateTime').textContent =
            now.toLocaleString('en-US', {
                month: 'long',
                day: 'numeric',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
    }

    function formatDuration(totalSeconds) {
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        return [
            String(hours).padStart(2, '0'),
            String(minutes).padStart(2, '0'),
            String(seconds).padStart(2, '0')
        ].join(':');
    }

    function updateSessionTimer() {
        if (!simulationActive || !simulationStartedAt) {
            sessionTimer.textContent = '00:00:00';
            return;
        }

        const startedTime = new Date(simulationStartedAt).getTime();
        const currentTime = Date.now();
        const elapsedSeconds = Math.max(0, Math.floor((currentTime - startedTime) / 1000));

        sessionTimer.textContent = formatDuration(elapsedSeconds);
    }

    function showNotification(message, type = 'success') {
        notification.textContent = message;
        notification.className = `notification ${type}`;

        setTimeout(() => {
            notification.className = 'notification';
            notification.textContent = '';
        }, 4500);
    }

    function updateTestControls() {
        startTestBtn.disabled = simulationActive;
        stopTestBtn.disabled = !simulationActive;

        if (simulationActive) {
            modeBadge.textContent = 'SIMULATION TEST ACTIVE';
            modeBadge.className = 'mode-badge mode-simulation';
            testStatusValue.textContent = 'Measuring';
        } else {
            modeBadge.textContent = 'WAITING TO START TEST';
            modeBadge.className = 'mode-badge mode-offline';
            testStatusValue.textContent = 'Idle';
        }

        samplesCollectedValue.textContent = samplesCollected;
    }

    async function postJson(url) {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({})
        });

        const data = await response.json().catch(() => ({
            message: 'The server returned an unexpected response.'
        }));

        if (!response.ok) {
            throw new Error(data.message || 'Request failed.');
        }

        return data;
    }

    const chartContext = document.getElementById('flowChart');

    const flowChart = new Chart(chartContext, {
        type: 'line',
        data: {
            labels: @json($dashboardData['chart_labels']),
            datasets: [{
                label: 'Flow Rate (L/min)',
                data: @json($dashboardData['chart_values']),
                borderWidth: 2,
                tension: 0.35,
                fill: true
            }]
        },
        options: {
            responsive: true,
            animation: {
                duration: 500
            },
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Flow Rate (L/min)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Reading Time'
                    }
                }
            }
        }
    });

    function formatOneDecimal(value) {
        return Number(value).toFixed(1);
    }

    function getStatusClass(status) {
        const normalizedStatus = String(status).toLowerCase();

        if (normalizedStatus === 'normal') {
            return 'status-normal';
        }

        if (normalizedStatus === 'low') {
            return 'status-low';
        }

        if (normalizedStatus === 'high') {
            return 'status-high';
        }

        return 'status-offline';
    }

    function escapeHtml(value) {
        const temporaryElement = document.createElement('div');
        temporaryElement.textContent = value;

        return temporaryElement.innerHTML;
    }

    function addReadingToTable(reading) {
        const emptyRow = document.getElementById('emptyTableRow');

        if (emptyRow) {
            emptyRow.remove();
        }

        const newRow = document.createElement('tr');

        newRow.innerHTML = `
            <td>${escapeHtml(reading.datetime_label)}</td>
            <td>${formatOneDecimal(reading.flow_rate)} L/min</td>
            <td>${formatOneDecimal(reading.rpm)} RPM</td>
            <td>${reading.pulse_count}</td>
            <td>${escapeHtml(reading.status)}</td>
        `;

        readingsTableBody.insertBefore(newRow, readingsTableBody.firstChild);

        while (readingsTableBody.children.length > maxTableRows) {
            readingsTableBody.removeChild(readingsTableBody.lastChild);
        }
    }

    function updateChart(reading) {
        flowChart.data.labels.push(reading.time_label);
        flowChart.data.datasets[0].data.push(Number(reading.flow_rate));

        while (flowChart.data.labels.length > maxChartPoints) {
            flowChart.data.labels.shift();
            flowChart.data.datasets[0].data.shift();
        }

        flowChart.update();
    }

    function showLatestReading(reading) {
        flowRateValue.textContent = `${formatOneDecimal(reading.flow_rate)} L/min`;

        statusValue.textContent = reading.status;
        statusValue.className = `status-value ${getStatusClass(reading.status)}`;

        rpmValue.textContent = `${formatOneDecimal(reading.rpm)} RPM`;
        pulseValue.textContent = reading.pulse_count;
        deviceStatusValue.textContent = reading.device_status;

        recommendationValue.textContent = reading.recommendation;
        lastUpdatedValue.textContent = reading.datetime_label;

        dataSource.textContent = reading.is_simulated
            ? 'Simulated Data'
            : 'Actual ESP32 Device Data';
    }

    async function generateSimulationReading() {
        if (!simulationActive) {
            return;
        }

        try {
            const data = await postJson(generateSimulationUrl);

            samplesCollected = Number(data.samples_collected);
            samplesCollectedValue.textContent = samplesCollected;

            showLatestReading(data.reading);
            addReadingToTable(data.reading);
            updateChart(data.reading);

        } catch (error) {
            showNotification(error.message, 'error');
        }
    }

    async function startSimulation() {
        try {
            const data = await postJson(startSimulationUrl);

            simulationActive = true;
            simulationStartedAt = data.started_at;
            samplesCollected = 0;

            updateTestControls();
            updateSessionTimer();

            showNotification('Simulation test started. The first reading is being generated.');

            await generateSimulationReading();

            if (simulationInterval) {
                clearInterval(simulationInterval);
            }

            simulationInterval = setInterval(
                generateSimulationReading,
                sampleDurationSeconds * 1000
            );

        } catch (error) {
            showNotification(error.message, 'error');
        }
    }

    async function stopSimulation() {
        try {
            const data = await postJson(stopSimulationUrl);

            simulationActive = false;
            simulationStartedAt = null;

            if (simulationInterval) {
                clearInterval(simulationInterval);
                simulationInterval = null;
            }

            updateTestControls();
            updateSessionTimer();

            showNotification(data.message || 'Simulation test stopped.');

        } catch (error) {
            showNotification(error.message, 'error');
        }
    }

    startTestBtn.addEventListener('click', startSimulation);
    stopTestBtn.addEventListener('click', stopSimulation);

    updateDateTime();
    updateSessionTimer();
    updateTestControls();

    setInterval(updateDateTime, 1000);
    setInterval(updateSessionTimer, 1000);

    if (simulationActive) {
        simulationInterval = setInterval(
            generateSimulationReading,
            sampleDurationSeconds * 1000
        );
    }
</script>


</body>
</html>
