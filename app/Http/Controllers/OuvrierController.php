<?php

namespace App\Http\Controllers;

use App\Models\Ouvrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OuvrierController extends Controller
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
                'metier' => 'required|string|max:255',
                'certifications' => 'nullable|string|max:255',
                'cv' => 'nullable|string|max:255',
                'diplome' => 'nullable|string|max:255',
                
            ]);
            //logger($request->all()) ;

        

            // Création de l'entreprise
            logger('ici') ;
            $entreprise = Ouvrier::create([
                'id' => $user->id,
                'metier' => $request->metier,
                'diplome' => $request->diplome,
                'certifications' => $request->certifications,
                'cv' => $request->cv,
               
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
    public function show(Ouvrier $ouvrier)
    {
         return response()->json([
            'message' => 'Profil_Ouvrier',
            'ouvrier' => $ouvrier
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ouvrier $ouvrier)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ouvrier $ouvrier)
    {
        //
    }
}
