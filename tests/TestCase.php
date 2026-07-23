<?php

namespace Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function actingAs(Authenticatable $user, $guard = null)
    {
        // Feature tests frequently switch identities without a browser session.
        // Clear the previous password fingerprint before Laravel's auth.session
        // middleware observes the new test identity.
        if ($this->app->bound('session.store')) {
            $this->app['session.store']->forget('password_hash_'.($guard ?: $this->app['auth']->getDefaultDriver()));
        }

        return parent::actingAs($user, $guard);
    }
}
