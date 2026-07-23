<?php

namespace App\Services;

use Carbon\CarbonInterface;

final class TotpService
{
    private const SECRET_BYTES = 20;

    private const TIME_STEP = 30;

    private const DIGITS = 6;

    public function generateSecret(): string
    {
        return $this->base32Encode(random_bytes(self::SECRET_BYTES));
    }

    public function provisioningUri(string $secret, string $account, string $issuer): string
    {
        return 'otpauth://totp/'.rawurlencode($issuer.':'.$account)
            .'?secret='.rawurlencode($secret)
            .'&issuer='.rawurlencode($issuer)
            .'&algorithm=SHA1&digits='.self::DIGITS.'&period='.self::TIME_STEP;
    }

    public function verify(string $secret, string $code, ?CarbonInterface $at = null): bool
    {
        return $this->matchingCounter($secret, $code, $at) !== null;
    }

    /**
     * Return the accepted time-step counter, or null for an invalid code.
     * The counter is persisted by the caller to prevent code replay.
     */
    public function matchingCounter(string $secret, string $code, ?CarbonInterface $at = null): ?int
    {
        if (! preg_match('/^\d{6}$/', $code)) {
            return null;
        }

        $counter = intdiv(($at ?? now())->getTimestamp(), self::TIME_STEP);

        foreach ([-1, 0, 1] as $offset) {
            if (hash_equals($this->code($secret, $counter + $offset), $code)) {
                return $counter + $offset;
            }
        }

        return null;
    }

    private function code(string $secret, int $counter): string
    {
        $binaryCounter = pack('J', $counter);
        $hash = hash_hmac('sha1', $binaryCounter, $this->base32Decode($secret), true);
        $offset = ord($hash[19]) & 0x0F;
        $value = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($value % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    private function base32Encode(string $value): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';

        foreach (unpack('C*', $value) as $byte) {
            $bits .= str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);
        }

        $encoded = '';
        foreach (str_split(str_pad($bits, (int) ceil(strlen($bits) / 5) * 5, '0'), 5) as $chunk) {
            $encoded .= $alphabet[bindec($chunk)];
        }

        return $encoded;
    }

    private function base32Decode(string $value): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';

        foreach (str_split(strtoupper(rtrim($value, '='))) as $character) {
            $position = strpos($alphabet, $character);
            if ($position === false) {
                return '';
            }
            $bits .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        $decoded = '';
        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) < 8) {
                break;
            }
            $decoded .= chr(bindec($chunk));
        }

        return $decoded;
    }
}
