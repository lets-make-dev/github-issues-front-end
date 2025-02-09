<?php

namespace App\Actions\GitHub;

use App\Concerns\GithubApiManager;
use Illuminate\Support\Facades\Http;

class CreateGithubIssue
{
    public function create($githubToken, $accountName, $repo, $title, $description, $labels)
    {
        $response = Http::withToken($githubToken)
        ->withHeaders(['Accept' => 'application/vnd.github.v3+json'])
        ->post("https://api.github.com/repos/{$accountName}/{$repo}/issues", [
            'title' => $title,
            'body' => $description,
            'labels' => $labels
        ]);

        return $response;
    }
}
