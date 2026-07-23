<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * @return array<int, string|null>
     */
    public function hosts(): array
    {
        return collect(config('security.trusted_hosts', []))
            ->map(fn (string $host): string => '^'.preg_quote($host, '/').'$')
            ->values()
            ->all();
    }
}
