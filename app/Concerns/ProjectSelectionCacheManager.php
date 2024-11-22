<?php

namespace App\Concerns;

trait ProjectSelectionCacheManager
{
    public function cacheProjectId($id): void
    {
        $cacheKey = md5(auth()->id());
        cache()->put($cacheKey, $id, 60 * 10);
    }

    public function getCachedProjectId()
    {
        $cacheKey = md5(auth()->id());

        return cache()->get($cacheKey);
    }

    public function clearCachedProjectId(): void
    {
        $cacheKey = md5(auth()->id());
        cache()->forget($cacheKey);
    }
}
