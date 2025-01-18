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
    $watch('firstAccountRepositories', value => { filterFirstAccountRepositories(); });
    $watch('selectedLables', value => { filteredRepoLabels(); });
    {{-- $watch('repositorieLabel', value => { filteredRepoLabels(); }); --}}
    $watch('secondAccountRepositories', value => { filterSecondAccountRepositories(); });
"
     x-on:keydown.escape.window="firstRepoOpen = false; secondRepoOpen = false"
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
    <div class="w-full">
        <div x-show="!showAccounts && connectedRepositories.length > 0" class="py-4">
            <button @click="showAccounts = !showAccounts" class="text-blue-600 hover:text-blue-800 mr-2">
                Manage GitHub Accounts
            </button>
        </div>
        <div class="flex justify-between gap-4">
            <div x-show="showAccounts || connectedRepositories.length == 0" class="w-full">
                <div class="mb-6">
                    <h3 class="text-xl font-semibold mb-4">Choose a GitHub Account</h3>
                    <div class="relative">
                        <button @click="firstAccountOpenContainer = !firstAccountOpenContainer" type="button"
                                class="relative w-full border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500 sm:text-sm"
                                aria-haspopup="listbox" aria-expanded="true" aria-labelledby="listbox-label">
                    <span class="block truncate"
                          x-text="selectedFirstAccountId ? accountsForFirstDropdown[selectedFirstAccountId] : 'Select a GitHub account'"></span>
                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                             fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                  d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </span>
                        </button>
                        <div x-show="firstAccountOpenContainer" @click.away="firstAccountOpenContainer = false"
                             class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm dark:text-white">
                            <ul tabindex="-1" role="listbox" aria-labelledby="listbox-label"
                                aria-activedescendant="listbox-option-3" :class="{ 'pointer-events-none opacity-50': fieldDisabled }">
                                <template x-for="(name, id) in accountsForFirstDropdown" :key="id">
                                    <li @click="selectedFirstAccountId = id; firstAccountOpenContainer = false; $wire.firstAccountSelected(id)"
                                        class="text-gray-900 dark:text-white cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        role="option">
                                        <span class="block truncate" x-text="name"></span>
                                    </li>
                                </template>
                                <li class="text-gray-900 dark:text-white cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 dark:hover:bg-gray-700"
                                    role="option">
                                    <a href="{{ route('projects.settings.github-connect', ['project' => $this->project->id]) }}"><span
                                                class="block truncate">Add another GitHub Account</span></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div x-show="selectedFirstAccountId" class="mb-6">
                    <h3 class="text-xl font-semibold mb-4">Add Repository To Projects <span
                                x-text="filteredFirstAccountRepositories.length"></span></h3>
                    <div class="relative" x-ref="firstDropdownContainer">
                        <div class="flex">
                            <input
                                    x-model="firstSearchTerm"
                                    @input="filterFirstAccountRepositories(); firstRepoOpen = true"
                                    @focus="firstRepoOpen = true"
                                    @click="firstRepoOpen = true"
                                    type="text"
                                    class="w-full border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 rounded-md shadow-sm pl-3 pr-10 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500 dark:text-white"
                                    placeholder="Search repositories...">
                            <button @click="firstRepoOpen = !firstRepoOpen" type="button"
                                    class="border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 rounded-md shadow-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                     viewBox="0 0 20 20"
                                     fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                          clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                        <div x-show="firstRepoOpen"
                             class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                            <ul tabindex="-1" role="listbox" aria-labelledby="listbox-label"
                                aria-activedescendant="listbox-option-3" :class="{ 'pointer-events-none opacity-50': fieldDisabled }">
                                {{--                        <span x-text="JSON.stringify(filteredFirstAccountRepositories)"></span>--}}
                                <template x-for="repo in filteredFirstAccountRepositories">
                                    <li @click="selectedRepo = repo; firstRepoOpen = false; firstSearchTerm = repo; $wire.selectLables(repo)"
                                        class="text-gray-900 dark:text-white cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        role="option">
                                        <span class="block truncate" x-text="repo"></span>
                                    </li>
                                </template>
                                <li x-show="filteredFirstAccountRepositories.length === 0"
                                    class="text-gray-500 cursor-default select-none relative py-2 pl-3 pr-9">
                                    No matching repositories found
                                </li>
                                <li wire:click="refreshRepos(selectedFirstAccountId)"
                                    class="text-gray-900 dark:text-white select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                                    role="option">
                                    <span class="block truncate">Refresh</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>




                {{-- <button
                        x-show="connectedRepositories.length > 0"
                        @click="showAccounts = !showAccounts" class="text-blue-600 hover:text-blue-800 mr-2">
                    Hide
                </button> --}}
            </div>


            {{--            second account--}}
            <div x-show="showAccounts || connectedRepositories.length == 0" class="w-full">
                <div class="mb-6">
                    <h3 class="text-xl font-semibold mb-4">Choose a GitHub Account</h3>
                    <div class="relative">
                        <button @click="secondAccountOpenContainer = !secondAccountOpenContainer" type="button"
                                class="relative w-full border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500 sm:text-sm"
                                aria-haspopup="listbox" aria-expanded="true" aria-labelledby="listbox-label">
                    <span class="block truncate"
                          x-text="selectedSecondAccountId ? accountsForSecondDropdown[selectedSecondAccountId] : 'Select a GitHub account'"></span>
                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                             fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                  d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </span>
                        </button>
                        <div x-show="secondAccountOpenContainer" @click.away="secondAccountOpenContainer = false"
                             class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm dark:text-white">
                            <ul tabindex="-1" role="listbox" aria-labelledby="listbox-label"
                                aria-activedescendant="listbox-option-3" :class="{'pointer-events-none opacity-50': fieldDisabled}">
                                <template x-for="(name, id) in accountsForSecondDropdown" :key="id">
                                    <li @click="selectedSecondAccountId = id; secondAccountOpenContainer = false; $wire.secondAccountSelected(id)"
                                        class="text-gray-900 dark:text-white cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        role="option">
                                        <span class="block truncate" x-text="name"></span>
                                    </li>
                                </template>
                                <li class="text-gray-900 dark:text-white cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 dark:hover:bg-gray-700"
                                    role="option">
                                    <a href="{{ route('projects.settings.github-connect', ['project' => $this->project->id]) }}"><span
                                                class="block truncate">Add another GitHub Account</span></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div x-show="selectedSecondAccountId" class="mb-6">
                    <h3 class="text-xl font-semibold mb-4">Add Repository To Projects <span
                                x-text="filteredSecondAccountRepositories.length"></span></h3>
                    <div class="relative" x-ref="secondDropdownContainer">
                        <div class="flex">
                            <input
                                    x-model="secondSearchTerm"
                                    @input="filterSecondAccountRepositories(); secondRepoOpen = true"
                                    @focus="secondRepoOpen = true"
                                    @click="secondRepoOpen = true"
                                    type="text"
                                    class="w-full border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 rounded-md shadow-sm pl-3 pr-10 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500 dark:text-white"
                                    placeholder="Search repositories...">
                            <button @click="secondRepoOpen = !secondRepoOpen" type="button"
                                    class="ml-2 border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 rounded-md shadow-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                     viewBox="0 0 20 20"
                                     fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                          clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                        <div x-show="secondRepoOpen"
                             class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                            <ul tabindex="-1" role="listbox" aria-labelledby="listbox-label"
                                aria-activedescendant="listbox-option-3" :class="{ 'pointer-events-none opacity-50': fieldDisabled }">
                                {{--                        <span x-text="JSON.stringify(filteredRepositories)"></span>--}}
                                <template x-for="repo in filteredSecondAccountRepositories">
                                    <li @click="secondRepoOpen = false; secondSearchTerm = repo"
                                    {{-- <li @click="$wire.connectRepository(repo); selectedRepo = null; secondRepoOpen = false; searchTerm = ''" --}}
                                        class="text-gray-900 dark:text-white cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        role="option">
                                        <span class="block truncate" x-text="repo"></span>
                                    </li>
                                </template>
                                <li x-show="filteredSecondAccountRepositories.length === 0"
                                    class="text-gray-500 cursor-default select-none relative py-2 pl-3 pr-9">
                                    No matching repositories found
                                </li>
                                <li wire:click="refreshRepos(selectedSecondAccountId)"
                                    class="text-gray-900 dark:text-white select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                                    role="option">
                                    <span class="block truncate">Refresh</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- add labels --}}

                <div x-show="firstSearchTerm && secondSearchTerm" class="mb-6" @click.away="repoLabelOpen = false">
                    <h3 class="text-xl font-semibold mb-4">Add labels</h3>
                    <div class="relative" x-ref="firstLabelDropdownContainer">
                        <div class="flex flex-wrap gap-2 mb-2">
                            <!-- Display selected labels -->
                            <template x-for="(label, index) in selectedLabels" :key="index">
                                <div class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white rounded-md px-3 py-1 flex items-center space-x-2">
                                    <span x-text="label"></span>
                                    <button @click="removeLabel(index)" type="button" class="text-gray-500 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div class="flex">
                            <input
                                    x-model="labelSearchTerm"
                                    @input="filteredRepoLabels(); repoLabelOpen = true"
                                    @focus="repoLabelOpen = true"
                                    @click="repoLabelOpen = true"
                                    type="text"
                                    class="w-full border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 rounded-md shadow-sm pl-3 pr-10 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500 dark:text-white"
                                    placeholder="Search labels...">
                            <button @click="repoLabelOpen = !repoLabelOpen" type="button"
                                    class="border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 rounded-md shadow-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                     viewBox="0 0 20 20"
                                     fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                          clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>

                        <div x-show="repoLabelOpen"
                             class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                            <ul tabindex="-1" role="listbox" aria-labelledby="listbox-label"
                                aria-activedescendant="listbox-option-3">
                                <template x-for="label in filteredRepositorieLabels" :key="label">
                                    <li @click="toggleLabelSelection(label)"
                                        :class="{'bg-gray-100 dark:bg-gray-700': isLabelSelected(label)}"
                                        class="text-gray-900 dark:text-white cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        role="option">
                                        <span class="block truncate" x-text="label"></span>
                                    </li>
                                </template>
                                <li x-show="filteredRepositorieLabels.length === 0"
                                    class="text-gray-500 cursor-default select-none relative py-2 pl-3 pr-9">
                                    No matching labels found
                                </li>
                                <li wire:click="refreshRepos(selectedFirstAccountId)"
                                    class="text-gray-900 dark:text-white select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                                    role="option">
                                    <span class="block truncate">Refresh</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <button
                    @click="accountSync()"
                    x-show="selectedLabels.length > 0"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    Sync Accounts
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
                    if(this.selectedFirstAccount && this.selectedSecondAccount)
                    {
                        this.selectedFirstAccountId = this.selectedFirstAccount ? this.selectedFirstAccount : null;
                        this.selectedSecondAccountId = this.selectedSecondAccount ? this.selectedSecondAccount : null;
                        this.$wire.firstAccountSelected(this.selectedFirstAccountId);
                        this.$wire.secondAccountSelected(this.selectedSecondAccountId);
                        this.firstSearchTerm = this.firstSelectedRepo ? this.firstSelectedRepo : '';
                        this.secondSearchTerm = this.secondSelectedRepo ? this.secondSelectedRepo : '';
                        this.$wire.selectLables(this.firstSearchTerm);
                        this.selectedLabels = this.selectedLables ? this.selectedLables : [];
                    }
                    document.addEventListener('click', (e) => {
                        this.closeDropdownIfClickedOutside(e, this.$refs);
                    });
                    window.addEventListener('error', event => {
                        const messageElement = document.getElementById('errorMessage');
                        messageElement.style.display = 'block';
                        messageElement.innerHTML = event.detail;
                        setTimeout(() => {
                            messageElement.style.display = 'none';
                        }, 3000);
                    });
                },
                firstAccountOpenContainer: false,
                secondAccountOpenContainer: false,
                showAccounts: false,
                firstRepoOpen: false,
                repoLabelOpen: false,
                secondRepoOpen: false,
                selectedFirstAccountId: null,
                selectedSecondAccountId: null,
                selectedRepo: null,
                processingRepos: {},
                deletingRepos: {},
                firstSearchTerm: '',
                labelSearchTerm: '',
                secondSearchTerm: '',
                filteredFirstAccountRepositories: [],
                filteredRepositorieLabels: [],
                filteredSecondAccountRepositories: [],
                selectedLabels: [],
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
                async filterFirstAccountRepositories() {
                    if (this.firstAccountRepositories.length) {
                        this.filteredFirstAccountRepositories = await this.filterFirstRepositories(this.firstAccountRepositories);
                    }
                },
                async filteredRepoLabels() {
                    console.log(this.selectedLables);
                    if (this.selectedLables.length) {
                        this.filteredRepositorieLabels = await this.filterRepositorieLabels(this.selectedLables);
                    }
                },
                async filterSecondAccountRepositories() {
                    if (this.secondAccountRepositories.length) {
                        this.filteredSecondAccountRepositories = await this.filterSecondRepositories(this.secondAccountRepositories);
                        console.log('this', this.filteredSecondAccountRepositories);
                    }
                },
                async filterFirstRepositories(accountRepositories) {
                    return accountRepositories.filter(repo => repo.toLowerCase().includes(this.firstSearchTerm.toLowerCase()));
                },
                async filterRepositorieLabels(repositorieLabel) {
                    return repositorieLabel.filter(label => label.toLowerCase().includes(this.labelSearchTerm.toLowerCase()));
                },
                async filterSecondRepositories(accountRepositories) {
                    return accountRepositories.filter(repo => repo.toLowerCase().includes(this.secondSearchTerm.toLowerCase()));
                },
                closeDropdownIfClickedOutside(event, refs) {
                    if (!refs.firstDropdownContainer.contains(event.target)) {
                        this.firstRepoOpen = false;
                    }
                    // if (!refs.firstLabelDropdownContainer.contains(event.target)) {
                    //     this.repoLabelOpen = false;
                    // }
                    if (!refs.secondDropdownContainer.contains(event.target)) {
                        this.secondRepoOpen = false;
                    }
                },
                toggleLabelSelection(label) {
                    if (this.isLabelSelected(label)) {
                        this.selectedLabels = this.selectedLabels.filter(l => l !== label);
                    } else {
                        this.selectedLabels.push(label);
                    }
                },

                isLabelSelected(label) {
                    return this.selectedLabels.includes(label);
                },

                removeLabel(index) {
                    this.selectedLabels.splice(index, 1);
                },
                accountSync() {
                    let data = {
                        account_from: this.selectedFirstAccountId,
                        account_to: this.selectedSecondAccountId,
                        repo_from: this.firstSearchTerm,
                        repo_to: this.secondSearchTerm,
                        labels: this.selectedLabels
                    }
                    this.$wire.syncAccount(data);
                }

                // async filteredRepoLabels() {
                //     console.log(this.selectedLabels);
                //     if (this.selectedLabels.length) {
                //         this.filteredRepositorieLabels = await this.filterRepositorieLabels(this.selectedLabels);
                //     }
                // },

                // async filterRepositorieLabels(repositorieLabel) {
                //     return repositorieLabel.filter(label => label.toLowerCase().includes(this.labelSearchTerm.toLowerCase()));
                // },
            }

        }
    </script>
</div>
