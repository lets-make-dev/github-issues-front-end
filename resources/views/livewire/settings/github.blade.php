<div>
    <h3 class="text-2xl font-semibold mb-4">GitHub Integration</h3>
    <div class="mb-6">
        <h3 class="text-xl font-semibold mb-4">Connect GitHub Account</h3>
        <div class="flex items-center space-x-4">
            <select x-model="selectedAccount" @change="repositories = []" class="flex-1 px-4 py-2 rounded border-2 border-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-800">
                <option value="">Select a GitHub account</option>
                <template x-for="account in githubAccounts" :key="account">
                    <option :value="account" x-text="account"></option>
                </template>
            </select>
            <button @click="repositories = ['repo1', 'repo2', 'repo3']" class="px-6 py-2 bg-gray-900 text-white rounded hover:bg-gray-800 transition-colors duration-200">Connect</button>
        </div>
    </div>
    <div x-show="selectedAccount && repositories.length > 0" class="mb-6">
        <h3 class="text-xl font-semibold mb-4">Select Repository</h3>
        <select class="w-full px-4 py-2 rounded border-2 border-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-800">
            <option value="">Select a repository</option>
            <template x-for="repo in repositories" :key="repo">
                <option :value="repo" x-text="repo"></option>
            </template>
        </select>
    </div>
    <div x-show="selectedAccount" class="mb-6">
        <h3 class="text-xl font-semibold mb-4">Connected GitHub Accounts</h3>
        <ul class="space-y-2">
            <template x-for="account in githubAccounts" :key="account">
                <li class="flex justify-between items-center p-2 bg-white rounded shadow">
                    <span x-text="account"></span>
                    <button class="text-red-600 hover:text-red-800">Disconnect</button>
                </li>
            </template>
        </ul>
    </div>
</div>