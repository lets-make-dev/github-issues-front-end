<?php

namespace App\Http\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GitHubController extends Controller
{
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

        $user->update(['github_token' => $installationToken]);

        // add repositories to the database
        foreach ($repositories as $repository) {
            // determine the account name from $repository['full_name']
            $accountName = explode('/', $repository['full_name'])[0];

            // find or create the account (for the user)
            /** @var \App\Models\Account $account */
            $account = $user->accounts()->firstOrCreate([
                'github_token' => $installationToken,
                'project_id' => 1,
                'name' => $accountName
            ]);

            // find or create the repo
            $account->repositories()->firstOrCreate([
                'name' => $repository['name'],
                'user_id' => $user->id
            ]);
        }
        // You can now do something with the repositories, like storing them in the database
        // or returning them in the response
        return response()->json($repositories);
    }

    private function listInstallations()
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->generateJWT(),
            'Accept' => 'application/vnd.github.v3+json',
        ])->get('https://api.github.com/app/installations');

        return $response->json();
    }

    private function getAccessToken($code)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json'
        ])
            ->post('https://github.com/login/oauth/access_token', [
                'client_id' => config('services.github.client_id'),
                'client_secret' => config('services.github.client_secret'),
                'code' => $code,
            ]);

        ray([
            'https://github.com/login/oauth/access_token',
            'client_id' => config('services.github.client_id'),
            'client_secret' => config('services.github.client_secret'),
            'code' => $code,
        ]);

        $data = $response->json();
        return $data['access_token'];
    }

    private function getInstallationToken($installationId)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->generateJWT(),
            'Accept' => 'application/vnd.github.v3+json',
        ])->post("https://api.github.com/app/installations/{$installationId}/access_tokens");

        $data = $response->json();
        ray($response->json());
        return $data['token'];
    }

    private function getRepositories($installationToken)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $installationToken,
            'Accept' => 'application/vnd.github.v3+json',
        ])->get('https://api.github.com/installation/repositories');

        return $response->json()['repositories'];
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
}
