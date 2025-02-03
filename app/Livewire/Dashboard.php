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
use App\Models\IssueSynced;
use GuzzleHttp\Promise\Create;
use App\Enums\GithubIssueState;
use App\Models\GitHubIntegration;
use App\Concerns\GithubApiManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Actions\GitHub\CreateGithubIssue;
use App\Actions\GitHub\CreateGithubComment;
use Illuminate\Http\Client\RequestException;

class Dashboard extends Component
{
    use GithubApiManager;

    public Project $project;

    public string $selectedRepo = '';

    public Repository $repos;

    public array|Collection $issues = [];

    public array|Collection $issuesFiltered = [];

    public array|Collection $issuesCopy = [];

    public bool $showClosed = false;

    public string $groupBy = '';

    public string $search = '';

    public string $newComment = '';

    protected array $queryString = ['selectedAccount', 'selectedRepo', 'showClosed', 'groupBy', 'search'];

    public $comments = [];

    public Issue $selectedIssue;

    public $accounts = [];

    public $selectedAccount = '';

    public $showCreateButton = false;

    public $title = '';

    public $description = '';

    public $allLabels = [];

    public $labels = [];

    public $primeryAccountId = '';

    public $integratedAccounts = [];

    public $primeryAccount = false;

    public Account $fromAccount;

    public Account $toAccount;

    public function mount(Project $project): void
    {
        $this->project = $project;
        // if(!empty(request()->query('selectedAccount')))
        // {
        //     $this->selectedAccount = request()->query('selectedAccount', '');
        //     $this->updatedSelectedAccount();
        // }

        // if(!empty(request()->query('selectedRepo')))
        // {
        //     $this->selectedRepo = request()->query('selectedRepo', '');
        //     $this->updatedSelectedRepo();
        // }
        // Fetch integrated accounts safely
        $integration = GitHubIntegration::with(['repoFrom', 'repoTo'])->where('project_id', $this->project->id)->first();

        if ($integration) {
            // $this->integratedAccounts = $integration->toArray();
            $this->fromAccount = $integration->accountFrom;
            $this->toAccount = $integration->accountTo;
            $this->repos = $integration->repoFrom;
            $this->allLabels = $integration->repoFrom->labels()->pluck('name', 'id')->toArray();
            // Fetch accounts using a reusable query method
            // $this->accounts = $this->getAccountQuery()->pluck('name', 'id');
            $this->accounts = $this->fromAccount;
        } else {
            $this->integratedAccounts = [];
            $this->accounts = [];
        }

        // fetch repos
        if ($this->repos) {
            $this->showCreateButton = true;
            $this->selectedRepo = $this->repos->name;
            // $this->fetchIssues();
            $this->getIssues();
        }
    }

    public function getIssues()
    {
        $this->issues = Issue::select('id','title', 'body', 'labels', 'creator', 'status', 'is_synced', 'issue_number')
            ->where([
                'project_id' => $this->project->id,
                'repository_id' => $this->repos->id,
                'account_id' => $this->fromAccount->id
            ])
            ->withCount('comments')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->toArray();
    }

    // Reusable query method
    public function getAccountQuery()
    {
        return Account::whereIn('id', $this->integratedAccounts);
    }

    public function gitHubIntegrationAccount()
    {
        return $this->project->gitHubIntegrations()->first();
    }


    public function updatedSelectedAccount(): void
    {
        if($this->selectedAccount == "null")
        {
            $this->repos = [];
            $this->showCreateButton = false;
            return;
        }

        // dd($this->project->gitHubIntegrations()->first());
        // $this->repos = Repository::where('account_id', $this->selectedAccount)->pluck('name')->toArray();
        $this->showCreateButton = true;

        $this->repos = GitHubIntegration::query()
        ->where(function ($query) {
            $query->where('account_from', $this->selectedAccount)
                  ->orWhere('account_to', $this->selectedAccount);
        })
        ->get()
        ->map(function ($integration) {
            $this->primeryAccount = $integration->account_from == $this->selectedAccount;
            return $integration->account_from == $this->selectedAccount
                ? $integration->repo_from
                : $integration->repo_to;
        })
        ->toArray();

        $this->selectedRepo = '';
        $this->issues = [];
        $this->showCreateButton = false;
    }

