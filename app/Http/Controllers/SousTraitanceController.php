<?php

namespace App\Http\Controllers;

use App\Models\SousTraitance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SousTraitanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $sousTraitance = SousTraitance::where('date_fin', '>=', Carbon::today())->get();

        return response()->json([
            'message' => 'Projet disponibles',
            'Projets' => $sousTraitance
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $request->validate([
            'projet_id' => 'required|exists:projets,id',
            'tache' => 'required|string',
            'date_debut' => 'nullable|date',
            'date_fin' => 'required|date',
            'mode' => 'required|in:appel,assignation',
            'entreprise_sous_traitante_id' => 'nullable|exists:entreprises,id'
        ]);
        
        $user = Auth::user();
        $entrepriseMaitre = $user->entreprise;

        if (!$entrepriseMaitre) {
            return response()->json(['message' => 'Seules les entreprises peuvent créer une sous-traitance.'], 403);
        }

        if ($request->mode === 'assignation' && !$request->entreprise_sous_traitante_id) {
            return response()->json(['message' => 'Entreprise sous-traitante requise pour une assignation.'], 422);
        }
        

        $sousTraitance = SousTraitance::create([
            'projet_id' => $request->projet_id,
            'entreprise_maitre_id' => $entrepriseMaitre->id,
            'entreprise_sous_traitante_id' => $request->entreprise_sous_traitante_id,
            'tache' => $request->tache,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'mode' => $request->mode,
            'statut' => $request->mode === 'assignation' ? 'en_attente' : null
        ]);

        return response()->json(['message' => 'Sous-traitance créée.', 'data' => $sousTraitance]);
        }

        //Décision de l'entreprise soustraitante
    public function repondreAssignation(Request $request, $sousTraitanceId)
{
    logger('le store');
    logger([ $request->all()]);

    $request->validate([
        'statut' => 'required|in:confirmee,refusee',
    ]);

    $user = Auth::user()->load('entreprise');

    if (!$user->entreprise) {
        return response()->json(['message' => 'Seule une entreprise peut répondre à une assignation.'], 403);
    }


    $sousTraitance = SousTraitance::where('id', $sousTraitanceId)
        ->where('entreprise_sous_traitante_id', $user->entreprise->id)
        ->firstOrFail();

    $sousTraitance->update(['statut' => $request->statut]);

    return response()->json(['message' => 'Réponse enregistrée.']);
}


    /**
     * Display the specified resource.
     */
    public function show(SousTraitance $sousTraitance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SousTraitance $sousTraitance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SousTraitance $sousTraitance)
    {
        //
    }
}
