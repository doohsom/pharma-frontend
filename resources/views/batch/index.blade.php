<!-- resources/views/batches/index.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cold Chain Monitoring - Batches</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen p-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold">Cold Chain Monitoring Batches</h1>
                <a href="{{ route('batches.refresh') }}" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                    <i class="fas fa-sync-alt mr-2"></i> Refresh Data
                </a>
            </div>

            <!-- Batches Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($batches as $batch)
                <a href="{{ route('batches.show', $batch['batch_id']) }}" 
                   class="block bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
                    <div class="p-6 {{ $batch['excursions'] > 0 ? 'border-l-4 border-red-500' : 'border-l-4 border-green-500' }}">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h2 class="text-xl font-bold">{{ $batch['batch_id'] }}</h2>
                                <p class="text-sm text-gray-500">{{ $batch['timestamp']->format('Y-m-d H:i:s') }}</p>
                            </div>
                            @if($batch['excursions'] > 0)
                            <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">
                                {{ $batch['excursions'] }} Excursions
                            </span>
                            @else
                            <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">
                                Normal
                            </span>
                            @endif
                        </div>

                        <div class="space-y-2">
                            @foreach($batch['readings_by_type'] as $type => $data)
                            <div class="flex justify-between items-center text-sm">
                                <span class="capitalize">{{ $type }}:</span>
                                <span class="{{ $data['excursions'] > 0 ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                    {{ $data['avg_value'] }}
                                    @if($type === 'temperature')°C
                                    @elseif($type === 'humidity')%
                                    @else hPa
                                    @endif
                                </span>
                            </div>
                            @endforeach
                            <div class="text-sm text-gray-500 mt-2">
                                Total Readings: {{ $batch['total_readings'] }}
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</body>
</html>