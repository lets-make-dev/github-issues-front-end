@section('title', 'Dashboard - ' . config('app.name', 'Laravel'))

<div class="bg-white dark:bg-gray-800 dark:text-white shadow-lg rounded-lg p-6 border border-gray-300 dark:border-gray-700">
    <div>
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <div class="flex items-center space-x-4 mb-4 md:mb-0">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Repository:</h2>
                <select wire:model.live="selectedRepo" class="border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    @foreach($repos as $repo)
                        <option value="{{ $repo }}">{{ $repo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center space-x-4">
                <input wire:model.debounce.300ms="search" type="text" placeholder="Search issues..." class="border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <label class="flex items-center">
                    <input wire:model="showClosed" type="checkbox" class="form-checkbox h-5 w-5 text-indigo-600 border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800">
                    <span class="ml-2 text-gray-700 dark:text-gray-300">Show closed</span>
                </label>
            </div>
        </div>

        <div class="mb-4">
            <label class="text-gray-700 dark:text-gray-300 font-semibold">Group by:</label>
            <select wire:model="groupBy" class="ml-2 border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="status">Status</option>
                <option value="milestone">Milestone</option>
                <!-- Add more grouping options as needed -->
            </select>
        </div>

        <div class="space-y-8">
            @foreach($this->groupedIssues as $group)
                <div class="mb-8">
                    <h3 class="text-xl font-semibold mb-4 pb-2 border-b-2 border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800">{{ $group['name'] }}</h3>
                    <x-dashboard.issues.list :issues="$group['issues']" :repo-name="$selectedRepo" />
                </div>
            @endforeach
        </div>
    </div>
</div>
