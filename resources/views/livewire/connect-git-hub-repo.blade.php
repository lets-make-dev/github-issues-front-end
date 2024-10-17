@section('projectName', $project->name)

<div>
    <main class="max-w-7xl mx-auto mt-8 px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg rounded-lg p-6 border-2 border-gray-800">
            <div class="text-center py-12">
                <i class="fas fa-code-branch text-6xl text-gray-800 mb-4"></i>
                <h2 class="text-2xl font-semibold text-gray-800 mb-2">No Repositories Connected</h2>
                <p class="text-gray-600 mb-6">Connect a GitHub repository to start tracking issues.</p>
                <button
                    wire:click="redirectToGitHubApp"
                    class="bg-gray-800 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-colors duration-300 font-semibold">
                    <i class="fab fa-github mr-2"></i>Connect GitHub Repository
                </button>
            </div>
        </div>
    </main>
</div>
