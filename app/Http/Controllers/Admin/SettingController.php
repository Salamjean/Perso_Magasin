<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = [
            'store_name' => Setting::get('store_name', 'Supermarché GestMagasin'),
            'store_phone' => Setting::get('store_phone', '+225 27 22 00 00 00'),
            'store_email' => Setting::get('store_email', 'contact@gestmagasin.com'),
            'store_address' => Setting::get('store_address', 'Abidjan, Côte d\'Ivoire'),
            'store_logo' => Setting::get('store_logo'),
            'currency' => Setting::get('currency', 'FCFA'),
            'default_vat_rate' => Setting::get('default_vat_rate', 18),
            'receipt_footer' => Setting::get('receipt_footer', 'Merci pour votre visite !'),
            'low_stock_global_threshold' => Setting::get('low_stock_global_threshold', 10),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'store_phone' => ['nullable', 'string', 'max:50'],
            'store_email' => ['nullable', 'email', 'max:255'],
            'store_address' => ['nullable', 'string'],
            'currency' => ['required', 'string', 'max:20'],
            'default_vat_rate' => ['nullable', 'numeric', 'min:0'],
            'receipt_footer' => ['nullable', 'string'],
            'low_stock_global_threshold' => ['required', 'integer', 'min:1'],
            'store_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:3072'],
        ], [
            'store_name.required' => 'Le nom de l\'établissement est obligatoire.',
            'store_logo.image' => 'Le fichier sélectionné doit être une image valide.',
            'store_logo.max' => 'L\'image du logo ne doit pas dépasser 3 Mo.',
        ]);

        // Gestion de la suppression explicite du logo
        if ($request->boolean('remove_logo')) {
            $oldLogo = Setting::get('store_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            Setting::set('store_logo', null, 'string');
        }

        // Gestion de l'upload du nouveau logo
        if ($request->hasFile('store_logo')) {
            $oldLogo = Setting::get('store_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            $path = $request->file('store_logo')->store('logos', 'public');
            Setting::set('store_logo', $path, 'string');
        }

        // Sauvegarde des autres paramètres textuels et numériques
        $keysToSave = [
            'store_name',
            'store_phone',
            'store_email',
            'store_address',
            'currency',
            'default_vat_rate',
            'receipt_footer',
            'low_stock_global_threshold',
        ];

        foreach ($keysToSave as $key) {
            if (isset($validated[$key])) {
                $val = $validated[$key];
                $type = is_numeric($val) ? (strpos((string) $val, '.') !== false ? 'float' : 'integer') : 'string';
                Setting::set($key, $val, $type);
            }
        }

        ActivityLog::log('parametres_modifies', 'Mise à jour des paramètres et de la configuration du magasin');

        return back()->with('success', 'Paramètres et logo du magasin mis à jour avec succès.');
    }
}
