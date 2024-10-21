<?php

namespace App\Livewire;

use Firebase\JWT\JWT;
use Illuminate\Http\Client\RequestException;
use Livewire\Component;
use App\Models\Project;
use App\Models\Account;
use Illuminate\Support\Facades\Http;

class Dashboard extends Component
{
    public $project;
    public $selectedRepo = '';
    public $repos = [];
    public $issues = [];
    public $showClosed = false;
    public $groupBy = 'status';
    public $search = '';

    protected $queryString = ['selectedRepo', 'showClosed', 'groupBy', 'search'];

    public function mount(Project $project)
    {
        ray()->clearAll();
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

    public function updatedShowClosed()
    {
        $this->fetchIssues();
    }

    public function updatedSearch()
    {
        $this->fetchIssues();
    }

    public function fetchIssues(): void
    {
        if (empty($this->selectedRepo)) {
            return;
        }

        $account = $this->project->accounts()->whereHas('repositories', function ($query) {
            $query->where('name', $this->selectedRepo);
        })->first();

        if (!$account) {
            $this->issues = [];
            return;
        }

        try {
            $response = Http::retry(2, 0, function ($exception, $request) use ($account) {
                if ($exception instanceof RequestException && $exception->response->status() === 401) {
                    ray('Refreshing token');
                    $this->refreshGitHubToken($account);
                    return true;
                }
                return false;
            })->withToken($account->github_token)
                ->get("https://api.github.com/repos/{$account->name}/{$this->selectedRepo}/issues", [
                    'state'    => $this->showClosed ? 'all' : 'open',
                    'per_page' => 100,
                ])
                ->throw();
            ray($response->json());
            $this->issues = collect($response->json())
                ->filter(function ($issue) {
                    return empty($this->search) || stripos($issue['title'], $this->search) !== false;
                })
                ->map(function ($issue) {
                    return [
                        'title'           => $issue['title'],
                        'description'     => $issue['body'],
                        'status'          => $issue['state'],
                        'creator'         => $issue['user']['login'],
                        'comments'        => $issue['comments'],
                        'labels'          => collect($issue['labels'])->pluck('name')->toArray(),
                        'milestone'       => $issue['milestone']['title'] ?? null,
                        'estimated_hours' => 0, // You might want to add custom logic for this
                        'priorities'      => [], // You might want to add custom logic for this
                    ];
                })
                ->toArray();
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
        })->values()->toArray();
    }

    public function refreshGitHubToken($account)
    {
        $installationId = $this->getInstallationId($account);
        $newToken = $this->getInstallationToken($installationId);

        $account->update(['github_token' => $newToken]);

        return $newToken;
    }

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

    private function generateJWT()
    {
        $privateKeyPath = storage_path('app/hubbub-the-missing-front-end.2024-09-11.private-key.pem');
        $privateKey = file_get_contents($privateKeyPath);

        $payload = [
            // issued at time
            'iat' => time(),
            // JWT expiration time (10 minutes maximum)
            'exp' => time() + (10 * 60),
            // GitHub App's identifier
            'iss' => config('services.github.app_id')
        ];

        return JWT::encode($payload, $privateKey, 'RS256');
    }

    private function getInstallationToken($installationId)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->generateJWT(),
            'Accept'        => 'application/vnd.github.v3+json',
        ])->post("https://api.github.com/app/installations/{$installationId}/access_tokens");

        $data = $response->json();
        ray($response->json());
        return $data['token'];
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
