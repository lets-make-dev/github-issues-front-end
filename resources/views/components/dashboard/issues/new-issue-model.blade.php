<div x-data="newIssueModelData();">
    <!-- Create new issue Modal -->
    <div x-show="showModalNewIssueModel" x-cloak class="fixed top-0 right-0 bottom-0 left-0 inset-0 bg-gray-800 bg-opacity-75 flex justify-center items-center z-50">
        <div class="bg-white dark:bg-gray-900 dark:text-white p-6 rounded-lg shadow-lg w-full max-w-lg relative">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold">Create a New Issue</h2>
                <button @click="closePopUp();" class="text-gray-700 dark:text-gray-300 text-3xl absolute top-1 right-2">&times;</button>
            </div>

            <!-- new issue create Form -->
            <form wire:submit.prevent="createIssue()" class="mt-3">
                <label for="title" class="block text-gray-700 dark:text-gray-300">Title:</label>
                <input type="text" wire:model.defer="title" id="title"
                    class="w-full p-2 border rounded-md dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-white mt-2"
                    placeholder="Enter a title for your issue">
                @error('title')
                    <span class="text-red-500 text-sm mt-1 block w-full">{{ $message }}</span>
                @enderror

                {{-- model form --}}
                <div class="mb-6 mt-4" @click.away="labelOpen = false">
                    <span class="block text-gray-700 dark:text-gray-300">Add labels:</span>
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

                        <div class="flex relative">
                            <input
                                    x-model="searchTerm"
                                    @input="filteredRepoLabels(); labelOpen = true"
                                    @focus="labelOpen = true"
                                    @click="labelOpen = true"
                                    type="text"
                                    class="w-full border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 rounded-md shadow-sm pl-3 pr-10 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500 dark:text-white"
                                    placeholder="Search labels...">
                            <button @click="labelOpen = !labelOpen;" type="button"
                                    class="border-none absolute right-px top-px border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 rounded-md shadow-sm px-2 py-2 text-sm focus:outline-none focus:ring-0 focus:ring-gray-500 focus:border-gray-500">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                     viewBox="0 0 20 20"
                                     fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                          clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>

                        <div x-show="labelOpen"
                             class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                            <ul tabindex="-1" role="listbox" aria-labelledby="listbox-label"
                                aria-activedescendant="listbox-option-3">
                                <template x-for="(label, index) in filteredLabels" :key="label">
                                    <li @click="toggleLabelSelection(label)"
                                        :class="{'bg-gray-100 dark:bg-gray-700': isLabelSelected(label)}"
                                        class="text-gray-900 dark:text-white cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        role="option">
                                        <span class="block truncate" x-text="label"></span>
                                    </li>
                                </template>
                                <li x-show="allLabels.length === 0"
                                    class="text-gray-500 cursor-default select-none relative py-2 pl-3 pr-9">
                                    No matching labels found
                                </li>
                                {{-- <li wire:click="refreshRepos(selectedFirstAccountId)"
                                    class="text-gray-900 dark:text-white select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                                    role="option">
                                    <span class="block truncate">Refresh</span>
                                </li> --}}
                            </ul>
                        </div>
                    </div>
                </div>
                <label for="description" class="block text-gray-700 dark:text-gray-300 mt-4">Description:</label>
                <div wire:ignore>
                    <textarea wire:model.defer="description" id="description" rows="3"
                        class="w-full p-2 border rounded-md dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-white mt-2"
                        placeholder="Enter a description for your issue"></textarea>
                </div>
                @error('description')
                    <span class="text-red-500 text-sm mt-1 block w-full">{{ $message }}</span>
                @enderror

                <button @click="saveSelectedLabels()" type="submit"
                    class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition duration-200">
                    <span wire:loading.class.remove="hidden" class="hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                    </span>
                    Submit New Issue
                </button>
                <button type="button" @click="closePopUp();" class="mt-4 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Close
                </button>
            </form>
        </div>
    </div>
    <script>
        function newIssueModelData() {
            return {
                labelDropdown : false,
                showModalNewIssueModel : false,
                selectedLabels : [],
                labelOpen : false,
                filteredLabels : [],
                searchTerm : '',
                easyMDE : null,
                element : null,
                init(){
                    this.element = document.getElementById('description') ? document.getElementById('description') : null;
                    this.filteredLabels = this.allLabels ? this.allLabels : [],

                    window.addEventListener('open-new-issue-modal', event => {
                            this.showModalNewIssueModel = true ;
                            this.initEasyMDE();
                    });
                     window.addEventListener('issueCreatedSucessfully', event => {
                        this.showModalNewIssueModel = false ;
                        this.selectedLabels = [];
                         const messageElement = document.getElementById('successMessage');
                         messageElement.style.display = 'block';
                         messageElement.innerHTML  = event.detail;
                         this.closePopUp()
                         setTimeout(() => {
                             messageElement.style.display = 'none';
                         }, 2000);
                     });
                },

                initEasyMDE() {
                    setTimeout(() => {
                    this.easyMDE = new EasyMDE({
                        maxHeight: '40vh',
                        element: this.element,
                        uploadImage: true,
                        imageUploadFunction: this.uploadImage.bind(this),
                        imageMaxSize: 2 * 1024 * 1024,
                        imageAccept: 'image/*',
                    });
                }, 200);
                },

                toggleLabelSelection(label) {
                    console.log(label);

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

                saveSelectedLabels() {
                    console.log(document.querySelector('#title').value);

                    if(document.querySelector('#title').value == '')
                    {
                        console.log('we found the error');

                        return;
                    }
                    let labels = Object.keys(this.allLabels).filter(key => this.selectedLabels.includes(this.allLabels[key]));
                    let descriptionValue = this.easyMDE.value();
                    this.$wire.setvalues(labels, descriptionValue);
                },

                async filteredRepoLabels() {
                    if (Object.keys(this.allLabels).length) {
                        this.filteredLabels = await this.filterLabels(this.allLabels);
                    }
                },

                async filterLabels(label) {
                    return Object.fromEntries(
                        Object.entries(label).filter(
                            ([key, value]) => value.toLowerCase().includes(this.searchTerm.toLowerCase())
                        )
                    );
                },
                closePopUp() {
                    if (this.easyMDE) {
                        this.easyMDE.toTextArea();
                        this.easyMDE = null;
                    }
                    if (this.element) {
                        this.element.value = '';
                    }
                    this.showModalNewIssueModel = false;
                },

                uploadImage(file) {
                let formData = new FormData();
                formData.append('image', file);

                console.log(formData);
                axios.post('/upload-image', formData, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'multipart/form-data',
                    }
                })
                .then(response => {
                    const cm = this.easyMDE.codemirror;
                    const cursorPosition = cm.getCursor();
                    const imageMarkdown = `![${response.data.name}](${response.data.url})`;
                    cm.replaceRange(imageMarkdown, cursorPosition);
                    const newCursorPosition = {
                        line: cursorPosition.line + 1,
                        ch: 0
                    };
                    cm.setCursor(newCursorPosition);
                })
                .catch(error => {
                    console.log(error);
                });

            }
            }
        }
     </script>
    </div>
