<?php

namespace App\Http\Controllers;

use App\Models\CandidatureStage;
use App\Models\OffreStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidatureStageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $entreprise = Auth::user();
    // Récupère toutes les offres de cette entreprise
    $offres = OffreStage::where('entreprise_id', $entreprise->id)->with('candidatureStage.stagiaire')->get();

    $candidatures = [];

    foreach ($offres as $offre) {
        foreach ($offre->candidatureStage as $candidature) {
            $candidatures[] = [
                'offre_titre' => $offre->domaine,
                'candidat_nom' => $candidature->stagiaire->user->nom ?? 'nom inconnu',
                'candidat_prenom' => $candidature->stagiaire->user->prenom ?? 'prenom inconnu',
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
        if (!$user->stagiaire || !$user->stagiaire->specialite ) {
            //Log::info('profilcompletion');
            return response()->json([
                'message' => 'Veuillez compléter votre profil entreprise avant de créer une offre.'
            ], 422);
           
        
        }

        $request->validate([
            'cip' => 'required|string',
            'cv' => 'required|string',
            'diplome' => 'required|string',
            'lettre_motivation' => 'required|string',
        ]);
        //logger($request->all()) ;
    
        $stagiaire = Auth::user(); // Si le stagiaire est connecté
    
        // Vérifie si déjà candidaté
        $existe = CandidatureStage::where('stagiaire_id', $stagiaire->id)
            ->where('offre_stage_id', $offreId)
            ->exists();
    
        if ($existe) {
            return response()->json(['message' => 'Vous avez déjà postulé à cette offre.'], 409);
        }
    
        $candidature = CandidatureStage::create([
            'stagiaire_id' => $stagiaire->id,
            'offre_stage_id' => $offreId,
            'cip' => $request->cip,
            'cv' => $request->cv,
            'diplome' => $request->diplome,
            'lettre_motivation' => $request->lettre_motivation,
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
    public function show(CandidatureStage $candidatureStage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CandidatureStage $candidatureStage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CandidatureStage $candidatureStage)
    {
        //
    }
}
