<?php

namespace App\Http\Controllers;

use App\Models\OffreEmploi;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; 

class OffreEmploiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $offreEmploi = OffreEmploi::where('date_limite', '>=', Carbon::today())->get();

        return response()->json([
            'message' => 'Offres disponibles',
            'offre' => $offreEmploi
        ], 200);
        
    }

    //les offres d'une entreprise
    public function mesOffreEmploi()
    {
         $entreprise = Auth::user();

        // Vérifie que l'offre appartient bien à l'entreprise connectée
        $offreEmploi = OffreEmploi::where('entreprise_id', $entreprise->id)->get();

        if ($offreEmploi->isEmpty()) {
            return response()->json([
                'message' => "Offre introuvable ou non autorisée"
            ], 404);
        }

        return response()->json([
            'message' => 'Offres disponibles',
            'offre' => $offreEmploi
        ], 200);
        
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
         
            // Vérifier si l'utilisateur est authentifié
            $user = Auth::user();
            
            

            if (!$user) {
                return response()->json(['message' => 'Utilisateur non authentifié'], 401);
            }
          
             // Vérifie si le profil entreprise est complété
            if (!$user->entreprise || !$user->entreprise->nom_entreprise || !$user->entreprise->IFU) {
                //Log::info('profilcompletion');
                return response()->json([
                    'message' => 'Veuillez compléter votre profil entreprise avant de créer une offre.'
                ], 422);
               
            
            }

            

            // Validation des données
            $request->validate([
                'projet' => 'required|string|max:255',
                'description' => 'required|string|max:255',
                'lieu' => 'required|string|max:255',
                'date_limite' => 'required|date|max:255',
                
            ]);
            // logger($request->all()) ;

            

        
            $offreEmploi = OffreEmploi::create([
                'entreprise_id' => $user->id,
                'projet' => $request->projet,
                'description' => $request->description,
                'lieu' => $request->lieu,
                'date_limite' => $request->date_limite,
            ]);
            //logger($offreEmploi->all()) ;
            
            

            // Retourner une réponse JSON
            return response()->json([
                'message' => 'Offre créée avec succès',
                'offre' => $offreEmploi
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Une erreur est survenue lors de la création d'offre",
                "erreur" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(OffreEmploi $offreEmploi)
    {
        return response()->json([
            'message' => 'Détails de l\'offre',
            'offre' => $offreEmploi
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OffreEmploi $offreEmploi)
    {
        try {
            // Vérifier si l'utilisateur est authentifié
            $user = Auth::user();
            

            if (!$user) {
                return response()->json(['message' => 'Utilisateur non authentifié'], 401);
            }

                // Vérifier que l'utilisateur est bien une entreprise propriétaire de l'offre
            if (!$user->entreprise || $user->entreprise->id !== $offreEmploi->entreprise_id) {
                return response()->json(['message' => "Vous n'êtes pas autorisé à modifier cette offre."], 403);
            }

            // Validation des données
            $request->validate([
                'projet' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:255',
                'lieu' => 'nullable|string|max:255',
                'date_limite' => 'nullable|date|max:255',
                
            ]);
            //logger($request->all()) ;

            

        
            $offreEmploi->update($request->all());
            
            logger($offreEmploi->all()) ;
            // Retourner une réponse JSON
            return response()->json([
                'message' => 'Projet  n°'. $offreEmploi->id .' de '.$offreEmploi->lieu .' mis à jour avec succès',
                'offre' => $offreEmploi
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Une erreur est survenue lors de la mise à jour d'offre",
                "erreur" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OffreEmploi $offreEmploi)
{
    try {
        $user = Auth::user();

        logger('Tentative de suppression', [
            'entreprise_id' => $offreEmploi->entreprise_id,
            'user_id' => $user->id,
        ]);

        if ($offreEmploi->entreprise_id !== $user->id) {
            logger('Accès refusé', ['offre' => $offreEmploi]);
            abort(403, 'Action non autorisée.');
        }

        $offreEmploi->delete();

        return response()->json(['message' => 'Offre supprimée avec succès']);
    } catch (\Exception $e) {
        logger('Erreur suppression', ['erreur' => $e->getMessage()]);
        return response()->json([
            'message' => 'Suppression échouée',
            'erreur' => $e->getMessage()
        ], 404);
    }
}

}
