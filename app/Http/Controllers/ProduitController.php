<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProduitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $produits = Produit::all();

        return response()->json(['produits' => $produits]);

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
          
             // Vérifie si le profil partenaire est complété
            if (!$user->partenaire || !$user->partenaire->IFU || !$user->partenaire->RCCM) {
                //Log::info('profilcompletion');
                return response()->json([
                    'message' => 'Veuillez compléter votre profil entreprise avant de créer une offre.'
                ], 422);
               
            
            }

            

            // Validation des données
            $request->validate([
                'nom' => 'required|string|max:255',
                'prix' => 'required|string|max:255',
                'description' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:255',
                'photo' =>'required|file|mimes:jpg,jpeg,png|max:10240',
                
            ]);
            // Log:info($request->all());
            
            // Traiter l'image
            if ($request->hasFile('photo')) {
                // Récupération du fichier
                $file = $request->file('photo');
                // Générer un nom unique pour l'image
                $imageName = uniqid().'_'.$file->getClientOriginalName();
                // Stockage dans storage/app/public/profil
                $path = $file->storeAs('produits', $imageName, 'public');
            } else {
                return response()->json([
                    'message' => 'fichier introuvable'
                ], 422);
            }

            

        
            $produit = Produit::create([
                'partenaire_id' => $user->id,
                'nom' => $request->nom,
                'prix' => $request->prix,
                'description' => $request->description,
                'type' => $request->type,
                'photo' => 'storage/' . $path,
            ]);
            //logger($offreEmploi->all()) ;
            
            

            // Retourner une réponse JSON
            return response()->json([
                'message' => 'Le produit est  créé avec succès',
                'produit' => $produit
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Une erreur est survenue lors de la création du produit",
                "erreur" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Produit $produit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produit $produit)
    {
        try {
            // Vérifier si l'utilisateur est authentifié
            $user = Auth::user();
            

            if (!$user) {
                return response()->json(['message' => 'Utilisateur non authentifié'], 401);
            }

                // Vérifier que l'utilisateur est bien un partenaire propriétaire du produit
            if (!$user->partenaire || $user->partenaire->id !== $produit->partenaire_id) {
                return response()->json(['message' => "Vous n'êtes pas autorisé à modifier ce produit."], 403);
            }

            // Validation des données
            $request->validate([
                'nom' => 'nullable|string|max:255',
                'prix' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:255',
                'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
                
            ]);
            //logger($request->all()) 

        
            $produit->nom = $request->filled('nom') ? $request->nom : $produit->nom;
            $produit->prix = $request->filled('prix') ? $request->prix : $produit->prix;
            $produit->description = $request->filled('description') ? $request->description : $produit->description;


            // Traiter l'image
            if ($request->hasFile('photo')) {
                
                if ($produit->photo && file_exists(public_path($produit->photo))) {
                unlink(public_path($produit->photo));
                }

                // Récupération du fichier
                $file = $request->file('photo');
                // Générer un nom unique pour l'image
                $imageName = uniqid().'_'.$file->getClientOriginalName();
                // Stockage dans storage/app/public/profil
                $path = $file->storeAs('produits', $imageName, 'public');
                $produit->photo = 'storage/' . $path;
            }
            $produit->save();

            // Retourner une réponse JSON
            return response()->json([
                'message' => 'Produit  n°'. $produit->id .' de '.$produit->lieu .' mis à jour avec succès',
                'offre' => $produit
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
    public function destroy(Produit $produit)
    {
        try {
        $user = Auth::user();

        
     

        if ($produit->partenaire_id  !== $user->id) {
            logger('Accès refusé', ['produit' => $produit]);
            abort(403, 'Action non autorisée.');
        }

        $produit->delete(); 

        return response()->json(['message' => 'produit supprimé avec succès']);
    } catch (\Exception $e) {
        logger('Erreur suppression', ['erreur' => $e->getMessage()]);
        return response()->json([
            'message' => 'Suppression échouée',
            'erreur' => $e->getMessage()
        ], 404);
    }
    }
}
