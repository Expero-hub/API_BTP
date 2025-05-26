<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EntrepriseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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

            // Validation des données
            $request->validate([
                'nom_entreprise' => 'required|string|max:255',
                'logo' => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
                'IFU' => 'required|string|max:255',
                'RCCM' => 'nullable|string|max:255',
                
            ]);

            // Traiter l'image
            if ($request->hasFile('logo')) {
                // Récupération du fichier
                $file = $request->file('logo');
                // Générer un nom unique pour l'image
                $imageName = $file->getClientOriginalName();
                // Stockage dans storage/app/public/profil
                $path = $file->storeAs('profil', $imageName, 'public');
            } else {
                return response()->json([
                    'message' => 'fichier introuvable'
                ], 422);
            }

            // Création de l'entreprise
            $entreprise = Entreprise::create([
                'id' => $user->id,
                'nom_entreprise' => $request->nom_entreprise,
                'logo' => 'storage/' . $path,
                'IFU' => $request->IFU,
                'RCCM' => $request->RCCM,
               
            ])->load('user');

            // Retourner une réponse JSON
            return response()->json([
                'message' => ' Profil complété avec succès',
                'document' => $entreprise
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Une erreur est survenue lors de l'enregistrement ",
                "erreur" => $e->getMessage()
            ], 500);
        }
    }

    
    /**
     * Display the specified resource.
     */
    public function show(Entreprise $entreprise)
    {
         return response()->json([
            'message' => 'Profil_Entreprise',
            'entreprise' => $entreprise
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Entreprise $entreprise)
    {
        try {
            // Vérifier si l'utilisateur est authentifié
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'Utilisateur non authentifié'], 401);
            }

            // Validation des données
            $request->validate([
                'nom_enteprise' => 'required|string|max:255',
                'logo' => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
                'IFU' => 'required|string|max:255',
                'RCCM' => 'nullable|string|max:RCCM',
            ]);

            // Mettre à jour
            

            if ($entreprise->logo && Storage::disk('public')->exists(str_replace('storage/', '', $entreprise->logo))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $entreprise->logo));
            }

            $entreprise->logo = $request->input('logo');

            if ($request->hasFile('logo')) {
                // Récupération du fichier
                $file = $request->file('logo');
                // Générer un nom unique pour l'image
                $imageName = $file->getClientOriginalName();
                // Stockage dans storage/app/public/documents
                $path = $file->storeAs('documents', $imageName, 'public');
                $entreprise->photo = 'storage/' . $path;
            }

            // Mise à jour du nom
            $entreprise->save();

            // Retourner une réponse JSON
            return response()->json([
                'message' => 'Document ' . $entreprise->nom . ' renomé',
                'entreprise' => $entreprise
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Une erreur est survenue lors de la modification ",
                "erreur" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Entreprise $entreprise)
    {
        //
    }
    public function offresParEntreprise($entrepriseId)
{
    $entreprise = Entreprise::with(['offresEmploi', 'offresStage'])->findOrFail($entrepriseId);

    return response()->json([
        'entreprise' => $entreprise->nom_entreprise,
        'offres_emploi' => $entreprise->offresEmploi,
        'offres_stage' => $entreprise->offresStage,
    ]);
}

}
