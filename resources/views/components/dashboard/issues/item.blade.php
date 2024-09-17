@props(['issue', 'repoName'])

<div class="border-2 border-gray-900 rounded-lg p-4 hover:bg-gray-100 transition-all duration-300 transform hover:-translate-y-1">
    <div class="flex justify-between items-start">
        <div>
            <h3 class="text-lg font-medium text-gray-900">{{ $issue['title'] }}</h3>
            <p class="text-sm text-gray-600">{{ $repoName }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="{{ $issue['status'] === 'Open' ? 'bg-green-100 text-green-800' : 'bg-gray-300 text-gray-800' }} text-xs font-semibold px-2 py-1 rounded-full border">
                {{ $issue['status'] }}
            </span>
            @foreach($issue['priorities'] as $priority)
                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-800">
                    {{ $priority }}
                </span>
            @endforeach
        </div>
    </div>
    <p class="text-gray-700 mt-2">{{ $issue['description'] }}</p>
    <div class="mt-4 flex items-center justify-between">
        <div class="flex items-center space-x-2">
            @foreach($issue['labels'] as $label)
                <x-dashboard.issues.label :label="$label" />
            @endforeach
        </div>
        <div class="text-sm text-gray-600">
            <span>Created by: <strong class="text-gray-900">{{ $issue['creator'] }}</strong></span>
            <span class="ml-4">💬 {{ $issue['comments'] }} comments</span>
        </div>
    </div>

    <div class="mt-2 flex items-center justify-between">
        @if($issue['milestone'])
            <span class="text-sm text-gray-600 bg-gray-200 text-gray-800 px-2 py-1 rounded">🏁 Milestone: {{ $issue['milestone'] }}</span>
        @else
            <span></span>
        @endif

        @if(isset($issue['estimated_hours']) && $issue['estimated_hours'] > 0)
            <x-progress-bar :issue="$issue" class="flex-grow ml-4" />
        @endif
    </div>
</div>
