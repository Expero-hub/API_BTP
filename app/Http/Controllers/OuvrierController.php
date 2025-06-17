<?php

namespace App\Http\Controllers;

use App\Models\Ouvrier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OuvrierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ouvrier = User::where('type', 'ouvrier')
        ->with('ouvrier')
        ->get();


        return response()->json([
            'données' => 'Ouvriers',
            'data' => $ouvrier, 
            ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    try {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $request->validate([
            'metier' => 'required|string|max:255',
            'certifications' => 'nullable|file|mimes:jpg,jpeg,png,pdf,jfif|max:2048',
            'cv' => 'required|file|mimes:jpg,jpeg,png,pdf,jfif|max:2048',
            'diplome' => 'nullable|file|mimes:jpg,jpeg,png,pdf,jfif|max:2048',
        ]);

        $diplomePath = $request->hasFile('diplome') 
            ? $this->uploadDocument($request->file('diplome'), 'diplome') 
            : null;

        $certificationsPath = $request->hasFile('certifications') 
            ? $this->uploadDocument($request->file('certifications'), 'certifications') 
            : null;

            if ($request->hasFile('cv')) {
                $cvPath = $this->uploadDocument($request->file('cv'), 'cv');
                logger("fichier cv stocké ici: $cvPath");
            } else {
                logger('Aucun fichier reçu pour cv');
            }

        $ouvrier = Ouvrier::create([
            'id' => $user->id, // réutilise l'id utilisateur (clé primaire partagée)
            'metier' => $request->metier,
            'diplome' => $diplomePath,
            'certifications' => $certificationsPath,
            'cv' => $cvPath,
        ])->load('user');

        return response()->json([
            'message' => 'Profil complété avec succès',
            'document' => $ouvrier
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            "message" => "Une erreur est survenue lors de l'enregistrement",
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

    // 👇 Ajout du 3e paramètre 'public' pour utiliser le disque public
    $path = $file->storeAs('documents', $filename, 'public');

    // 👇 Retourne le chemin accessible via le lien symbolique
    return 'storage/' . $path;
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
