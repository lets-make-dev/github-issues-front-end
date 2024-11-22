<?php

namespace App\Livewire;

use App\Concerns\ProjectSelectionCacheManager;
use App\Models\Project;
use Livewire\Component;

class ConnectGitHubRepo extends Component
{
    use ProjectSelectionCacheManager;

    public Project $project;

    public function mount(Project $project)
    {
        $this->project = $project;
    }

    public function render()
    {
        return view('livewire.connect-git-hub-repo');
    }

    public function redirectToGitHubApp()
    {
        $this->cacheProjectId($this->project->id);

        return redirect()->away('https://github.com/apps/hubbub-the-missing-front-end/installations/new');
    }
}
