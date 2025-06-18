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

//Les candidatures liées à une offre donnée

    public function voirCandidaturesParOffre($id)
    {
        $entreprise = Auth::user();

        // Vérifie que l'offre appartient bien à l'entreprise connectée
        $offre = OffreEmploi::where('id', $id)
            ->where('entreprise_id', $entreprise->id)
            ->with('candidatureEmplois.ouvrier.user')
            ->first();

        if (!$offre) {
            return response()->json([
                'message' => "Aucune offre "
            ], 404);
        }

        $candidatures = [];

        foreach ($offre->candidatureEmplois as $candidature) {
            $candidatures[] = [
                'candidat_nom' => $candidature->ouvrier->user->nom ?? 'nom inconnu',
                'candidat_prenom' => $candidature->ouvrier->user->prenom ?? 'prenom inconnu',
                'date_candidature' => $candidature->created_at,
                // Autres champs si nécessaire
            ];
        }

        return response()->json([
            'offre' => $offre->projet,
            'candidatures' => $candidatures,
        ]);
    }

//les candidatures d'un ouvrier donné

    public function mesCandidatures()
    {
        $user = Auth::user();
        $candidatures = CandidatureEmploi::where('ouvrier_id', $user->id)->get();

        return response()->json([
            'message' => 'Vos candidatures',
            'candidatures' => $candidatures
        ], 200);
        
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $offreId)
{
    try {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        if (!$user->ouvrier || !$user->ouvrier->metier || !$user->ouvrier->cv) {
            return response()->json([
                'message' => 'Veuillez compléter votre profil ouvrier avant de créer une offre.'
            ], 422);
        }

        $request->validate([
            'cip' => 'required|file|mimes:jpg,jpeg,png,pdf,jfif|max:2048',
            'cv' => 'required|file|mimes:jpg,jpeg,png,pdf,jfif|max:2048',
            'diplome' => 'nullable|file|mimes:jpg,jpeg,png,pdf,jfif|max:2048',
        ]);

        $ouvrier = $user;

        $existe = CandidatureEmploi::where('ouvrier_id', $ouvrier->id)
            ->where('offre_emploi_id', $offreId)
            ->exists();

        if ($existe) {
            return response()->json(['message' => 'Vous avez déjà postulé à cette offre.'], 409);
        }

        $cipPath = $this->uploadDocument($request->file('cip'), 'cip');
        $cvPath = $this->uploadDocument($request->file('cv'), 'cv');
        $diplomePath = $this->uploadDocument($request->file('diplome'), 'diplome');

        $candidature = CandidatureEmploi::create([
            'ouvrier_id' => $ouvrier->id,
            'offre_emploi_id' => $offreId,
            'cip' => $cipPath,
            'cv' => $cvPath,
            'diplome' => $diplomePath,
        ]);
        //logger("Candidature ID : " . $candidature->id);


        return response()->json([
            'message' => 'Candidature envoyée avec succès.',
            'candidature' => $candidature,
        ], 201);

    } catch (\Exception $e) {
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
    
    //Accepter ou rejeter une candidature
    public function accepter($id)
{
    
    $candidature = CandidatureEmploi::find($id);

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
    $candidature = CandidatureEmploi::find($id);

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
