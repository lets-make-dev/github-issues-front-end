<?php

namespace App\Livewire;

use App\Models\Project;
use Livewire\Component;
use Illuminate\Support\Facades\Http;

class ConnectGitHubRepo extends Component
{
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
        return redirect()->away('https://github.com/apps/hubbub-the-missing-front-end/installations/new');
    }
}
