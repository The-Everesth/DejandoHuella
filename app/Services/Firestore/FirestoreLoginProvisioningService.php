<?php

namespace App\Services\Firestore;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FirestoreLoginProvisioningService
{
    protected FirestoreRestClient $client;

    public function __construct(FirestoreRestClient $client)
    {
        $this->client = $client;
    }

    public function provisionMissingLocalUser(string $email, string $plainPassword): bool
    {
        $normalizedEmail = strtolower(trim($email));
        if ($normalizedEmail === '' || trim($plainPassword) === '') {
            return false;
        }

        $existingByEmail = User::withTrashed()
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->first();

        if ($existingByEmail) {
            if (method_exists($existingByEmail, 'trashed') && $existingByEmail->trashed()) {
                $existingByEmail->restore();
            }

            return false;
        }

        $firebaseUser = $this->findFirestoreUserByEmail($normalizedEmail);
        if (! $firebaseUser) {
            return false;
        }

        $status = strtolower(trim((string) ($firebaseUser['status'] ?? 'active')));
        if ($status !== '' && $status !== 'active') {
            Log::warning('Firestore login provisioning skipped due to non-active Firebase user status', [
                'email' => $normalizedEmail,
                'status' => $status,
            ]);

            return false;
        }

        $targetUserId = $this->resolveTargetLaravelUserId($firebaseUser);
        if (! $targetUserId) {
            Log::warning('Firestore login provisioning skipped because no valid laravel user id was resolved', [
                'email' => $normalizedEmail,
                'docId' => $firebaseUser['_docId'] ?? $firebaseUser['id'] ?? null,
            ]);

            return false;
        }

        $existingById = User::withTrashed()->find($targetUserId);
        if ($existingById && strtolower((string) $existingById->email) !== $normalizedEmail) {
            Log::warning('Firestore login provisioning skipped because target laravel id is already used by another email', [
                'email' => $normalizedEmail,
                'targetUserId' => $targetUserId,
                'existingEmail' => $existingById->email,
            ]);

            return false;
        }

        if ($existingById) {
            if (method_exists($existingById, 'trashed') && $existingById->trashed()) {
                $existingById->restore();
            }

            $existingById->password = $plainPassword;
            $existingById->email_verified_at = $existingById->email_verified_at ?: now();
            $existingById->remember_token = $existingById->remember_token ?: Str::random(10);
            $existingById->role_request_status = $existingById->role_request_status ?: 'approved';
            $existingById->save();

            return true;
        }

        $user = new User();
        $user->id = $targetUserId;
        $user->name = $this->resolveDisplayName($firebaseUser, $normalizedEmail);
        $user->email = $normalizedEmail;
        $user->password = $plainPassword;
        $user->email_verified_at = now();
        $user->remember_token = Str::random(10);
        $user->requested_role = null;
        $user->role_request_status = 'approved';
        $user->role_requested_at = now();
        $user->role_reviewed_at = now();
        $user->save();

        Log::info('Provisioned missing local user from Firestore users collection during login', [
            'email' => $normalizedEmail,
            'userId' => $targetUserId,
        ]);

        return true;
    }

    protected function findFirestoreUserByEmail(string $normalizedEmail): ?array
    {
        $docs = $this->client->listDocs('users');
        foreach ($docs as $doc) {
            $docEmail = strtolower(trim((string) ($doc['email'] ?? '')));
            if ($docEmail === $normalizedEmail) {
                return $doc;
            }
        }

        return null;
    }

    protected function resolveTargetLaravelUserId(array $firebaseUser): ?int
    {
        $laravelUserId = isset($firebaseUser['laravelUserId']) ? (int) $firebaseUser['laravelUserId'] : 0;
        if ($laravelUserId > 0) {
            return $laravelUserId;
        }

        $docId = (string) ($firebaseUser['_docId'] ?? $firebaseUser['id'] ?? '');
        if (preg_match('/^u_(\d+)$/', $docId, $matches) === 1) {
            $parsed = (int) ($matches[1] ?? 0);
            return $parsed > 0 ? $parsed : null;
        }

        return null;
    }

    protected function resolveDisplayName(array $firebaseUser, string $normalizedEmail): string
    {
        $name = trim((string) ($firebaseUser['name'] ?? ''));
        if ($name !== '') {
            return $name;
        }

        $beforeAt = strstr($normalizedEmail, '@', true);
        return $beforeAt !== false && $beforeAt !== '' ? $beforeAt : 'Usuario';
    }
}