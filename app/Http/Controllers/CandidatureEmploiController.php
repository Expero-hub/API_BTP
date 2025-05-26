<?php

namespace App\Http\Controllers;

use App\Models\CandidatureEmploi;
use App\Models\OffreEmploi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CandidatureEmploiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $entreprise = Auth::user();
    // Récupère toutes les offres de cette entreprise
    $offres = OffreEmploi::where('entreprise_id', $entreprise->id)->with('candidatureEmplois.ouvrier')->get();

    $candidatures = [];

    foreach ($offres as $offre) {
        foreach ($offre->candidatureEmplois as $candidature) {
            $candidatures[] = [
                'offre_titre' => $offre->projet,
                'candidat_nom' => $candidature->ouvrier->user->nom ?? 'nom inconnu',
                'candidat_prenom' => $candidature->ouvrier->user->prenom ?? 'prenom inconnu',
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
    public function store(Request $request, $offreId)
{
    try{
         // Vérifier si l'utilisateur est authentifié
         $user = Auth::user();
            
            

         if (!$user) {
             return response()->json(['message' => 'Utilisateur non authentifié'], 401);
         }
       

        // Vérifie si le profil entreprise est complété
        if (!$user->ouvrier || !$user->ouvrier->metier || !$user->ouvrier->cv) {
            //Log::info('profilcompletion');
            return response()->json([
                'message' => 'Veuillez compléter votre profil entreprise avant de créer une offre.'
            ], 422);
           
        
        }

        $request->validate([
            'cip' => 'required|string',
            'cv' => 'required|string',
        ]);
        //logger($request->all()) ;
    
        $ouvrier = Auth::user(); // Si l'ouvrier est connecté
    
        // Vérifie si déjà candidaté
        $existe = CandidatureEmploi::where('ouvrier_id', $ouvrier->id)
            ->where('offre_emploi_id', $offreId)
            ->exists();
    
        if ($existe) {
            return response()->json(['message' => 'Vous avez déjà postulé à cette offre.'], 409);
        }
    
        $candidature = CandidatureEmploi::create([
            'ouvrier_id' => $ouvrier->id,
            'offre_emploi_id' => $offreId,
            'cip' => $request->cip,
            'cv' => $request->cv,
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
    public function show(CandidatureEmploi $candidatureEmploi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CandidatureEmploi $candidatureEmploi)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CandidatureEmploi $candidatureEmploi)
    {
        //
    }
}
