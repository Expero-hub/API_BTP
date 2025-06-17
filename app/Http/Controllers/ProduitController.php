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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produit $produit)
    {
        //
    }
}
