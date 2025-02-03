<?php

namespace App\Actions\GitHub;

use App\Concerns\GithubApiManager;
use Illuminate\Support\Facades\Http;

class CreateGithubComment
{
    public function create($githubToken, $accountName, $repo, $issueNumber, $comment)
    {
        $response = Http::withToken($githubToken)
            ->withHeaders(['Accept' => 'application/vnd.github.v3+json'])
            ->post("https://api.github.com/repos/{$accountName}/{$repo}/issues/{$issueNumber}/comments", [
                'body' => $comment,
            ]);

        return $response;
    }
}
