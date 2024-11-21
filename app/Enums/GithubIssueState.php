<?php

namespace App\Enums;

enum GithubIssueState: string
{
    case Open = 'open';
    case Closed = 'closed';

    // Optional: Add any helper methods if needed
    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Closed => 'Closed',
        };
    }
}
