<?php

namespace App\Support;

use App\Models\MobileAuthToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MobileApiAuth
{
    public static function issueToken(User $user, string $name = 'mobile-app', ?int $ttlDays = 30): string
    {
        $plainTextToken = Str::random(64);

        MobileAuthToken::create([
            'user_id' => $user->id,
            'name' => $name,
            'token' => hash('sha256', $plainTextToken),
            'expires_at' => $ttlDays ? now()->addDays($ttlDays) : null,
        ]);

        return $plainTextToken;
    }

    public static function userFromRequest(Request $request): ?User
    {
        $plainTextToken = $request->bearerToken();

        if (! $plainTextToken) {
            return null;
        }

        $token = MobileAuthToken::query()
            ->where('token', hash('sha256', $plainTextToken))
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $token) {
            return null;
        }

        $token->forceFill(['last_used_at' => now()])->save();
        $request->attributes->set('mobile_token_id', $token->id);

        $user = $token->user;

        if (! $user) {
            return null;
        }

        if (! is_null($user->getAttribute('is_active')) && ! $user->getAttribute('is_active')) {
            return null;
        }

        if ($user->getAttribute('role') !== 'intern') {
            return null;
        }

        return $user;
    }

    public static function revokeCurrentToken(Request $request): void
    {
        $tokenId = $request->attributes->get('mobile_token_id');

        if (! $tokenId) {
            return;
        }

        MobileAuthToken::query()->whereKey($tokenId)->delete();
    }
}
