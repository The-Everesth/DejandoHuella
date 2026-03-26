<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Firestore\UsersFirestoreService;
use App\Models\FirestoreAuthenticatableUser;

class RefreshFirestoreUser
{
    protected $firestore;

    public function __construct(UsersFirestoreService $firestore)
    {
        $this->firestore = $firestore;
    }

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            // El id real de Firestore está en $user->id
            $freshData = $this->firestore->getUserByDocId((string)$user->id);
            if ($freshData) {
                $freshUser = new FirestoreAuthenticatableUser($freshData);
                Auth::setUser($freshUser);
            }
        }
        return $next($request);
    }
}
