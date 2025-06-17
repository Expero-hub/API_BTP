<?php

namespace App\Http\Controllers;

use App\Models\CandidatureSousTraitance;
use App\Models\SousTraitance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidatureSousTraitanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $entreprise = Auth::user();
    // Récupère toutes les   taches de cette entreprise
    $taches = SousTraitance::where('entreprise_maitre_id', $entreprise->id)->with('candidatureSousTraitance.entreprise')->get();

    $candidatures = [];

    foreach ($taches as $tache) {
        foreach ($tache->candidatureSousTraitance as $candidature) {
            $candidatures[] = [
                'tache' => $tache->tache,
                'candidat_nom' => $candidature->entreprise->user->nom ?? 'nom inconnu',
                'candidat_prenom' => $candidature->entreprise->user->email ?? 'prenom inconnu',
                'date_candidature' => $candidature->created_at,
                // Autres infos utiles ici...
            ];
        }
    }

    return response()->json([
        'entreprise_id' => $entreprise->entreprise->nom_entreprise,
        'candidatures' => $candidatures,
    ]);
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request, $sousTraitance)
    {
        try{
         // Vérifier si l'utilisateur est authentifié
         $user = Auth::user();
            
            

         if (!$user) {
             return response()->json(['message' => 'Utilisateur non authentifié'], 401);
         }
       

        // Vérifie si le profil entreprise est complété
        if (!$user->entreprise || !$user->entreprise->IFU ) {
            //Log::info('profilcompletion');
            return response()->json([
                'message' => 'Veuillez compléter votre profil entreprise avant de créer une offre.'
            ], 422);
           
        
        }

         $request->validate([
           
            'motivation' => 'nullable|file|mimes:jpg,jpeg,png,pdf,jfif|max:2048',
            
        ]);
 
    
        // Vérifie si déjà candidaté
        $existe = CandidatureSousTraitance::where('entreprise_id', $user->id)
            ->where('sous_traitance_id', $sousTraitance)
            ->exists();
    
        if ($existe) {
            return response()->json(['message' => 'Vous avez déjà postulé à cette tâche.'], 409);
        }


         // Traiter l'image
            if ($request->hasFile('motivation')) {
                // Récupération du fichier
                $file = $request->file('motivation');
                // Générer un nom unique pour l'image
                $imageName =uniqid() . '.' .  $file->getClientOriginalName();
                // Stockage dans storage/app/public/documents
                $path = $file->storeAs('documents', $imageName, 'public');
            } else {
                return response()->json([
                    'message' => 'fichier introuvable'
                ], 422);
            }
    
        
        $candidature = CandidatureSousTraitance::create([
            'entreprise_id' => $user->id,
            'sous_traitance_id' => $sousTraitance,
            'motivation' => 'storage/' . $path,
        
        ]);
    
        return response()->json([
            'message' => 'Candidature envoyée avec succès.',
            'candidature' => $candidature,
        ], 201);
    }catch (\Exception $e) {
        return response()->json([
            "message" => "Une erreur est survenue lors de votre postulation",
            "erreur" => $e->getMessage()
        ], 500);
    }
    }

    /**
     * Display the specified resource.
     */
    public function show(CandidatureSousTraitance $candidatureSousTraitance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CandidatureSousTraitance $candidatureSousTraitance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CandidatureSousTraitance $candidatureSousTraitance)
    {
        //
    }

    //Accepter candidature tache ou refuser

    public function accepter($id)
{
    
    $candidature = CandidatureSousTraitance::find($id);

    if (!$candidature) {
        return response()->json(['message' => 'Candidature introuvable'], 404);
    }

    //s'assurer que l'acteur est bel et bien l'auteur de l'offre Expé 

    if ($candidature->sousTraitance->entreprise_maitre_id !== Auth::id()) {
    return response()->json(['message' => 'Action non autorisée. Vous n\'êtes pas l\'auteur de cette offre '], 403);
}



    $candidature->statut = 'acceptee';
    $candidature->save();

    return response()->json(['message' => 'Candidature acceptée avec succès', 'candidature' => $candidature]);
}

public function rejeter($id)
{
    $candidature = CandidatureSousTraitance::find($id);

    if (!$candidature) {
        return response()->json(['message' => 'Candidature introuvable'], 404);
    }

    //s'assurer que l'acteur est bel et bien l'auteur de l'offre Expé 

    if ($candidature->sousTraitance->entreprise_maitre_id !== Auth::id()) {
    return response()->json(['message' => 'Action non autorisée. Vous n\'êtes pas l\'auteur de cette offre '], 403);
    }



    $candidature->statut = 'refusee';
    $candidature->save();

        return response()->json(['message' => 'Candidature rejetée avec succès', 'candidature' => $candidature]);
    }
}
