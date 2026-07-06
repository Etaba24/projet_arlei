<?php

namespace App\Http\Controllers;

use App\Models\Rapport;
use App\Models\User;
use App\Notifications\RapportSoumis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RapportController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $rapports = $user->hasAdminInterface()
            ? Rapport::with('user')->latest()->paginate(10)
            : Rapport::where('user_id', $user->id)->latest()->paginate(10);

        return view('rapports.index', compact('rapports'));
    }

    public function create()
    {
        return redirect()->route('rapports.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titre' => 'required|string|max:255',
            'contenu' => 'required|string|max:4000',
        ]);

        $rapport = Rapport::create([
            'user_id' => Auth::id(),
            'titre' => $data['titre'],
            'contenu' => $data['contenu'],
            'statut' => 'soumis',
        ]);

        $admins = User::query()
            ->where(function ($query) {
                $query->where('role', 'admin')
                    ->orWhereHas('customRole', function ($roleQuery) {
                        $roleQuery->where('slug', '!=', 'operateur-terrain');
                    });
            })
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(new RapportSoumis($rapport));
        }

        return redirect()->route('rapports.index')
            ->with('status', 'Rapport soumis avec succès à l’administration.');
    }

    public function show(Rapport $rapport)
    {
        if (!Auth::user()->hasAdminInterface() && $rapport->user_id !== Auth::id()) {
            abort(403);
        }

        if (Auth::user()->hasAdminInterface() && $rapport->statut !== 'lu') {
            $rapport->update(['statut' => 'lu']);
        }

        return view('rapports.show', compact('rapport'));
    }
}
