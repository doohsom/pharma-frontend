<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-chart-matrix@1.1.1/dist/chartjs-chart-matrix.min.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .card { box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); margin-bottom: 1rem; }
        .chart-container { height: 300px; }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <h1 class="mb-4">Sensor Dashboard</h1>
        
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Sensors</h5>
                        <p class="card-text display-4"><?php echo e(count($sensorIds ?? [])); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Excursion Count</h5>
                        <p class="card-text display-4"><?php echo e($excursionCount ?? 0); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Current Temperature</h5>
                        <canvas id="gaugeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <?php dd($hello); ?>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Sensor Readings Over Time</h5>
                        <div class="chart-container">
                            <canvas id="lineChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Latest Sensor Values</h5>
                        <div class="chart-container">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Temperature Distribution</h5>
                        <div class="chart-container">
                            <canvas id="histogramChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Sensor Heatmap</h5>
                        <div class="chart-container">
                            <canvas id="heatmapChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Latest 50 Sensor Readings</h5>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Sensor ID</th>
                                <th>Timestamp</th>
                                <th>Value</th>
                                <th>Excursion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $latestReadings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reading): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="<?php echo e($reading['hasExcursion'] ? 'table-danger' : ''); ?>">
                                    <td><?php echo e($reading['sensorId']); ?></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($reading['timestamp'])->format('Y-m-d H:i:s')); ?></td>
                                    <td><?php echo e($reading['decryptedValue']); ?></td>
                                    <td><?php echo e($reading['hasExcursion'] ? 'Yes' : 'No'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Line Chart
        new Chart(document.getElementById('lineChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($lineChartData['timestamps'], 15, 512) ?>,
                datasets: <?php echo json_encode($lineChartData['datasets'], 15, 512) ?>
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { type: 'time', time: { unit: 'minute' } },
                    y: { title: { display: true, text: 'Temperature' } }
                }
            }
        });

        // Gauge Chart
        new Chart(document.getElementById('gaugeChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [<?php echo json_encode($gaugeData['value'], 15, 512) ?>, 100 - <?php echo json_encode($gaugeData['value'], 15, 512) ?>],
                    backgroundColor: ['rgba(75, 192, 192, 0.8)', 'rgba(200, 200, 200, 0.3)'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                circumference: 180,
                rotation: -90,
                cutout: '80%',
                plugins: {
                    tooltip: { enabled: false },
                    legend: { display: false },
                    title: {
                        display: true,
                        text: <?php echo json_encode($gaugeData['value'], 15, 512) ?> + '°C',
                        position: 'bottom',
                        font: { size: 24 }
                    }
                }
            }
        });

        // Bar Chart
        new Chart(document.getElementById('barChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($barChartData['labels'], 15, 512) ?>,
                datasets: [{
                    label: 'Latest Sensor Value',
                    data: <?php echo json_encode($barChartData['data'], 15, 512) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Temperature' } }
                }
            }
        });

        // Histogram Chart
        new Chart(document.getElementById('histogramChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($histogramData['labels'], 15, 512) ?>,
                datasets: [{
                    label: 'Frequency',
                    data: <?php echo json_encode($histogramData['data'], 15, 512) ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.8)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Frequency' } }
                }
            }
        });

        // Heatmap Chart
        new Chart(document.getElementById('heatmapChart').getContext('2d'), {
            type: 'matrix',
            data: {
                datasets: [{
                    label: 'Sensor Temperatures',
                    data: <?php echo json_encode($heatmapData, 15, 512) ?>,
                    backgroundColor: (context) => {
                        const value = context.dataset.data[context.dataIndex].v;
                        const min = Math.min(...context.dataset.data.map(d => d.v));
                        const max = Math.max(...context.dataset.data.map(d => d.v));
                        const normalizedValue = (value - min) / (max - min);
                        return `rgba(255, 0, 0, ${normalizedValue})`;
                    },
                    width: (context) => {
                        const chartWidth = context.chart.width;
                        const count = Math.sqrt(context.dataset.data.length);
                        return (chartWidth / count) - 1;
                    },
                    height: (context) => {
                        const chartHeight = context.chart.height;
                        const count = Math.sqrt(context.dataset.data.length);
                        return (chartHeight / count) - 1;
                    }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: false,
                    tooltip: {
                        callbacks: {
                            title: (context) => {
                                const dataPoint = context[0].dataset.data[context[0].dataIndex];
                                return `Sensor ${dataPoint.x}, ${dataPoint.y}`;
                            },
                            label: (context) => {
                                return `Temperature: ${context.dataset.data[context.dataIndex].v.toFixed(2)}°C`;
                            }
                        }
                    }
                },
                scales: {
                    x: { display: false },
                    y: { display: false }
                }
            }
        });
    </script>
</body>
</html>
<?php /**PATH /var/www/html/pharma-api/resources/views/batch/shew.blade.php ENDPATH**/ ?>