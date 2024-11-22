<?php

namespace App\Livewire;

use App\Concerns\GithubApiManager;
use App\Concerns\ProjectSelectionCacheManager;
use App\Models\Account;
use App\Models\Project;
use App\Models\Repository;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class ProjectSettings extends Component
{
    use GithubApiManager, ProjectSelectionCacheManager;

    public Project $project;

    public string $projectName;

    public ?string $projectDescription;

    public string $projectVisibility;

    public string $githubToken;

    public array $githubAccounts = [];

    public string $selectedAccount = '';

    public array $repositories = [];

    public array $connectedRepositories = [];

    public string $activeTab = 'general';

    protected $listeners = ['accountSelected'];

    public function mount(Project $project, $tab = 'general'): void
    {
        $this->project = $project;
        $this->projectName = $project->name;
        $this->projectDescription = $project->description;
        $this->projectVisibility = $project->visibility;
        $tab = 'github';
        $this->activeTab = in_array($tab, ['general', 'github', 'users']) ? $tab : 'general';

        $this->githubAccounts = $project->accounts->pluck('name', 'id')->toArray();
        $this->loadConnectedRepositories();
    }

    public function accountSelected($accountId): void
    {
        $this->selectedAccount = $accountId;
        $this->loadRepositories();
    }

    public function loadRepositories(): void
    {
        if ($this->selectedAccount) {
            $account = Account::find($this->selectedAccount);
            if ($account) {
                $this->repositories = $account->repositories()
                    ->whereNotIn('id', array_column($this->connectedRepositories, 'id'))
                    ->pluck('name')->toArray();
            }
        }
    }

    public function refreshRepos(): void
    {
        if (! $this->selectedAccount) {
            session()->flash('error', 'Please select a GitHub account first.');

            return;
        }

        $account = Account::find($this->selectedAccount);

        if (! $account) {
            session()->flash('error', 'Selected account not found.');

            return;
        }
        try {
            $refreshToken = $this->refreshGitHubToken($account);
            $repos = $this->getRepositories($refreshToken);
            if (count($repos)) {
                $repos = collect($repos)->pluck('name')->toArray();
                // Update or create repositories
                foreach ($repos as $repoName) {
                    $account->repositories()->updateOrCreate(['name' => $repoName, 'user_id' => $account->user_id]);
                }
                $this->repositories = $repos;
                //                session()->flash('message', 'Repositories refreshed successfully.');
            } else {
                $this->dispatch('error', 'Failed to fetch repositories. Please try again.');
                //                session()->flash('error', 'Failed to fetch repositories. Please try again.');
            }
        } catch (\Exception $e) {
            $this->dispatch('error', 'An error occurred while fetching repositories: '.$e->getMessage());

        }
    }

    public function updateGeneralSettings(): void
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

    public function connectGitHub(): void
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

    public function selectRepository($repo): void
    {
        $this->project->update(['github_repository' => $repo]);
        session()->flash('message', 'GitHub repository selected successfully.');
    }

    public function disconnectGitHub(): void
    {
        $this->project->update(['github_token' => null, 'github_repository' => null]);
        $this->githubAccounts = [];
        $this->selectedAccount = '';
        $this->repositories = [];
        session()->flash('message', 'GitHub account disconnected successfully.');
    }

    public function disconnectRepository($repoId): void
    {
        $this->project->repositories()->detach($repoId);
        $this->loadConnectedRepositories();
        session()->flash('message', 'Repository disconnected successfully.');
    }

    private function loadGitHubData(): void
    {
        if ($this->project->github_token) {
            $this->connectGitHub();
        }
    }

    public function changeTab($tab): void
    {
        $this->activeTab = in_array($tab, ['general', 'github', 'users']) ? $tab : 'general';
    }

    public function loadConnectedRepositories(): void
    {
        $this->connectedRepositories = $this->project->repositories->load('account:id,name')->toArray();

        if ($this->connectedRepositories) {
            $this->loadRepositories();
        }
    }

    public function render()
    {
        return view('livewire.project-settings');
    }

    public function connectRepository($repoName): void
    {
        $repository = Repository::firstOrCreate(['name' => $repoName]);
        $this->project->repositories()->syncWithoutDetaching([$repository->id]);
        $this->loadConnectedRepositories();
        session()->flash('message', 'Repository connected successfully.');
    }

    public function connectGitHubAccount(Project $project)
    {
        $this->cacheProjectId($project->id);

        return redirect()->away('https://github.com/apps/hubbub-the-missing-front-end/installations/new');
    }
}
