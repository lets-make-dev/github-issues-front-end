@props(['showClosed', 'groupBy'])

<div class="mb-6 flex flex-wrap items-center">
    <label class="mr-4 text-gray-700 flex items-center cursor-pointer group">
        <input type="checkbox" wire:model="showClosed" class="form-checkbox h-5 w-5 text-gray-900 rounded focus:ring-gray-900 focus:ring-opacity-50 border-2 border-gray-900">
        <span class="ml-2 group-hover:text-gray-900 transition-colors duration-300">Show closed issues</span>
    </label>
    <div x-data="{ open: false }" class="relative inline-block text-left">
        <div>
            <button @click="open = !open" type="button" class="inline-flex justify-center w-full rounded-md border-2 border-gray-900 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-900 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-gray-900" id="options-menu" aria-haspopup="true" aria-expanded="true">
                <span x-text="$wire.groupBy === 'none' ? 'No grouping' : 'Group by Milestone'"></span>
                <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white border-2 border-gray-900 z-10">
            <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                <a href="#" wire:click="setGroupBy('milestone')" @click="open = false" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">Group by Milestone</a>
                <a href="#" wire:click="setGroupBy('none')" @click="open = false" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">No grouping</a>
            </div>
        </div>
    </div>
</div>