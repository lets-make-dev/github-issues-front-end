<?php

namespace App\Livewire;

use Livewire\Component;

class Dashboard extends Component
{
    public $groupBy = 'none';
    public $showClosed = false;
    public $issues = [];
    public $repoName = 'user/repository'; // Add this line
    public $repos = ['Admin Dashboard', 'iOS App']; // Add available repos
    public $selectedRepo = 'Admin Dashboard'; // Default selected repo

    public function mount($repoName = null)
    {

        if (!auth()->user()->github_token) {
            return redirect()->route('projects.settings.github-connect', request('project'));
        }

        $this->repoName = $repoName ?? 'user/repository'; // Update this line
        // Sample issues data
        $this->issues = [
            [
                'id' => 1,
                'title' => 'Implement user authentication',
                'status' => 'Open',
                'description' => 'We need to add user authentication to the application.',
                'labels' => ['Feature', 'Security'],
                'creator' => 'johndoe',
                'comments' => 3,
                'milestone' => 'v1.0 Release',
                'estimated_hours' => 20,
                'logged_hours' => 5,
                'estimated_completion_date' => '2023-06-30',
                'priorities' => ['High', 'Security']
            ],
            [
                'id' => 2,
                'title' => 'Fix responsive layout on mobile',
                'status' => 'Open',
                'description' => 'The layout is broken on mobile devices. We need to make it responsive.',
                'labels' => ['Bug', 'UI'],
                'creator' => 'janedoe',
                'comments' => 2,
                'milestone' => 'v1.1 Release',
                'estimated_hours' => 10,
                'logged_hours' => 3,
                'estimated_completion_date' => '2023-07-15',
                'priorities' => ['Medium']
            ],
            [
                'id' => 3,
                'title' => 'Optimize database queries',
                'status' => 'Closed',
                'description' => 'Some database queries are slow. We need to optimize them for better performance.',
                'labels' => ['Performance', 'Backend'],
                'creator' => 'bobsmith',
                'comments' => 5,
                'milestone' => 'v1.0 Release',
                'estimated_hours' => 15,
                'logged_hours' => 15,
                'estimated_completion_date' => '2023-06-15',
                'priorities' => ['Medium High', 'Performance']
            ],
            [
                'id' => 4,
                'title' => 'Add dark mode support',
                'status' => 'Open',
                'description' => 'Users have requested a dark mode option. We should implement this feature.',
                'labels' => ['Feature', 'UI'],
                'creator' => 'alicejohnson',
                'comments' => 1,
                'milestone' => 'v1.2 Release',
                'estimated_hours' => 25,
                'logged_hours' => 0,
                'estimated_completion_date' => '2023-08-01',
                'priorities' => ['Low', 'User Experience']
            ],
            [
                'id' => 5,
                'title' => 'Implement email notifications',
                'status' => 'Open',
                'description' => 'We need to send email notifications for important events in the application.',
                'labels' => ['Feature', 'Backend'],
                'creator' => 'charliebravo',
                'comments' => 0,
                'milestone' => 'v1.1 Release',
                'estimated_hours' => 12,
                'logged_hours' => 2,
                'estimated_completion_date' => '2023-07-20',
                'priorities' => ['Medium', 'User Communication']
            ]
        ];
    }

    public function toggleShowClosed()
    {
        $this->showClosed = !$this->showClosed;
    }

    public function setGroupBy($value)
    {
        $this->groupBy = $value;
    }

    public function getGroupedIssuesProperty()
    {
        if ($this->groupBy === 'none') {
            return [['name' => 'All Issues', 'issues' => $this->filteredIssues()]];
        }

        return collect($this->filteredIssues())
            ->groupBy(function ($issue) {
                return $issue['milestone'] ?? 'No Milestone';
            })
            ->map(function ($issues, $name) {
                return ['name' => $name, 'issues' => $issues];
            })
            ->values()
            ->toArray();
    }

    private function filteredIssues()
    {
        return collect($this->issues)
            ->when(!$this->showClosed, function ($collection) {
                return $collection->where('status', 'Open');
            })
            ->values()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
