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
    //Les candidatures liées à une offre donnée

    

    public function voirCandidaturesParOffre($id)
    {
        $entreprise = Auth::user();

        // Vérifie que l'offre appartient bien à l'entreprise connectée
        $offre = OffreStage::where('id', $id)
            ->where('entreprise_id', $entreprise->id)
            ->with('candidatureStage.stagiaire.user')
            ->first();

        if (!$offre) {
            return response()->json([
                'message' => "Offre introuvable ou non autorisée"
            ], 404);
        }

        $candidatures = [];

        foreach ($offre->candidatureStage as $candidature) {
            $candidatures[] = [
                'candidat_nom' => $candidature->stagiaire->user->nom ?? 'nom inconnu',
                'candidat_prenom' => $candidature->stagiaire->user->prenom ?? 'prenom inconnu',
                'date_candidature' => $candidature->created_at,
                // Autres champs si nécessaire
            ];
        }

        return response()->json([
            'offre' => $offre->domaine,
            'candidatures' => $candidatures,
        ]);
    }




    //les candidatures d'un stagiaires donné 
    public function mesCandidatures()
    {
        $user = Auth::user();
        $candidatures = OffreStage::all();

        return response()->json([
            'message' => 'Vos candidatures  Stage',
            'candidatures' => $candidatures
        ], 200);
        
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
            'cip' => 'required|file|mimes:jpg,jpeg,png,pdf,jfif|max:2048',
            'cv' => 'required|file|mimes:jpg,jpeg,png,pdf,jfif|max:2048',
            'diplome' => 'nullable|file|mimes:jpg,jpeg,png,pdf,jfif|max:2048',
            'lettre_motivation' => 'nullable|file|mimes:jpg,jpeg,png,pdf,jfif|max:2048',
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
    
        $cipPath = $this->uploadDocument($request->file('cip'), 'cip');
        $cvPath = $this->uploadDocument($request->file('cv'), 'cv');
        $diplomePath = $this->uploadDocument($request->file('diplome'), 'diplome');
        $lettrePath = $this->uploadDocument($request->file('lettre_motivation'), 'lettre_motivation');

        $candidature = CandidatureStage::create([
            'stagiaire_id' => $stagiaire->id,
            'offre_stage_id' => $offreId,
            'cip' => $cipPath,
            'cv' => $cvPath,
            'diplome' => $diplomePath,
            'lettre_motivation' => $lettrePath,
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

    private function uploadDocument($file, $prefix = '')
{
    if (!$file) {
        return null;
    }

    $extension = $file->getClientOriginalExtension();
    $filename = $prefix . '_' . uniqid() . '.' . $extension;
    $path = $file->storeAs('public/documents', $filename);

    return $path;
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

    //Accepter ou rejeter une candidature
    public function accepter($id)
{
    
    $candidature = CandidatureStage::find($id);

    if (!$candidature) {
        return response()->json(['message' => 'Candidature introuvable'], 404);
    }

    //s'assurer que l'acteur est bel et bien l'auteur de l'offre Expé 

    if ($candidature->offre->entreprise_id !== Auth::id()) {
    return response()->json(['message' => 'Action non autorisée. Vous n\'êtes pas l\'auteur de cette offre '], 403);
}



    $candidature->statut = 'acceptee';
    $candidature->save();

    return response()->json(['message' => 'Candidature acceptée avec succès', 'candidature' => $candidature]);
}

public function rejeter($id)
{
    $candidature = CandidatureStage::find($id);

    if (!$candidature) {
        return response()->json(['message' => 'Candidature introuvable'], 404);
    }

    //s'assurer que l'acteur est bel et bien l'auteur de l'offre Expé 

    if ($candidature->offre->entreprise_id !== Auth::id()) {
    return response()->json(['message' => 'Action non autorisée. Vous n\'êtes pas l\'auteur de cette offre '], 403);
}



    $candidature->statut = 'rejettee';
    $candidature->save();

    return response()->json(['message' => 'Candidature rejetée avec succès', 'candidature' => $candidature]);
}
}
