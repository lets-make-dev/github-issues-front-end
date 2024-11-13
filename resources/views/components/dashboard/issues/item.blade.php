@props(['issue', 'repoName'])
<div >
    <div class="border-2 dark:border border-gray-900 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-300 transform hover:-translate-y-1 dark:text-white">
        <div class="flex justify-between items-start">

            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $issue['title'] }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $repoName }}</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="{{ $issue['status'] === 'open' ? 'bg-green-100 text-green-800' : 'bg-gray-300 text-gray-800' }} text-xs font-semibold px-2 py-1 rounded-full border">
                    {{ $issue['status'] }}
                </span>
                @foreach($issue['priorities'] as $priority)
                    <span class="text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-800">
                        {{ $priority }}
                    </span>
                @endforeach
            </div>
        </div>
        <p class="text-gray-700 dark:text-gray-200 mt-2">{!! $issue['description'] !!}</p>
        <div class="mt-4 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                @foreach($issue['labels'] as $color => $label)
                    <x-dashboard.issues.label :label="$label" :color="$color"/>
                @endforeach
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-300 flex items-center gap-4">
                <span>Created by: <strong class="text-gray-900 dark:text-white">{{ $issue['creator'] }}</strong></span>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/>
                        </svg>

                        <span wire:click="showComments({{ $issue['issue_number'] }})">{{ $issue['comments_count'] }} comments</span>

                    </div>
                    <button  wire:click="showAddCommentModel({{ $issue['issue_number'] }})" class="rounded">
                        <i class="fa-solid fa-plus" title="Add Comment"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-2 flex items-center justify-between">
            @if($issue['milestone'])
                <span class="text-sm text-gray-600 bg-gray-200 px-2 py-1 rounded">🏁 Milestone: {{ $issue['milestone'] }}</span>
            @else
                <span></span>
            @endif

            @if(isset($issue['estimated_hours']) && $issue['estimated_hours'] > 0)
                <x-progress-bar :issue="$issue" class="flex-grow ml-4"/>
            @endif
        </div>
    </div>
</div>



<!-- Static -->
{{--<div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-300 transform hover:-translate-y-1 dark:text-white">--}}
{{--    <div class="flex justify-between items-start">--}}
{{--        <div>--}}
{{--            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Implement user authentication</h3>--}}
{{--            <p class="text-sm text-gray-600 dark:text-gray-300">Admin Dashboard</p>--}}
{{--        </div>--}}
{{--        <div class="flex items-center space-x-2">--}}
{{--            <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full border">--}}
{{--                {{ $issue['status'] }}--}}
{{--            </span>--}}
{{--                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-800">--}}
{{--                    High--}}
{{--                </span>--}}
{{--                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-800">--}}
{{--                    Security--}}
{{--                </span>--}}
{{--                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-800">--}}
{{--                    User Communication--}}
{{--                </span>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--    <p class="text-gray-700 dark:text-gray-200 mt-2">We need to add user authentication to the application.</p>--}}
{{--    <div class="mt-4 flex items-center justify-between">--}}
{{--        <div class="flex items-center space-x-2">--}}
{{--            <span class="text-xs font-semibold px-2 py-1 rounded border bg-blue-100 text-blue-800 border-blue-800">--}}
{{--                Feature--}}
{{--            </span>                            --}}
{{--            <span class="text-xs font-semibold px-2 py-1 rounded border bg-yellow-100 text-yellow-800 border-yellow-800">--}}
{{--                Security--}}
{{--            </span>--}}
{{--            <span class="text-xs font-semibold px-2 py-1 rounded border bg-red-100 text-red-800 border-red-800">--}}
{{--                Bug--}}
{{--            </span>--}}
{{--            <span class="text-xs font-semibold px-2 py-1 rounded border bg-purple-100 text-purple-800 border-purple-800">--}}
{{--                UI--}}
{{--            </span>--}}
{{--            <span class="text-xs font-semibold px-2 py-1 rounded border bg-pink-100 text-pink-800 border-pink-800">--}}
{{--                Backend--}}
{{--            </span>--}}
{{--        </div>--}}
{{--        <div class="text-sm text-gray-600 dark:text-gray-300">--}}
{{--            <span>Created by: <strong class="text-gray-900 dark:text-white">{{ $issue['creator'] }}</strong></span>--}}
{{--            <span class="ml-4">💬 {{ $issue['comments'] }} comments</span>--}}
{{--        </div>--}}
{{--    </div>--}}

{{--    <div class="mt-2 flex items-center justify-between">--}}
{{--        <span class="text-sm bg-gray-200 text-gray-800 px-2 py-1 rounded">🏁 Milestone: v1.0 Release</span>--}}
{{--        <div class="@ flex justify-end items-center text-sm text-gray-600 dark:text-gray-300">--}}
{{--            <span class="mr-4">Est. 2023-06-30</span>--}}
{{--            <span class="mr-2">5 / 20 hrs</span>--}}
{{--            <div class="w-24">--}}
{{--                <div class="bg-gray-200 rounded-full h-1.5 overflow-hidden">--}}
{{--                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: 25%"></div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
