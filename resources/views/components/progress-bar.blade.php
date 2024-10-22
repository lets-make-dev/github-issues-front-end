@props(['issue'])

@if(isset($issue['estimated_hours']) && $issue['estimated_hours'] > 0)
    <div class="@ flex justify-end items-center text-sm text-gray-600 dark:text-gray-300">
        @if(isset($issue['estimated_completion_date']))
            <span class="mr-4">Est. {{ $issue['estimated_completion_date'] }}</span>
        @endif
        <span class="mr-2">{{ $issue['logged_hours'] ?? 0 }} / {{ $issue['estimated_hours'] }} hrs</span>
        <div class="w-24">
            <div class="bg-gray-200 rounded-full h-1.5 overflow-hidden">
                @php
                    $logged_hours = $issue['logged_hours'] ?? 0;
                    $progress = ($logged_hours / $issue['estimated_hours']) * 100;
                    $progress = min(100, max(0, $progress));
                @endphp
                <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $progress }}%"></div>
            </div>
        </div>
    </div>
@endif