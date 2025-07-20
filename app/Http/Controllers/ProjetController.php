<?php

namespace App\Http\Controllers;

use App\Models\CandidatureProjet;
use App\Models\EntrepriseProjet;
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
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        // 1. Projets créés par l'entreprise
        $projetsCrees = Projet::where('entreprise_id', $user->id)
            ->where('date_fin', '>=', Carbon::today())
            ->get();

        // 2. Projets assignés à l'entreprise
        $idsProjetsAssignes = EntrepriseProjet::where('entreprise_id', $user->id)
            ->where('statut', 'accepte')
            ->pluck('projet_id');

        $projetsAssignes = Projet::whereIn('id', $idsProjetsAssignes)
            ->where('date_fin', '>=', Carbon::today())
            ->get();

        // 3. Projets client auxquels l'entreprise a postulé et accepté
        $idsProjetsPostulesEtAcceptes = CandidatureProjet::where('entreprise_id', $user->id)
            ->where('statut', 'accepte')
            ->pluck('projet_id');

        $projetsClientAcceptes = Projet::whereIn('id', $idsProjetsPostulesEtAcceptes)
            ->where('date_fin', '>=', Carbon::today())
            ->get();

        // Fusionner les 3 collections
        $tousLesProjets = $projetsCrees
            ->merge($projetsAssignes)
            ->merge($projetsClientAcceptes)
            ->unique('id') // éviter les doublons
            ->values();    // réindexer proprement

        return response()->json([
            'message' => 'Projets disponibles',
            'projets' => $tousLesProjets
        ], 200);
    }

    //projets créés  par un client 
    public function projetsClient(Request $request)
{
    $user = $request->user();
    $projets = Projet::where('client_id', $user->id)->with('entrepriseProjet.entreprise')->get();

    return response()->json([
        'message' => 'Projets du client',
        'data' => $projets
    ]);
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

    $data = [
        'titre' => $request->titre,
        'description' => $request->description,
        'lieu' => $request->lieu,
        'date_debut' => $request->date_debut,
        'date_fin' => $request->date_fin,
    ];

    if ($user->hasRole('entreprise')) {
        // Vérification que le profil est bien complété
        if (!$user->entreprise || !$user->entreprise->nom_entreprise || !$user->entreprise->IFU) {
            return response()->json(['message' => 'Veuillez compléter votre profil entreprise.'], 403);
        }

        $data['entreprise_id'] = $user->entreprise->id;
    } elseif ($user->hasRole('client')) {
        $data['client_id'] = $user->id;
    } else {
        return response()->json(['message' => 'Utilisateur non autorisé à créer un projet.'], 403);
    }

    $projet = Projet::create($data);

    return response()->json(['message' => 'Projet créé avec succès.', 'projet' => $projet], 201);
}


    /**
     * Display the specified resource.
     */
    public function show($id)
{
    $projet = Projet::with(['entreprise', 'taches'])
                ->findOrFail($id);
    return response()->json([
        'message' => 'Détails du projet',
        'data' => $projet
    ]);
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
