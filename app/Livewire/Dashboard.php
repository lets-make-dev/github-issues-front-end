<?php

namespace App\Livewire;

use Exception;
use App\Models\Issue;
use App\Models\Account;
use App\Models\Comment;
use App\Models\Project;
use Livewire\Component;
use App\Models\Repository;
use Illuminate\Support\Js;
use App\Enums\GithubIssueState;
use App\Models\GitHubIntegration;
use App\Concerns\GithubApiManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

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

    public $comments = [];

    public $selectedIssueNumber = '';

    public $accounts = [];

    public $selectedAccount = '';

    public $showCreateButton = false;

    public $title = '';

    public $description = '';

    public $allLabels = [];

    public $labels = [];

    public $primeryAccountId = '';

    public $integratedAccounts;

    public function mount(Project $project): void
    {
        $this->project = $project;

        // Fetch integrated accounts safely
        $integration = GitHubIntegration::select('account_from', 'account_to')
            ->where('project_id', $this->project->id)
            ->first();

        if ($integration) {
            $this->integratedAccounts = $integration->toArray();
            $this->primeryAccountId = $this->integratedAccounts['account_from'];

            // Fetch accounts using a reusable query method
            $this->accounts = $this->getAccountQuery()->pluck('name', 'id');
        } else {
            $this->integratedAccounts = [];
            $this->accounts = [];
        }

        // Handle repos
        if (!empty($this->repos)) {
            $this->selectedRepo = $this->selectedRepo ?: $this->repos[0];
            $this->fetchIssues();
        }
    }

    // Reusable query method
    public function getAccountQuery()
    {
        return Account::whereIn('id', array_values($this->integratedAccounts));
    }


    public function updatedSelectedAccount(): void
    {
        if($this->selectedAccount == "null")
        {
            $this->repos = [];
            // $this->showCreateButton = false;
            return;
        }

        $this->repos = Repository::where('account_id', $this->selectedAccount)->pluck('name')->toArray();
        // $this->showCreateButton = true;
    }

    public function updatedSelectedRepo(): void
    {
        if($this->selectedRepo != "null")
        {
            $this->showCreateButton = true;
            $this->fetchIssues();
            $this->allLabels = json_decode(Repository::where(['account_id' => $this->selectedAccount],['name' => $this->selectedRepo])->value('labels'));
            return;
        }
            $this->showCreateButton = false;
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
        $account = $this->getAccountQuery()->whereRelation('repositories', 'name', $this->selectedRepo)->first();

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
                    $repositoryId = $this->project->repositories()->where('name', $this->selectedRepo)->first()?->id;
                    $commentsCount = Comment::byIssueAndProject($issue['number'], $this->project->id, $repositoryId)->count();

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
                        'comments_count' => $commentsCount,
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

    public function addComment()
    {
        // $this->emit('commentAddedSucessfully', $issueNumber);
        $issueNumber = $this->selectedIssueNumber;

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

    public function showComments($issueNumber)
    {
        $this->comments = Comment::byIssueAndProject($issueNumber, $this->project->id, $this->project->repositories()
            ->where('name', $this->selectedRepo)->first()?->id)
            ->get();
        $this->dispatch('open-modal');
    }

    public function showAddCommentModel($issueNumber)
    {
        $this->selectedIssueNumber = $issueNumber;
        $this->dispatch('open-add-comment-modal');
    }

    public function showNewIssueModel()
    {
        $this->dispatch('open-new-issue-modal');
    }

    public function render()
    {
        return view('livewire.dashboard');
    }

    public function createIssue()
    {
        $this->validate([
            'title' => 'required|max:255',
            'description' => 'required',
        ]);
        $message = 'GitHubbub';
        // try {
            if(array_intersect(json_decode($this->project->gitHubIntegrations()->first()->labels), $this->labels))
            {
                $this->createGithubIssue($this->title, $this->description);
                $message = 'GitHub';
            }
            Issue::create([
                'title' => $this->title,
                'body' => $this->description,
                'project_id' => $this->project->id,
                'repository_name' => $this->selectedRepo,
                'github_issue_id' => $response['id']??null,
                'labels' => json_encode($this->labels),
                'account_id' => $this->getAccountQuery()->whereRelation('repositories', 'name', $this->selectedRepo)->first()->id
            ]);

            $this->dispatch('issueCreatedSucessfully', "Issue successfully created on $message.");

            $this->title = '';
            $this->description = '';
            $this->labels = [];
            $this->fetchIssues();
        // } catch (Exception $e) {
        //     session()->flash('error', 'Could not create issue. Please try again later.');
        // }
    }

    public function createGithubIssue($title, $description)
    {
        try {
            $account = $this->getAccountQuery()->whereRelation('repositories', 'name', $this->selectedRepo)->first();
                $response = Http::withToken($account->github_token)
                    ->withHeaders(['Accept' => 'application/vnd.github.v3+json'])
                    ->post("https://api.github.com/repos/{$account->name}/{$this->selectedRepo}/issues", [
                        'title' => $title,
                        'body' => $description,
                        'labels' => $this->labels
                    ])->json();

            return $response;

        } catch (Exception $e) {
            session()->flash('error', 'Could not create issue. Please try again later.');
        }
    }

    public function saveSelectedLabels($labels)
    {
        $this->labels = $labels;
    }
}
// Check if the response is successful
// Return the JSON response from the API
// Throw an exception if the API request fails
