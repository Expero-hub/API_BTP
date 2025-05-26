<?php

namespace App\Http\Controllers;

use App\Models\Stagiaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StagiaireController extends Controller
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
                'specialite' => 'required|string|max:255',
                'certifications' => 'nullable|string|max:255',
                'cv' => 'nullable|string|max:255',
                
            ]);
            

        

            // Completer profil Stagiaire
            
            $stagiaire = Stagiaire::create([
                'id' => $user->id,
                'specialite' => $request->specialite,
                'certifications' => $request->certifications,
                'cv' => $request->cv,
               
            ])->load('user');
            
            // Retourner une réponse JSON
            return response()->json([
                'message' => ' Profil complété avec succès',
                'stagiaire' => $stagiaire
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
    public function show(Stagiaire $stagiaire)
    {
        return response()->json([
            'message' => 'Profil_Stagiaire',
            'stagiaire' => $stagiaire
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Stagiaire $stagiaire)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Stagiaire $stagiaire)
    {
        //
    }
}
