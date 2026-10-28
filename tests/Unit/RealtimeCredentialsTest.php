<?php

namespace Tests\Unit;

use App\Support\RealtimeCredentials;
use PHPUnit\Framework\TestCase;

/**
 * 2026-10-28: `.env.example` ships REVERB_APP_ID / KEY / SECRET empty, and an empty key silently turned real-time off
 * ("Live updates off" on every address). A blank value is now filled in from the server's own APP_KEY, so a fresh install
 * needs no set-up; a value written in .env still wins.
 */
class RealtimeCredentialsTest extends TestCase
{
    private const APP_KEY = 'base64:Zm9yLXRlc3RzLW9ubHktbm90LWEtcmVhbC1rZXk=';

    public function test_a_value_written_in_env_is_used_as_it_is(): void
    {
        $this->assertSame('42', RealtimeCredentials::appId(' 42 '));
        $this->assertSame('my-own-key', RealtimeCredentials::key('my-own-key', self::APP_KEY));
        $this->assertSame('my-own-secret', RealtimeCredentials::secret('my-own-secret', self::APP_KEY));
    }

    public function test_a_blank_value_is_derived_from_the_app_key_and_is_never_empty(): void
    {
        foreach ([null, '', '   '] as $blank) {
            $this->assertSame('clinic', RealtimeCredentials::appId($blank));
            $this->assertMatchesRegularExpression('/^[0-9a-f]{20}$/', RealtimeCredentials::key($blank, self::APP_KEY));
            $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', RealtimeCredentials::secret($blank, self::APP_KEY));
        }
    }

    public function test_the_derived_values_are_stable_and_differ_per_server_and_per_purpose(): void
    {
        $key = RealtimeCredentials::key(null, self::APP_KEY);
        $secret = RealtimeCredentials::secret(null, self::APP_KEY);

        $this->assertSame($key, RealtimeCredentials::key('', self::APP_KEY), 'the server and the browser pages must get the same value every time');
        $this->assertNotSame($key, RealtimeCredentials::key(null, 'base64:b3RoZXItc2VydmVyLWFwcC1rZXk='), 'another server derives another key');
        $this->assertNotSame($secret, RealtimeCredentials::secret(null, 'base64:b3RoZXItc2VydmVyLWFwcC1rZXk='));
        $this->assertStringNotContainsString($key, $secret, 'the public key is not a part of the secret');
        $this->assertNotSame(substr($secret, 0, 20), $key, 'key and secret come from different labels');
    }

    public function test_without_any_app_key_nothing_is_invented_so_the_feature_stays_off_instead_of_using_a_guessable_secret(): void
    {
        $this->assertSame('', RealtimeCredentials::key(null, null));
        $this->assertSame('', RealtimeCredentials::secret('', ''));
        $this->assertSame('explicit', RealtimeCredentials::key('explicit', null), 'an explicit value never needs the app key');
    }
}
