<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\Firestore\UsersFirestoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Services\CloudinaryService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $newProfilePhotoUrl = null;

        // 1. Subir imagen a Cloudinary si hay nueva
        if ($request->hasFile('profile_photo')) {
            $cloudinary = app(CloudinaryService::class);
            $newProfilePhotoUrl = $cloudinary->uploadImage($request->file('profile_photo'), 'profile-photos');
        }

        // 2. Construir datos actualizados
        $data = [
            'name'  => $request->input('name'),
            'email' => $request->input('email'),
        ];
        if ($newProfilePhotoUrl !== null) {
            $data['profilePhotoUrl'] = $newProfilePhotoUrl;
        }

        // 3. Actualizar en Firestore
        try {
            $firestore = app(UsersFirestoreService::class);
            $docId = $firestore->getUserDocId($user->id);
            $firestore->updateUserFields($docId, $data);

            // 4. Refrescar usuario autenticado en sesión
            $freshData = $firestore->getUserByDocId($docId);
            if ($freshData) {
                $userClass = get_class($user);
                $freshUser = new $userClass($freshData);
                Auth::setUser($freshUser);
            }
        } catch (Throwable $exception) {
            return Redirect::back()
                ->withInput()
                ->withErrors(['profile' => 'No se pudo actualizar el perfil en Firestore. Intenta de nuevo.']);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->profile_photo_path && File::exists(public_path($user->profile_photo_path))) {
            File::delete(public_path($user->profile_photo_path));
        }

        try {
            app(UsersFirestoreService::class)->delete((int) $user->id);
        } catch (Throwable $exception) {
            Log::warning('No se pudo eliminar el usuario en Firestore tras eliminar cuenta local.', [
                'userId' => $user->id,
                'error' => $exception->getMessage(),
            ]);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
