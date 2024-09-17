<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Project;

class ProjectSelector extends Component
{
    use WithPagination;

    public $newProjectName = '';
    public $selectedProjectId = '';
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';

    protected $queryString = ['search', 'sortField', 'sortDirection'];

    public function createProject()
    {
        $this->validate([
            'newProjectName' => 'required|min:3|max:255|unique:projects,name',
        ]);

        $project = Project::create([
            'name' => $this->newProjectName,
        ]);

        if (!auth()->user()->github_token) {
            return redirect()->route('projects.settings.github-connect', $project);
        }

        $this->selectedProjectId = $project->id;
        $this->redirectToProject();
    }

    public function updatedSelectedProjectId()
    {
        if ($this->selectedProjectId) {
            $this->redirectToProject();
        }
    }

    public function redirectToProject()
    {
        return redirect()->route('projects.show', $this->selectedProjectId);
    }

    public function selectProject($projectId)
    {
        $this->selectedProjectId = $projectId;
        $this->redirectToProject();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $projects = Project::search($this->search)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.project-selector', [
            'projects' => $projects,
        ]);
    }
}
