<?php

namespace App\Http\Controllers;

use App\Models\EntrepriseProjet;
use App\Models\Projet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EntrepriseProjetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

     //Assignation de projet à une entreprise par un client 
    public function store(Request $request)
    {
         $request->validate([
        'projet_id' => 'required|exists:projets,id',
        'entreprise_id' => 'required|exists:entreprises,id',
    ]);

    $projet = Projet::find($request->projet_id);

    // Vérifie que l'utilisateur est bien le créateur du projet
    if ($projet->client_id !== Auth::id()) {
        return response()->json(['message' => 'Vous n\'êtes pas l\'auteur de ce projet.'], 403);
    }

    // Enregistrement dans une table de type "projet_assignations" ou dans le projet lui-même
    EntrepriseProjet::create([
        'projet_id' => $projet->id,
        'entreprise_id' => $request->entreprise_id,
    ]);

    return response()->json(['message' => 'L’entreprise a été sollicitée. En attente de sa réponse.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, EntrepriseProjet $entrepriseProjet)
    {
       
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EntrepriseProjet $entrepriseProjet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EntrepriseProjet $entrepriseProjet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EntrepriseProjet $entrepriseProjet)
    {
        //
    }
    public function repondreAssignation( $projetId)
{
    


    $user = Auth::user()->load('entreprise');

    if (!$user->entreprise) {
        return response()->json(['message' => 'Seule une entreprise peut répondre à une assignation.'], 403);
    }


    $prestation = EntrepriseProjet::where('id', $projetId)
        ->where('entreprise_id', $user->entreprise->id)
        ->firstOrFail();

   $prestation->statut = 'accepte';
    $prestation->save();
    return response()->json(['message' => 'Projet accepté.']);
}
}
