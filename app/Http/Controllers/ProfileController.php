<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\Firestore\UsersFirestoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
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
        $newProfilePhotoPath = null;

        if ($request->hasFile('profile_photo')) {
            $directory = public_path('uploads/profile-photos');

            if (! File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            $extension = $request->file('profile_photo')->getClientOriginalExtension();
            $filename = 'user-'.$user->id.'-'.Str::lower((string) Str::ulid()).'.'.$extension;

            $request->file('profile_photo')->move($directory, $filename);

            $newProfilePhotoPath = 'uploads/profile-photos/'.$filename;
        }

        $oldProfilePhotoPath = $user->profile_photo_path;

        try {
            $user->fill($request->safe()->only(['name', 'email']));

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            if ($newProfilePhotoPath !== null) {
                $user->profile_photo_path = $newProfilePhotoPath;
            }

            $user->save();
        } catch (Throwable $exception) {
            if ($newProfilePhotoPath !== null && File::exists(public_path($newProfilePhotoPath))) {
                File::delete(public_path($newProfilePhotoPath));
            }

            return Redirect::back()
                ->withInput()
                ->withErrors(['profile' => 'No se pudo actualizar el perfil. Intenta de nuevo.']);
        }

        if ($newProfilePhotoPath !== null && $oldProfilePhotoPath && File::exists(public_path($oldProfilePhotoPath))) {
            File::delete(public_path($oldProfilePhotoPath));
        }

        try {
            app(UsersFirestoreService::class)->syncFromUser($user->fresh());
        } catch (Throwable $exception) {
            Log::warning('No se pudo sincronizar el perfil con Firestore tras actualizar usuario.', [
                'userId' => $user->id,
                'error' => $exception->getMessage(),
            ]);
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
