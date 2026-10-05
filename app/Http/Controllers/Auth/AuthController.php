<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\SyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Veuillez saisir votre identifiant (Email ou Téléphone).',
            'password.required' => 'Veuillez saisir votre mot de passe.',
        ]);

        $input = trim($request->input('login'));
        $password = $request->input('password');

        // Nettoyage pour numéro de téléphone
        $cleanPhone = preg_replace('/[^\d+]/', '', $input);

        // Si aucun utilisateur n'existe encore dans la base locale (premier lancement avec base vierge), synchronisation immédiate depuis MySQL distant
        if (User::count() === 0) {
            try {
                app(SyncService::class)->syncAll();
            } catch (\Throwable $e) {
                // Mode hors-ligne si non joignable
            }
        }

        // Recherche : si l'identifiant ressemble à un email ou s'il s'agit d'un numéro de téléphone
        $user = User::where(function ($query) use ($input, $cleanPhone) {
            $query->where('email', $input)
                ->orWhere('phone', $input)
                ->orWhereRaw("REPLACE(REPLACE(phone, ' ', ''), '-', '') = ?", [$cleanPhone]);
        })->first();

        // Vérification des identifiants
        if (! $user || ! Hash::check($password, $user->password)) {
            return back()->withErrors([
                'login' => 'Identifiant ou mot de passe incorrect.',
            ])->withInput($request->only('login'));
        }

        // Vérification de la cohérence de connexion : Admin par email, Employés par téléphone
        if ($user->role === 'admin' && ! filter_var($input, FILTER_VALIDATE_EMAIL)) {
            return back()->withErrors([
                'login' => 'L\'administrateur doit se connecter avec son adresse email.',
            ])->withInput($request->only('login'));
        }

        if (in_array($user->role, ['magasinier', 'caissier', 'livreur']) && filter_var($input, FILTER_VALIDATE_EMAIL)) {
            return back()->withErrors([
                'login' => 'Les employés doivent se connecter avec leur numéro de téléphone.',
            ])->withInput($request->only('login'));
        }

        // Vérification du statut actif
        if (! $user->isActive()) {
            return back()->withErrors([
                'login' => 'Votre compte a été désactivé par l\'administrateur.',
            ])->withInput($request->only('login'));
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        ActivityLog::log('connexion', "Connexion de {$user->full_name} ({$user->role})");

        return $this->redirectBasedOnRole($user);
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            ActivityLog::log('deconnexion', "Déconnexion de {$user->full_name}");
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Vous avez été déconnecté avec succès.');
    }

    public function profile(): View
    {
        return view('auth.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'firstname' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'current_password' => ['nullable', 'required_with:new_password'],
            'new_password' => ['nullable', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'email.unique' => 'Cet email est déjà utilisé par un autre compte.',
            'current_password.required_with' => 'Veuillez saisir votre mot de passe actuel.',
            'new_password.min' => 'Le nouveau mot de passe doit contenir au moins 6 caractères.',
            'new_password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        // Vérification du mot de passe actuel si changement demandé
        if (! empty($validated['new_password'])) {
            if (! Hash::check($request->input('current_password'), $user->password)) {
                return back()->withErrors([
                    'current_password' => 'Votre mot de passe actuel est incorrect.',
                ])->withInput($request->except(['current_password', 'new_password', 'new_password_confirmation']));
            }
            $user->password = Hash::make($validated['new_password']);
        }

        $user->name = $validated['name'];
        $user->firstname = $validated['firstname'] ?? null;
        if ($user->role === 'admin' && ! empty($validated['email'])) {
            $user->email = $validated['email'];
        } elseif (! empty($validated['email'])) {
            $user->email = $validated['email'];
        }
        $user->phone = $validated['phone'] ?? null;
        $user->address = $validated['address'] ?? null;
        $user->save();

        ActivityLog::log('profil_modifie', "Mise à jour du profil et/ou mot de passe pour {$user->full_name}");

        return back()->with('success', 'Vos informations et mot de passe ont été mis à jour avec succès.');
    }

    private function redirectBasedOnRole($user): RedirectResponse
    {
        return match ($user->role) {
            'admin' => redirect()->intended(route('admin.dashboard')),
            'magasinier' => redirect()->intended(route('magasinier.dashboard')),
            'caissier' => redirect()->intended(route('caissier.dashboard')),
            'livreur' => redirect()->intended(route('livreur.dashboard')),
            default => redirect()->route('login'),
        };
    }
}