    // public function updatedSelectedRepo(): void
    // {
    //     if($this->selectedRepo != "null")
    //     {
    //         if($this->primeryAccount)
    //         {
    //             $this->showCreateButton = true;
    //         }
    //         $this->fetchIssues();
    //         $this->allLabels = json_decode(Repository::where(['account_id' => $this->selectedAccount],['name' => $this->selectedRepo])->value('labels'));
    //         return;
    //     }
    //         $this->showCreateButton = false;
    // }

    public function updatedShowClosed(): void
    {
        // $this->fetchIssues();
        $this->getIssues();
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
                        'description' => $issue['body'] ? str()->markdown($issue['body']) : '',
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
        $syncedIssue = $this->selectedIssue?->syncedIssues()?->first();

        $this->validate([
            'newComment' => 'required',
        ]);

        try {

            $accountFrom = $this->getAndRefreshAccount();

            $response = $this->createGithubComment(
                account: $accountFrom,
                repo: $this->selectedRepo,
                issueNumber: $this->selectedIssue->issue_number
            );

            if (!$response) {
                return $this->addError('newComment', 'Failed to add comment to GitHub.');
            }

            $this->createLocalComment($this->selectedIssue, $response);

            $syncedIssue = $this->selectedIssue?->syncedIssues()?->first();
            if($this->selectedIssue->is_synced && $syncedIssue)
            {
                $this->handleSyncedComment($syncedIssue);
            }

            $this->dispatch('commentAddedSucessfully', 'Comment successfully added to GitHub.');

            $this->newComment = '';
            // $this->fetchIssues();
            $this->getIssues();

        } catch (Exception $e) {
            session()->flash('error', 'Could not add comment. Please try again later.');

            return response()->json([
                'error' => 'Could not add comment. Please try again later.',
            ], 500);
        }

    }

    public function handleSyncedComment(Issue $syncedIssue)
    {
            $this->refreshGitHubToken($this->toAccount);
            $response = $this->createGithubComment(
                account: $this->toAccount,
                repo: $syncedIssue->repository->name,
                issueNumber: $syncedIssue->issue_number
            );

            if (!$response) {
                return $this->addError('newComment', 'Failed to add comment to synced GitHub repository.');
            }
            $this->createLocalComment($syncedIssue, $response);
    }

    public function createLocalComment(Issue $issue, $response)
    {
        Comment::create([
            'content' => $this->newComment,
            'project_id' => $this->project->id,
            'repository_id' => $this->repos->id,
            'issue_number' => $issue->issue_number,
            'github_comment_id' => $response['id'],
            'issue_id' => $issue->id
        ]);
    }

    public function createGithubComment(Account $account, string $repo, $issueNumber)
    {
        $response = (new CreateGithubComment)->create(
            githubToken: $account->github_token,
            accountName: $account->name,
            repo: $repo,
            issueNumber: $issueNumber,
            comment: $this->newComment
        );
        return $response->status() === 201 ? $response->json() : null;
    }

    // private function addCommentToIssue($issueNumber, $comment)
    // {
    //     // $account = $this->project->accounts()->whereRelation('repositories', 'name', $this->selectedRepo)->first();
    //     $account = $this->getAccountQuery()->whereRelation('repositories', 'name', $this->selectedRepo)->first();

    //     $response = Http::withToken($account->github_token)
    //         ->withHeaders(['Accept' => 'application/vnd.github.v3+json'])
    //         ->post("https://api.github.com/repos/{$account->name}/{$this->selectedRepo}/issues/{$issueNumber}/comments", [
    //             'body' => $comment,
    //         ]);

    //     return $response;

    // }

    public function showComments(Issue $issue)
    {
        $this->comments = $issue->comments;
        $this->dispatch('open-modal');
    }

