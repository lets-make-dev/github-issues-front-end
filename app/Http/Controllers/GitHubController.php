<?php

namespace App\Http\Controllers;

use App\Concerns\GithubApiManager;
use App\Concerns\ProjectSelectionCacheManager;
use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GitHubController extends Controller
{
    use GithubApiManager, ProjectSelectionCacheManager;

    public function handleGitHubCallback(Request $request)
    {
        $code = $request->input('code');
        $installationId = $request->input('installation_id');

        $installationToken = $this->getInstallationToken($installationId);

        $repositories = $this->getRepositories($installationToken);

        if (app()->environment('local')) {
            $user = User::find(1);
        } else {
            $user = auth()->user();
        }

        $projectId = $this->getCachedProjectId();
        $this->clearCachedProjectId();

        $user->update(['github_token' => $installationToken]);
        // add repositories to the database
        foreach ($repositories as $repository) {
            // determine the account name from $repository['full_name']
            $accountName = explode('/', $repository['full_name'])[0];

            // find or create the account (for the user)
            /** @var Account $account */
            $account = $user->accounts()->firstOrCreate([
                'github_token' => $installationToken,
                'project_id' => $projectId,
                'name' => $accountName,
            ]);

            // find or create the repo
            $account->repositories()->firstOrCreate([
                'name' => $repository['name'],
                'user_id' => $user->id,
            ]);
        }

        // You can now do something with the repositories, like storing them in the database
        // or returning them in the response
        return to_route('projects.settings', ['project' => $projectId]);
        //        return response()->json($repositories);
    }

    private function listInstallations()
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->generateJWT(),
            'Accept' => 'application/vnd.github.v3+json',
        ])->get('https://api.github.com/app/installations');

        return $response->json();
    }

    private function getAccessToken($code)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])
            ->post('https://github.com/login/oauth/access_token', [
                'client_id' => config('services.github.client_id'),
                'client_secret' => config('services.github.client_secret'),
                'code' => $code,
            ]);

        $data = $response->json();

        return $data['access_token'];
    }
}
