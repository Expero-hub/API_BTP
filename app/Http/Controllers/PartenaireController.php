<?php

namespace App\Http\Controllers;

use App\Models\Partenaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartenaireController extends Controller
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
                'secteur' => 'required|string|max:255',
                'description' => 'required|string|max:255',
                'adresse' => 'required|string|max:255',
                'contact' => 'required|string|max:255',
                'logo' => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
                'IFU' => 'required|string|max:255',
                'RCCM' => 'nullable|string|max:255',
                
            ]);
             Log:info($request->all());


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
            $entreprise = Partenaire::create([
                'id' => $user->id,
                'secteur' => $request->secteur,
                'description' => $request->description,
                'adresse' => $request->adresse,
                'contact' => $request->contact,
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
    public function show(Partenaire $partenaire)
    {
        return response()->json([
            'message' => 'Profil_Partenaire',
            'partenaire' => $partenaire
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Partenaire $partenaire)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Partenaire $partenaire)
    {
        //
    }
}
