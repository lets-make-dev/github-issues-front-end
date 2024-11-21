<style>
    .repo-item {
        transition: all 1.5s ease;
        max-height: 100px;
        opacity: 1;
        overflow: hidden;
    }

    .repo-item.deleting {
        max-height: 0;
        opacity: 0;
        margin-top: 0;
        margin-bottom: 0;
        padding-top: 0;
        padding-bottom: 0;
    }
</style>

<div x-data="initGithubData();"
     x-init="
    $watch('$wire.connectedRepositories', value => connectedRepositories = value);
    $watch('repositories', value => { filterRepositories(); });
"
     x-on:keydown.escape.window="repoOpen = false"
>
    <div wire:loading>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="flex space-x-3">
                <div class="w-4 h-4 bg-white rounded-full animate-[dot-bounce_0.5s_ease-in-out_infinite]"></div>
                <div class="w-4 h-4 bg-white rounded-full animate-[dot-bounce_0.5s_ease-in-out_0.1s_infinite]"></div>
                <div class="w-4 h-4 bg-white rounded-full animate-[dot-bounce_0.5s_ease-in-out_0.2s_infinite]"></div>
            </div>
        </div>
    </div>

    <h3 class="text-2xl font-semibold mb-4">GitHub Integration</h3>
    <div x-show="!showAccounts && connectedRepositories.length > 0" class="py-4">
        <button @click="showAccounts = !showAccounts" class="text-blue-600 hover:text-blue-800 mr-2">
            Manage GitHub Accounts
        </button>
    </div>
    <div x-show="showAccounts || connectedRepositories.length == 0">
        <div class="mb-6">
            <h3 class="text-xl font-semibold mb-4">Choose a GitHub Account</h3>
            <div class="relative">
                <button @click="accountsContainerOpen = !accountsContainerOpen" type="button"
                        class="relative w-full border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500 sm:text-sm"
                        aria-haspopup="listbox" aria-expanded="true" aria-labelledby="listbox-label">
                    <span class="block truncate"
                          x-text="selectedAccountId ? githubAccounts[selectedAccountId] : 'Select a GitHub account'"></span>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                             fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                  d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </span>
                </button>
                <div x-show="accountsContainerOpen" @click.away="accountsContainerOpen = false"
                     class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm dark:text-white">
                    <ul tabindex="-1" role="listbox" aria-labelledby="listbox-label"
                        aria-activedescendant="listbox-option-3">
                        <template x-for="(name, id) in githubAccounts" :key="id">
                            <li @click="selectedAccountId = id; accountsContainerOpen = false; $wire.accountSelected(id)"
                                class="text-gray-900 dark:text-white cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 dark:hover:bg-gray-700"
                                role="option">
                                <span class="block truncate" x-text="name"></span>
                            </li>
                        </template>
                        <li class="text-gray-900 dark:text-white cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 dark:hover:bg-gray-700"
                            role="option">
                            <a href="{{ route('github.connect') }}"><span
                                        class="block truncate">Add another GitHub Account</span></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div x-show="selectedAccountId" class="mb-6">
            <h3 class="text-xl font-semibold mb-4">Add Repository To Projects <span
                        x-text="filteredRepositories.length"></span></h3>
            <div class="relative" x-ref="dropdownContainer">
                <div class="flex">
                    <input
                            x-model="searchTerm"
                            @input="filterRepositories(); repoOpen = true"
                            @focus="repoOpen = true"
                            @click="repoOpen = true"
                            type="text"
                            class="w-full border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 rounded-md shadow-sm pl-3 pr-10 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500 dark:text-white"
                            placeholder="Search repositories...">
                    <button @click="repoOpen = !repoOpen" type="button"
                            class="ml-2 border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 rounded-md shadow-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                             fill="currentColor">
                            <path fill-rule="evenodd"
                                  d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
                <div x-show="repoOpen"
                     class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                    <ul tabindex="-1" role="listbox" aria-labelledby="listbox-label"
                        aria-activedescendant="listbox-option-3">
                        {{--                        <span x-text="JSON.stringify(filteredRepositories)"></span>--}}
                        <template x-for="repo in filteredRepositories">
                            <li @click="$wire.connectRepository(repo); selectedRepo = null; repoOpen = false; searchTerm = ''"
                                class="text-gray-900 dark:text-white cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 dark:hover:bg-gray-700"
                                role="option">
                                <span class="block truncate" x-text="repo"></span>
                            </li>
                        </template>
                        <li x-show="filteredRepositories.length === 0"
                            class="text-gray-500 cursor-default select-none relative py-2 pl-3 pr-9">
                            No matching repositories found
                        </li>
                        <li wire:click="refreshRepos"
                            class="text-gray-900 dark:text-white select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                            role="option">
                            <span class="block truncate">Refresh</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <button
                x-show="connectedRepositories.length > 0"
                @click="showAccounts = !showAccounts" class="text-blue-600 hover:text-blue-800 mr-2">
            Hide
        </button>
    </div>

    <div class="mb-6" x-show="connectedRepositories.length > 0">
        <h3 class="text-xl font-semibold mb-4">Connected Repositories</h3>
        <ul class="space-y-2">
            <template x-for="repo in connectedRepositories" :key="repo.id">
                <li class="repo-item flex justify-between items-center p-2 bg-white dark:bg-gray-800 rounded shadow dark:text-white"
                    :class="{ 'deleting': deletingRepos[repo.id] }">
                    <span x-text="repo.account.name + '/' + repo.name"></span>
                    <template x-if="!processingRepos[repo.id] && !deletingRepos[repo.id]">
                        <button
                                @click="disconnectRepo(repo.id)"
                                class="text-red-600 hover:text-red-800">Disconnect
                        </button>
                    </template>
                    <template x-if="processingRepos[repo.id]">
                        <span class="text-gray-500">Deleting...</span>
                    </template>
                </li>
            </template>
        </ul>

    </div>


    <script>
        function initGithubData() {
            return {
                init() {
                    document.addEventListener('click', (e) => {
                        this.closeDropdownIfClickedOutside(e, this.$refs);
                    });
                    window.addEventListener('error', event => {
                        const messageElement = document.getElementById('errorMessage');
                        messageElement.style.display = 'block';
                        messageElement.innerHTML = event.detail;
                        // setTimeout(() => {
                        //     messageElement.style.display = 'none';
                        // }, 3000);
                    });
                },
                accountsContainerOpen: false,
                showAccounts: false,
                repoOpen: false,
                selectedAccountId: null,
                selectedRepo: null,
                processingRepos: {},
                deletingRepos: {},
                searchTerm: '',
                filteredRepositories: [],
                disconnectRepo(repoId) {
                    this.processingRepos[repoId] = true;
                    this.$wire.disconnectRepository(repoId).then(() => {
                        this.processingRepos[repoId] = false;
                        this.deletingRepos[repoId] = true;
                        setTimeout(() => {
                            this.connectedRepositories = this.connectedRepositories.filter(repo => repo.id !== repoId);
                            delete this.deletingRepos[repoId];
                        }, 2000);
                    }).catch(() => {
                        this.processingRepos[repoId] = false;
                    });
                },
                async filterRepositories() {
                    if (this.repositories.length) {
                        this.filteredRepositories = await this.repositories.filter(repo =>
                            repo.toLowerCase().includes(this.searchTerm.toLowerCase())
                        );
                    }
                },
                closeDropdownIfClickedOutside(event, refs) {
                    if (!refs.dropdownContainer.contains(event.target)) {
                        this.repoOpen = false;
                    }
                }
            }

        }
    </script>
</div>