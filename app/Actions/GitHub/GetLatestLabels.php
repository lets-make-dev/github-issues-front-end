<?php

namespace App\Actions\GitHub;

use Illuminate\Support\Facades\Http;

class GetLatestLabels
{
    public function getLabel($installationToken, $owner, $repoName)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$installationToken,
                'Accept' => 'application/vnd.github.v3+json',
            ])
            ->get("https://api.github.com/repos/{$owner}/{$repoName}/labels")
            ->throw();

            return $response->json();

        } catch (\Exception $e) {
            return [];
        }
    }
}



// $calculateReadTime = new CalculateReadTime(200);
// $readTime = $calculateReadTime->execute($blog->content);
