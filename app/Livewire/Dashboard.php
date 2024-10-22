<?php

namespace App\Livewire;

use App\Enums\GithubIssueState;
use App\Models\Project;
use Firebase\JWT\JWT;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class Dashboard extends Component
{
    public Project $project;

    public string $selectedRepo = '';

    public array $repos = [];

    public array|Collection $issues = [];

    public array|Collection $issuesFiltered = [];
    public array|Collection $issuesCopy = [];

    public bool $showClosed = false;

    public string $groupBy = '';

    public string $search = '';

    protected array $queryString = ['selectedRepo', 'showClosed', 'groupBy', 'search'];

    public function mount(Project $project): void
    {
        $this->project = $project;
        $this->repos = $this->project->repositories()->pluck('name')->toArray();
        if (!empty($this->repos)) {
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

        if (!$account) {
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
                    'state'    => $this->showClosed ? GithubIssueState::Closed->value : 'all',
                    'per_page' => 100,
                ])
                ->throw();
            $this->issues = collect($response->json())
                ->filter(function ($issue) {
                    return empty($this->search) || stripos($issue['title'], $this->search) !== false;
                })
                ->map(function ($issue) {
                    return [
                        'title'           => $issue['title'],
                        'description'     => str()->markdown($issue['body']),
                        'status'          => $issue['state'],
                        'creator'         => $issue['user']['login'],
                        'comments'        => $issue['comments'],
                        'labels'          => collect($issue['labels'])->pluck('name', 'color')->toArray(),
                        'milestone'       => $issue['milestone']['title'] ?? null,
                        'estimated_hours' => 0, // You might want to add custom logic for this
                        'priorities'      => [], // You might want to add custom logic for this
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
                'name'   => ucfirst($key),
                'issues' => $group->toArray(),
            ];
        })->sortByDesc('name')->values()->toArray();
    }

    /**
     * @throws ConnectionException
     */
    public function refreshGitHubToken($account)
    {
        $installationId = $this->getInstallationId($account);
        $newToken = $this->getInstallationToken($installationId);

        $account->update(['github_token' => $newToken]);

        return $newToken;
    }

    /**
     * @throws ConnectionException
     * @throws \Exception
     */
    private function getInstallationId($account)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->generateJWT(),
            'Accept'        => 'application/vnd.github.v3+json',
        ])->get('https://api.github.com/app/installations');

        $installations = $response->json();

        foreach ($installations as $installation) {
            if ($installation['account']['login'] === $account->name) {
                return $installation['id'];
            }
        }

        throw new \Exception("No installation found for account: {$account->name}");
    }

    private function generateJWT(): string
    {
        $privateKeyPath = storage_path('app/hubbub-the-missing-front-end.2024-09-11.private-key.pem');

        $privateKey = file_get_contents($privateKeyPath);

        $payload = [
            // issued at time
            'iat' => time(),
            // JWT expiration time (10 minutes maximum)
            'exp' => time() + (10 * 60),
            // GitHub App's identifier
            'iss' => config('services.github.app_id'),
        ];

        return JWT::encode($payload, $privateKey, 'RS256');
    }

    /**
     * @throws ConnectionException
     */
    private function getInstallationToken($installationId)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->generateJWT(),
            'Accept'        => 'application/vnd.github.v3+json',
        ])->post("https://api.github.com/app/installations/{$installationId}/access_tokens");

        $data = $response->json();

        return $data['token'];
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
