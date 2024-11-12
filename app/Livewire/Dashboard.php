<?php

namespace App\Livewire;

use App\Concerns\GithubApiManager;
use App\Enums\GithubIssueState;
use App\Models\Comment;
use App\Models\Project;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class Dashboard extends Component
{
    use GithubApiManager;

    public Project $project;

    public string $selectedRepo = '';

    public array $repos = [];

    public array|Collection $issues = [];

    public array|Collection $issuesFiltered = [];

    public array|Collection $issuesCopy = [];

    public bool $showClosed = false;

    public string $groupBy = '';

    public string $search = '';

    public string $newComment = '';

    protected array $queryString = ['selectedRepo', 'showClosed', 'groupBy', 'search'];

    public function mount(Project $project): void
    {
        $this->project = $project;
        $this->repos = $this->project->repositories()->pluck('name')->toArray();
        if (! empty($this->repos)) {
            $this->selectedRepo = $this->selectedRepo ?: $this->repos[0];
            $this->fetchIssues();
        }
    }

    public function updatedSelectedRepo(): void
    {
        $this->fetchIssues();
    }

    public function updatedShowClosed(): void
    {
        $this->fetchIssues();
    }

    public function updatedSearch(): void
    {
        if (empty($this->search)) {
            $this->issues = $this->issuesCopy;

            return;
        }
        $this->issues = collect($this->issues)
            ->filter(function ($issue) {
                return empty($this->search) || stripos($issue['title'], $this->search) !== false;
            });
    }

    public function fetchIssues(): void
    {
        if (empty($this->selectedRepo)) {
            return;
        }

        $account = $this->project->accounts()->whereRelation('repositories', 'name', $this->selectedRepo)->first();

        if (! $account) {
            $this->issues = [];

            return;
        }

        try {
            $response = Http::retry(2, 0, function ($exception, $request) use ($account) {
                if ($exception instanceof RequestException && $exception->response->status() === 401) {
                    $this->refreshGitHubToken($account);

                    return true;
                }

                return false;
            })->withToken($account->github_token)
                ->get("https://api.github.com/repos/{$account->name}/{$this->selectedRepo}/issues", [
                    'state' => $this->showClosed ? GithubIssueState::Closed->value : 'all',
                    'per_page' => 100,
                ])
                ->throw();

            $this->issues = collect($response->json())
                ->filter(function ($issue) {
                    return empty($this->search) || stripos($issue['title'], $this->search) !== false;
                })
                ->map(function ($issue) {
                    $repositoryId = $this->project->repositories()->where('name', $this->selectedRepo)->first()->id;
                    // $comments = Comment::where('issue_number', $issue['number'])
                    //     ->where('project_id', $this->project->id)
                    //     ->where('repository_id', $repositoryId)
                    //     ->get();
                    $comments = Comment::byIssueAndProject($issue['number'], $this->project->id, $repositoryId)->get();

                    return [
                        'title' => $issue['title'],
                        'description' => str()->markdown($issue['body']),
                        'status' => $issue['state'],
                        'creator' => $issue['user']['login'],
                        'comments' => $issue['comments'],
                        'labels' => collect($issue['labels'])->pluck('name', 'color')->toArray(),
                        'milestone' => $issue['milestone']['title'] ?? null,
                        'estimated_hours' => 0, // You might want to add custom logic for this
                        'priorities' => [], // You might want to add custom logic for this
                        'issue_id' => $issue['id'],
                        'issue_number' => $issue['number'],
                        'issues_comments' => $comments,
                        'comments_count' => $comments->count(),

                    ];
                })
                ->toArray();
            $this->issuesCopy = $this->issues;

        } catch (RequestException $e) {
            // Handle request errors
            $this->issues = [];
            // You might want to add error handling or logging here
        }
    }

    public function getGroupedIssuesProperty()
    {
        $groupedIssues = collect($this->issues)->groupBy($this->groupBy);

        return $groupedIssues->map(function ($group, $key) {
            return [
                'name' => ucfirst($key),
                'issues' => $group->toArray(),
            ];
        })->sortByDesc('name')->values()->toArray();

    }

    public function addComment($issueNumber)
    {
        // $this->emit('commentAddedSucessfully', $issueNumber);

        $this->validate([
            'newComment' => 'required',
        ]);

        try {

            $response = $this->addCommentToIssue(
                $issueNumber,
                $this->newComment,
            );
            if ($response->status() !== 201) {
                return $this->addError('newComment', 'Failed to add comment to GitHub.');
            }

            Comment::create([
                'content' => $this->newComment,
                'project_id' => $this->project->id,
                'repository_id' => $this->project->repositories()->where('name', $this->selectedRepo)->first()->id,
                'issue_number' => $issueNumber,
                'github_comment_id' => $response['id'],
            ]);

            $this->dispatch('commentAddedSucessfully', 'Comment successfully added to GitHub.');

            $this->newComment = '';
            $this->fetchIssues();

        } catch (Exception $e) {
            session()->flash('error', 'Could not add comment. Please try again later.');

            return response()->json([
                'error' => 'Could not add comment. Please try again later.',
            ], 500);
        }

    }

    private function addCommentToIssue($issueNumber, $comment)
    {
        $account = $this->project->accounts()->whereRelation('repositories', 'name', $this->selectedRepo)->first();

        $response = Http::withToken($account->github_token)
            ->withHeaders(['Accept' => 'application/vnd.github.v3+json'])
            ->post("https://api.github.com/repos/{$account->name}/{$this->selectedRepo}/issues/{$issueNumber}/comments", [
                'body' => $comment,
            ]);

        return $response;

    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
// Check if the response is successful
// Return the JSON response from the API
// Throw an exception if the API request fails
