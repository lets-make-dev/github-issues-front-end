@section('title', 'Dashboard - ' . config('app.name', 'Laravel'))

<div class="bg-white shadow-lg rounded-lg p-6 border-2 border-gray-900">
<div>
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <div class="flex items-center space-x-4 mb-4 md:mb-0">
            <h2 class="text-2xl font-semibold text-gray-900">Repository:</h2>
            <select wire:model="selectedRepo" class="border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                @foreach($repos as $repo)
                    <option value="{{ $repo }}">{{ $repo }}</option>
                @endforeach
            </select>
        </div>
        <x-dashboard.issues.search />
    </div>

    <x-dashboard.issues.controls :show-closed="$showClosed" :group-by="$groupBy" />

    <div class="space-y-8">
        @foreach($this->groupedIssues as $group)
            <div class="mb-8">
                <h3 class="text-xl font-semibold mb-4 pb-2 border-b-2 border-gray-300">{{ $group['name'] }}</h3>
                <x-dashboard.issues.list :issues="$group['issues']" :repo-name="$selectedRepo" />
            </div>
        @endforeach
    </div>
</div>
</div>
