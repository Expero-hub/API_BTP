<?php

namespace App\Http\Controllers;

use App\Models\Projet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $projet = Projet::where('date_fin', '>=', Carbon::today())->get();

        return response()->json([
            'message' => 'Projet disponibles',
            'projets' => $projet
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $request->validate([
        'titre' => 'required|string|max:255',
        'description' => 'nullable|string',
        'lieu' => 'required|string|max:255',
        'date_debut' => 'nullable|date',
        'date_fin' => 'required|date',
    ]);

    $user = Auth::user();

    // Si l'utilisateur est une entreprise, on vérifie que son profil est complété
    if ($user->hasRole('entreprise')) {
        if (!$user->entreprise || !$user->entreprise->nom_entreprise || !$user->entreprise->IFU) {
            return response()->json(['message' => 'Veuillez compléter votre profil entreprise.'], 403);
        }
    }

    // Création du projet, on récupère les IDs si disponibles
    $projet = Projet::create([
        'titre' => $request->titre,
        'description' => $request->description,
        'lieu' => $request->lieu,
        'date_debut' => $request->date_debut,
        'date_fin' => $request->date_fin,
        'entreprise_id' => $user->entreprise?->id,
        'client_id' => $user->id,
    ]);

    return response()->json(['message' => 'Projet créé avec succès.', 'projet' => $projet], 201);
}


    /**
     * Display the specified resource.
     */
    public function show(Projet $projet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Projet $projet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Projet $projet)
    {
        //
    }

    public function projetOuverts(){
         $projetsOuverts = Projet::whereNotNull('client_id') // créés par un client
                             ->whereDoesntHave('entrepriseProjet') // pas encore assignés
                             ->get();

    return response()->json([
        'projets_ouverts' => $projetsOuverts
    ]);


    }

    
}
