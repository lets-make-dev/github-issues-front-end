@section('title', $project->name . ' - Project Settings - ' . config('app.name', 'Laravel'))
<div>
    <div x-data="{
        activeTab: @entangle('activeTab'),
        githubAccounts: @entangle('githubAccounts'),
        selectedAccount: @entangle('selectedAccount'),
        repositories: @entangle('repositories'),
        connectedRepositories: @entangle('connectedRepositories'),
        changeTab(tab) {
            this.$wire.changeTab(tab);
            const url = new URL(window.location);
            url.pathname = url.pathname.replace(/\/settings\/.*$/, '/settings/' + tab);
            history.pushState({}, '', url);
        }
    }" class="flex">
        <!-- Sidebar -->
        <aside class="w-64 dark:text-white self-start mr-8">
            <h1 class="text-2xl font-bold mb-5">Settings</h1>
            <nav>
                <a @click.prevent="changeTab('general')"
                   :class="{ 'bg-gray-200 dark:bg-gray-800': activeTab === 'general' }"
                   class="block py-2 px-4 mb-2 rounded hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors duration-200"
                   href="#">
                    <i class="fas fa-cog mr-2"></i> General
                </a>
                <a @click.prevent="changeTab('github')"
                   :class="{ 'bg-gray-200 dark:bg-gray-800': activeTab === 'github' }"
                   class="block py-2 px-4 mb-2 rounded hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors duration-200"
                   href="#">
                    <i class="fab fa-github mr-2"></i> GitHub
                </a>
                <a @click.prevent="changeTab('users')"
                   :class="{ 'bg-gray-200 dark:bg-gray-800': activeTab === 'users' }"
                   class="block py-2 px-4 mb-2 rounded hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors duration-200"
                   href="#">
                    <i class="fas fa-users mr-2"></i> Users
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1">

            <div class="">
                <div class=" dark:text-white">
                    <!-- General Settings -->
                    <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100">
                        <h3 class="text-2xl font-semibold mb-4">General Settings</h3>
                        <form wire:submit.prevent="updateGeneralSettings" class="space-y-6">
                            @if (session()->has('message'))
                                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                                     role="alert">
                                    {{ session('message') }}
                                </div>
                            @endif
                            <div>
                                <label for="project-name" class="block text-lg font-medium mb-2">Project Name</label>
                                <input type="text" id="project-name" wire:model="projectName"
                                       class="w-full px-4 py-2 rounded border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 focus:outline-none focus:ring-1 focus:ring-gray-800">
                                @error('projectName') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="project-description" class="block text-lg font-medium mb-2">Project
                                    Description</label>
                                <textarea id="project-description" wire:model="projectDescription" rows="4"
                                          class="w-full px-4 py-2 rounded border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 focus:outline-none focus:ring-1 focus:ring-gray-800"></textarea>
                                @error('projectDescription') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="project-visibility" class="block text-lg font-medium mb-2">Project
                                    Visibility</label>
                                <select id="project-visibility" wire:model="projectVisibility"
                                        class="w-full px-4 py-2 rounded border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 focus:outline-none focus:ring-1 focus:ring-gray-800">
                                    <option value="public">Public</option>
                                    <option value="private">Private</option>
                                </select>
                                @error('projectVisibility') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <button type="submit"
                                    class="px-6 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition-colors duration-200">
                                Save Changes
                            </button>
                        </form>
                    </div>

                    <!-- GitHub Integration -->
                    <template x-if="activeTab === 'github'">
                        <div class="min-h-[60vh]"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100">
                            <x-settings.github/>
                        </div>
                    </template>

                    <!-- Users Management -->
                    <div x-show="activeTab === 'users'" x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100">
                        <h3 class="text-2xl font-semibold mb-4">Users Management</h3>
                        <div class="mb-6">
                            <h3 class="text-xl font-semibold mb-4">Invite User</h3>
                            <form class="flex items-center space-x-4">
                                <input type="email" placeholder="Enter email address"
                                       class="flex-1 px-4 py-2 rounded border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 focus:outline-none focus:ring-1 focus:ring-gray-800">
                                <select class="px-3 pr-7 py-2 rounded border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 focus:outline-none focus:ring-1 focus:ring-gray-800">
                                    <option value="admin">Admin</option>
                                    <option value="member">Member</option>
                                    <option value="viewer">Viewer</option>
                                </select>
                                <button type="submit"
                                        class="px-6 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition-colors duration-200">
                                    Invite
                                </button>
                            </form>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold mb-4">Current Users</h3>
                            <table class="w-full border border-gray-300 dark:border-gray-700">
                                <thead class="bg-gray-200 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-2 text-left">Name</th>
                                    <th class="px-4 py-2 text-left">Email</th>
                                    <th class="px-4 py-2 text-left">Role</th>
                                    <th class="px-4 py-2 text-left">Actions</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-300 dark:divide-gray-700 rounded-b">
                                <tr>
                                    <td class="px-4 py-2">John Doe</td>
                                    <td class="px-4 py-2">john@example.com</td>
                                    <td class="px-4 py-2">Admin</td>
                                    <td class="px-4 py-2">
                                        <button class="text-blue-600 hover:text-blue-800 mr-2">Edit</button>
                                        <button class="text-red-600 hover:text-red-800">Remove</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">Jane Smith</td>
                                    <td class="px-4 py-2">jane@example.com</td>
                                    <td class="px-4 py-2">Member</td>
                                    <td class="px-4 py-2">
                                        <button class="text-blue-600 hover:text-blue-800 mr-2">Edit</button>
                                        <button class="text-red-600 hover:text-red-800">Remove</button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
