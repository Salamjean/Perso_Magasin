<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::where('role', '!=', 'admin');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('firstname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $logs = ActivityLog::latest()->limit(10)->get();

        $stats = [
            'total' => User::where('role', '!=', 'admin')->count(),
            'active' => User::where('role', '!=', 'admin')->where('status', 'active')->count(),
            'inactive' => User::where('role', '!=', 'admin')->where('status', 'inactive')->count(),
        ];

        return view('admin.users.index', compact('users', 'logs', 'stats'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'firstname' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:50', 'unique:users,phone'],
            'address' => ['nullable', 'string'],
            'role' => ['required', 'in:magasinier,caissier,livreur'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé par un autre utilisateur.',
            'email.unique' => 'Cet email est déjà attribué.',
            'role.required' => 'Le rôle est obligatoire.',
            'role.in' => 'Le rôle sélectionné est invalide.',
            'password.required' => 'Le mot de passe initial est obligatoire.',
            'password.min' => 'Le mot de passe doit comporter au moins 6 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('users', 'public');
        }

        $user = User::create([
            'name' => $validated['name'],
            'firstname' => $validated['firstname'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            'role' => $validated['role'],
            'status' => $validated['status'],
            'password' => Hash::make($validated['password']),
            'photo' => $photoPath,
        ]);

        ActivityLog::log(
            'utilisateur_cree',
            "Création de l'employé {$user->full_name} ({$user->role}) par l'administrateur",
            null,
            $user->only(['name', 'firstname', 'email', 'phone', 'role', 'status'])
        );

        return redirect()->route('admin.users.index')->with('success', "L'utilisateur {$user->full_name} a été créé avec succès.");
    }

    public function show(User $user): View
    {
        $userLogs = ActivityLog::where('user_id', $user->id)->latest()->paginate(15);

        return view('admin.users.show', compact('user', 'userLogs'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'firstname' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['required', 'string', 'max:50', 'unique:users,phone,'.$user->id],
            'address' => ['nullable', 'string'],
            'role' => ['required', 'in:magasinier,caissier,livreur'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.unique' => 'Ce numéro de téléphone est déjà attribué.',
            'email.unique' => 'Cet email est déjà attribué.',
            'role.required' => 'Le rôle est obligatoire.',
            'role.in' => 'Le rôle sélectionné est invalide.',
            'password.min' => 'Le mot de passe doit comporter au moins 6 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $oldValues = $user->only(['name', 'firstname', 'email', 'role', 'status', 'phone', 'address']);

        if ($request->hasFile('photo')) {
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }
            $user->photo = $request->file('photo')->store('users', 'public');
        }

        $user->name = $validated['name'];
        $user->firstname = $validated['firstname'] ?? null;
        $user->email = $validated['email'] ?? null;
        $user->phone = $validated['phone'];
        $user->address = $validated['address'] ?? null;
        $user->role = $validated['role'];
        $user->status = $validated['status'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        ActivityLog::log(
            'utilisateur_modifie',
            "Modification de l'utilisateur {$user->full_name} ({$user->role})",
            $oldValues,
            $user->only(['name', 'firstname', 'email', 'role', 'status', 'phone', 'address'])
        );

        return redirect()->route('admin.users.index')->with('success', "L'utilisateur {$user->full_name} a été mis à jour.");
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas modifier votre propre statut.');
        }

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        $actionText = $user->status === 'active' ? 'débloqué' : 'bloqué';

        ActivityLog::log(
            'utilisateur_statut_change',
            "Le compte de l'utilisateur {$user->full_name} a été {$actionText}."
        );

        return back()->with('success', "Le compte de {$user->full_name} a été {$actionText} avec succès.");
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'new_password' => ['required', 'string', 'min:6'],
        ], [
            'new_password.required' => 'Le nouveau mot de passe est obligatoire.',
            'new_password.min' => 'Le mot de passe doit comporter au moins 6 caractères.',
        ]);

        $user->password = Hash::make($request->new_password);
        $user->save();

        ActivityLog::log(
            'reinitialisation_mot_de_passe',
            "Réinitialisation du mot de passe de {$user->full_name} par l'administrateur"
        );

        return back()->with('success', "Le mot de passe de {$user->full_name} a été réinitialisé.");
    }

    public function destroy(User $user): RedirectResponse
    {
        return back()->with('error', 'La suppression des comptes utilisateurs est désactivée. Vous pouvez uniquement bloquer leur accès.');
    }
}
