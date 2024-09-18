<?php

namespace App\Livewire;

use App\Models\Account;
use Livewire\Component;
use App\Models\Project;
use Illuminate\Support\Facades\Http;

class ProjectSettings extends Component
{
    public Project $project;
    public $projectName;
    public $projectDescription;
    public $projectVisibility;
    public $githubToken;
    public $githubAccounts = [];
    public $selectedAccount = '';
    public $repositories = [];

    public $activeTab = 'general';

    protected $listeners = ['accountSelected'];

    public function mount(Project $project, $tab = 'general')
    {
        $this->project = $project;
        $this->projectName = $project->name;
        $this->projectDescription = $project->description;
        $this->projectVisibility = $project->visibility;
        $this->activeTab = in_array($tab, ['general', 'github', 'users']) ? $tab : 'general';

        $this->githubAccounts = $project->accounts->pluck('name', 'id')->toArray();
    }

    public function accountSelected($accountId)
    {
        $this->selectedAccount = $accountId;
        $this->loadRepositories();
    }

    public function loadRepositories()
    {
        if ($this->selectedAccount) {
            $account = Account::find($this->selectedAccount);
            if ($account) {
                $this->repositories = $account->repositories()->pluck('name')->toArray();
            }
        }
    }

    public function refreshRepos()
    {
        if (!$this->selectedAccount) {
            session()->flash('error', 'Please select a GitHub account first.');
            return;
        }

        $account = Account::find($this->selectedAccount);

        if (!$account) {
            session()->flash('error', 'Selected account not found.');
            return;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $account->github_token,
                'Accept' => 'application/vnd.github.v3+json',
            ])->get('https://api.github.com/installation/repositories');

            if ($response->successful()) {
                $repos = collect($response->json()['repositories'])->pluck('name')->toArray();

                // Update or create repositories
                foreach ($repos as $repoName) {
                    $account->repositories()->updateOrCreate(['name' => $repoName]);
                }

                $this->repositories = $repos;
                session()->flash('message', 'Repositories refreshed successfully.');
            } else {
                session()->flash('error', 'Failed to fetch repositories. Please try again.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while fetching repositories: ' . $e->getMessage());
        }
    }

    public function updateGeneralSettings()
    {
        $this->validate([
            'projectName' => 'required|string|max:255',
            'projectDescription' => 'nullable|string',
            'projectVisibility' => 'required|in:public,private',
        ]);

        $this->project->update([
            'name' => $this->projectName,
            'description' => $this->projectDescription,
            'visibility' => $this->projectVisibility,
        ]);

        session()->flash('message', 'Project settings updated successfully.');
    }

    public function connectGitHub()
    {
        $this->validate([
            'githubToken' => 'required|string',
        ]);

        try {
            $response = Http::withToken($this->githubToken)->get('https://api.github.com/user');

            if ($response->successful()) {
                $user = $response->json();
                $this->githubAccounts = [$user['login']];
                $this->selectedAccount = $user['login'];
                $this->project->update(['github_token' => encrypt($this->githubToken)]);
                $this->loadRepositories();
                session()->flash('message', 'GitHub account connected successfully.');
            } else {
                session()->flash('error', 'Failed to connect GitHub account. Please check your token.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while connecting to GitHub.');
        }

        $this->githubToken = '';
    }

    public function selectRepository($repo)
    {
        $this->project->update(['github_repository' => $repo]);
        session()->flash('message', 'GitHub repository selected successfully.');
    }

    public function disconnectGitHub()
    {
        $this->project->update(['github_token' => null, 'github_repository' => null]);
        $this->githubAccounts = [];
        $this->selectedAccount = '';
        $this->repositories = [];
        session()->flash('message', 'GitHub account disconnected successfully.');
    }

    private function loadGitHubData()
    {
        if ($this->project->github_token) {
            $this->connectGitHub();
        }
    }

    public function changeTab($tab)
    {
        $this->activeTab = in_array($tab, ['general', 'github', 'users']) ? $tab : 'general';
    }


    public function render()
    {
        return view('livewire.project-settings');
    }
}
