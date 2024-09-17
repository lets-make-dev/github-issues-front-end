<?php

namespace App\Livewire;

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
    public $repositories = [ 'bytelaunch/my-house' ];

    public $activeTab = 'general';

    public function mount(
        Project $project,
        $tab = 'general'
    )
    {

        $this->project = $project;
        $this->projectName = $project->name;
        $this->projectDescription = $project->description;
        $this->projectVisibility = $project->visibility;
        $this->activeTab = in_array($tab, ['general', 'github', 'users']) ? $tab : 'general';

        $this->githubAccounts = $project->accounts->pluck('name', 'id');
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

    public function loadRepositories()
    {
        ray(1);
        if ($this->selectedAccount) {
            $response = Http::withToken(decrypt($this->project->github_token))
                ->get("https://api.github.com/users/{$this->selectedAccount}/repos");

            if ($response->successful()) {
                $this->repositories = collect($response->json())->pluck('name')->toArray();
            }
        }
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
