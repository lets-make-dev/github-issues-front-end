@props(['githubAccounts', 'selectedAccount', 'repositories'])

<div x-data="{
        open: false,
        showAccounts: false,
        repoOpen: false,
        selectedRepo: null
    }">
    <h3 class="text-2xl font-semibold mb-4">GitHub Integration</h3>
    <div
        x-show="!showAccounts"
        class="py-4">
        <button
            @click="showAccounts = !showAccounts"
            class="text-blue-600 hover:text-blue-800 mr-2">
            Manage GitHub Accounts
        </button>
    </div>
    <div x-show="showAccounts">
        <div
            class="mb-6">
            <h3 class="text-xl font-semibold mb-4">Choose a Github Account</h3>
            <div class="relative">
                <button @click="open = !open" type="button"
                        class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500 sm:text-sm"
                        aria-haspopup="listbox" aria-expanded="true" aria-labelledby="listbox-label">
                    <span class="block truncate" x-text="selectedAccount || 'Select a GitHub account'"></span>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                             fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                  d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </span>
                </button>
                <div x-show="open" @click.away="open = false"
                     class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                    <ul tabindex="-1" role="listbox" aria-labelledby="listbox-label"
                        aria-activedescendant="listbox-option-3">
                        <template x-for="account in githubAccounts" :key="account">
                            <li @click="selectedAccount = account; open = false; "
                                class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-gray-100"
                                role="option">
                                <span class="block truncate" x-text="account"></span>
                            </li>
                        </template>
                        <li
                            class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-gray-100"
                            role="option">
                            <a href="{{ route('github.connect') }}"><span class="block truncate">Add another GitHub Account</span></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div x-show="selectedAccount && repositories.length > 0" class="mb-6">
            <h3 class="text-xl font-semibold mb-4">Add Repository To Projects</h3>
            <div class="relative">
                <button @click="repoOpen = !repoOpen" type="button"
                        class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500 sm:text-sm"
                        aria-haspopup="listbox" aria-expanded="true" aria-labelledby="listbox-label">
                    <span class="block truncate" x-text="selectedRepo || 'Select a repository'"></span>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                             fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                  d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </span>
                </button>
                <div x-show="repoOpen" @click.away="repoOpen = false"
                     class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                    <ul tabindex="-1" role="listbox" aria-labelledby="listbox-label"
                        aria-activedescendant="listbox-option-3">
                        <template x-for="repo in repositories" :key="repo">
                            <li @click="selectedRepo = repo; repoOpen = false"
                                class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-gray-100"
                                role="option">
                                <span class="block truncate" x-text="repo"></span>
                            </li>
                        </template>
                        <li @click="alert('refresh')"
                            class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-gray-100"
                            role="option">
                            <span class="block truncate">Refresh</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <button
            @click="showAccounts = !showAccounts"
            class="text-blue-600 hover:text-blue-800 mr-2">
            Hide
        </button>
    </div>

    <div class="mb-6">
        <h3 class="text-xl font-semibold mb-4">Connected Repositories</h3>
        <ul class="space-y-2">
            <template x-for="repo in repositories" :key="repo">
                <li class="flex justify-between items-center p-2 bg-white rounded shadow">
                    <span x-text="repo"></span>
                    <button class="text-red-600 hover:text-red-800">Disconnect</button>
                </li>
            </template>
        </ul>
    </div>

</div>
