<?php

namespace App\Concerns;

use Exception;
use Firebase\JWT\JWT;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

trait GithubApiManager
{
    private function getInstallationToken($installationId)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->generateJWT(),
            'Accept' => 'application/vnd.github.v3+json',
        ])->post("https://api.github.com/app/installations/{$installationId}/access_tokens");

        $data = $response->json();

        return $data['token'];
    }

    private function generateJWT(): string
    {
        $privateKeyPath = app_path(config('services.github.pem_key_path'));

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
    public function refreshGitHubToken($account)
    {
        $installationId = $this->getInstallationId($account);
        $newToken = $this->getInstallationToken($installationId);

        $account->update(['github_token' => $newToken]);

        return $newToken;
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    private function getInstallationId($account)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->generateJWT(),
            'Accept' => 'application/vnd.github.v3+json',
        ])->get('https://api.github.com/app/installations');

        $installations = $response->json();

        foreach ($installations as $installation) {
            if ($installation['account']['login'] === $account->name) {
                return $installation['id'];
            }
        }

        throw new Exception("No installation found for account: {$account->name}");
    }

    private function getRepositories($installationToken)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$installationToken,
            'Accept' => 'application/vnd.github.v3+json',
        ])->get('https://api.github.com/installation/repositories?per_page=100');

        return $response->json()['repositories'];
    }
}
