@section('title', 'Dashboard - ' . config('app.name', 'Laravel'))

<div>
    <div wire:loading>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="flex space-x-3">
                <div class="w-4 h-4 bg-white rounded-full animate-[dot-bounce_0.5s_ease-in-out_infinite]"></div>
                <div class="w-4 h-4 bg-white rounded-full animate-[dot-bounce_0.5s_ease-in-out_0.1s_infinite]"></div>
                <div class="w-4 h-4 bg-white rounded-full animate-[dot-bounce_0.5s_ease-in-out_0.2s_infinite]"></div>
            </div>
        </div>
    </div>
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <div>
            <div class="flex items-center space-x-4 mb-4 md:mb-0">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Account:</h2>
                <select wire:model.live="selectedAccount" class="border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-black dark:text-white
                    ">
                    <option value="{{$accounts->id}}">{{$accounts->name}}</option>
                    {{-- <option value="null">Chosse a Account</option> --}}
                    {{-- @foreach($accounts as $key => $account)
                        <option value="{{ $key }}">{{ $account }}</option>
                    @endforeach --}}
                </select>
            </div>
            <div class="flex items-center space-x-4 mb-4 mt-4 md:mb-0">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Repository:</h2>
                <select wire:model.live="selectedRepo" class="border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-black dark:text-white
                    ">
                    <option value="{{$repos->id}}">{{$repos->name}}</option>
                    {{-- <option value="null">Chosse a Repository</option> --}}
                    {{-- @foreach($repos as $repo)
                        <option value="{{ $repo }}">{{ $repo }}</option>
                    @endforeach --}}
                </select>
            </div>
        </div>
        <div class="flex items-center w-full md:w-auto">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search issues..."
                   class="w-full md:w-auto text-gray-900 dark:text-white placeholder-gray-500 rounded-l px-4 py-2 border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <button class="bg-gray-900 text-white px-4 py-2 rounded-r hover:bg-gray-700 transition-colors duration-300 border border-gray-900 dark:border-gray-600">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </div>

    <div class="mb-6 flex flex-wrap items-center space-x-4">
        <div class="flex items-center space-x-4">
            <label class="flex items-center">
                <input wire:model.live="showClosed" type="checkbox"
                       class="form-checkbox h-5 w-5 text-indigo-600 border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800">
                <span class="ml-2 text-gray-700 dark:text-gray-300">Show closed</span>
            </label>
        </div>
        <div>
            <label class="text-gray-700 dark:text-gray-300 font-semibold">Group by:</label>
            <select wire:model.live="groupBy"
                    class="ml-2 border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-black dark:text-white">
                <option value="">None</option>
                <option value="status">Status</option>
                <option value="milestone">Milestone</option>
                <!-- Add more grouping options as needed -->
            </select>
        </div>
    </div>

    <div class="space-y-8" x-data="{ allLabels: @entangle('allLabels'), showCreateButton: @entangle('showCreateButton') }">
            <button
                x-show="$wire.showCreateButton"
                wire:click='showNewIssueModel()'
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                New Issue
            </button>

        @foreach($this->groupedIssues as $group)
            <div class="mb-8">
                <h3 class="text-xl font-semibold mb-4 pb-2 border-b-2 border-gray-300 dark:border-gray-600 dark:text-white">{{ $group['name'] ?: ($showClosed ? 'Closed' : 'All') }}</h3>
                <x-dashboard.issues.list :issues="$group['issues']" :repo-name="$selectedRepo"/>
            </div>
        @endforeach
        <x-dashboard.issues.comment-list-model :comments="$comments"/>
        <x-dashboard.issues.add-comment-model/>
        <x-dashboard.issues.new-issue-model />


    </div>
</div>