    public function showAddCommentModel(Issue $issue)
    {
        $this->selectedIssue = $issue;
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
            'title' => 'required|max:255'
        ]);

        try {
            return DB::transaction(function () {
                $accountFrom = $this->getAndRefreshAccount();

                $primaryGithubIssue = $this->createGithubIssue(
                    account: $accountFrom,
                    repo: $this->selectedRepo
                );

                if (!$primaryGithubIssue) {
                    throw new Exception('Failed to create primary GitHub issue');
                }

                $issue = $this->createLocalIssue($accountFrom, $this->repos, $primaryGithubIssue);

                $this->handleSyncedIssue($accountFrom, $issue);

                $this->handleSuccess();

                return $issue;
            });
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    private function getAndRefreshAccount(): Account
    {
        $account = $this->gitHubIntegrationAccount()?->accountFrom()?->first();
        $this->refreshGitHubToken($account);

        return $account;
    }

    private function createGithubIssue(Account $account, string $repo): ?array
    {
        $response = (new CreateGithubIssue)->create(
            githubToken: $account->github_token,
            accountName: $account->name,
            repo: $repo,
            title: $this->title,
            description: $this->description,
            labels: $this->getLabelsValue($this->labels)
        );

        return $response->status() === 201 ? $response->json() : null;
    }

    private function createLocalIssue(Account $account, Repository $repo, array $githubResponse): Issue
    {
        $issue = Issue::create([
            'title' => $this->title,
            'body' => $this->description,
            'project_id' => $this->project->id,
            'repository_id' => $repo->id,
            'github_issue_id' => $githubResponse['id'],
            'issue_number' => $githubResponse['number'],
            'labels' => json_encode($this->labels),
            'account_id' => $account->id,
            'creator' => config('github.bot_name'),
        ]);

        return $issue;
    }

    private function handleSyncedIssue(Account $primaryAccount, Issue $primaryIssue, $isManualSync = false): void
    {
        $syncedAccount = $primaryAccount->syncedAccounts()?->first();
        $integration = $this->project->gitHubIntegrations()->first();

        if (!$isManualSync && !$this->shouldCreateSyncedIssue($syncedAccount, $integration)) {
            return;
        }

        $this->refreshGitHubToken($syncedAccount);

        $syncedResponse = $this->createGithubIssue(
            account: $syncedAccount,
            repo: $integration->repoTo?->name
        );

        if ($syncedResponse) {
            $secondaryIssue = $this->createLocalIssue($syncedAccount, $integration->repoTo, $syncedResponse);
            $this->createSyncedIssueRecord($primaryIssue->id, $secondaryIssue->id);
            $primaryIssue->update(['is_synced' =>true]);
        }
    }

    private function shouldCreateSyncedIssue(?Account $syncedAccount, ?GitHubIntegration $integration): bool
    {
        if (!$syncedAccount || !$integration) {
            return false;
        }

        return !empty(array_intersect(
            json_decode($integration->labels),
            $this->labels
        ));
    }

    private function createSyncedIssueRecord(int $primaryIssueId, int $secondaryIssueId): void
    {
        IssueSynced::create([
            'from_issue' => $primaryIssueId,
            'to_issue' => $secondaryIssueId,
        ]);
    }

    private function handleSuccess(): void
    {
        $this->dispatch('issueCreatedSucessfully', 'Issue successfully created on GitHub.');
        $this->resetForm();
        // $this->fetchIssues();
        $this->getIssues();
    }

    private function handleError(Exception $e): void
    {
        logger()->error('GitHub Issue Creation Failed', [
            'error' => $e->getMessage(),
            'repo' => $this->selectedRepo,
            'project' => $this->project->id
        ]);

        session()->flash('error', 'Could not create issue. Please try again later.');
    }

    private function resetForm(): void
    {
        $this->reset(['title', 'description', 'labels']);
    }


    public function saveSelectedLabels($labels)
    {
        $this->labels = $labels;
    }

    public function getLabelsValue(array $labels): array
    {
        return array_map(function ($label) {
            return $this->allLabels[$label];
        }, $labels);
    }

    public function syncIssue(Issue $issue)
    {
        $this->title = $issue->title;
        $this->description = $issue->body;
        $this->labels = json_decode($issue->getRawOriginal('labels'));
        $this->handleSyncedIssue($this->getAndRefreshAccount(), $issue, true);
        $this->dispatch('issueCreatedSucessfully', 'Issue successfully synced on GitHub.');
        $this->getIssues();
    }
}
// Check if the response is successful
// Return the JSON response from the API
// Throw an exception if the API request fails
