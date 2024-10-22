@props(['issue', 'repoName'])

<div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-300 transform hover:-translate-y-1 dark:text-white">
    <div class="flex justify-between items-start">
        <div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $issue['title'] }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $repoName }}</p>
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
    <p class="text-gray-700 dark:text-gray-200 mt-2">{{ $issue['description'] }}</p>
    <div class="mt-4 flex items-center justify-between">
        <div class="flex items-center space-x-2">
            @foreach($issue['labels'] as $label)
                <x-dashboard.issues.label :label="$label" />
            @endforeach
        </div>
        <div class="text-sm text-gray-600 dark:text-gray-300">
            <span>Created by: <strong class="text-gray-900 dark:text-white">{{ $issue['creator'] }}</strong></span>
            <span class="ml-4">💬 {{ $issue['comments'] }} comments</span>
        </div>
    </div>

    <div class="mt-2 flex items-center justify-between">
        @if($issue['milestone'])
            <span class="text-sm text-gray-600 bg-gray-200 px-2 py-1 rounded">🏁 Milestone: {{ $issue['milestone'] }}</span>
        @else
            <span></span>
        @endif

        @if(isset($issue['estimated_hours']) && $issue['estimated_hours'] > 0)
            <x-progress-bar :issue="$issue" class="flex-grow ml-4" />
        @endif
    </div>
</div>

<!-- Static -->
<div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-300 transform hover:-translate-y-1 dark:text-white">
    <div class="flex justify-between items-start">
        <div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Implement user authentication</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300">Admin Dashboard</p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full border">
                {{ $issue['status'] }}
            </span>
                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-800">
                    High
                </span>
                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-800">
                    Security
                </span>
                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-800">
                    User Communication
                </span>
        </div>
    </div>
    <p class="text-gray-700 dark:text-gray-200 mt-2">We need to add user authentication to the application.</p>
    <div class="mt-4 flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <span class="text-xs font-semibold px-2 py-1 rounded border bg-blue-100 text-blue-800 border-blue-800">
                Feature
            </span>                            
            <span class="text-xs font-semibold px-2 py-1 rounded border bg-yellow-100 text-yellow-800 border-yellow-800">
                Security
            </span>
            <span class="text-xs font-semibold px-2 py-1 rounded border bg-red-100 text-red-800 border-red-800">
                Bug
            </span>
            <span class="text-xs font-semibold px-2 py-1 rounded border bg-purple-100 text-purple-800 border-purple-800">
                UI
            </span>
            <span class="text-xs font-semibold px-2 py-1 rounded border bg-pink-100 text-pink-800 border-pink-800">
                Backend
            </span>
        </div>
        <div class="text-sm text-gray-600 dark:text-gray-300">
            <span>Created by: <strong class="text-gray-900 dark:text-white">{{ $issue['creator'] }}</strong></span>
            <span class="ml-4">💬 {{ $issue['comments'] }} comments</span>
        </div>
    </div>

    <div class="mt-2 flex items-center justify-between">
        <span class="text-sm bg-gray-200 text-gray-800 px-2 py-1 rounded">🏁 Milestone: v1.0 Release</span>
        <div class="@ flex justify-end items-center text-sm text-gray-600 dark:text-gray-300">
            <span class="mr-4">Est. 2023-06-30</span>
            <span class="mr-2">5 / 20 hrs</span>
            <div class="w-24">
                <div class="bg-gray-200 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: 25%"></div>
                </div>
            </div>
        </div>
    </div>
</div>
