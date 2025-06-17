<?php

namespace App\Http\Controllers;

use App\Models\CandidatureProjet;
use App\Models\Projet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidatureProjetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function postuler($projetId, Request $request)
{
    $user = Auth::user();

    if (!$user->hasRole('entreprise') || !$user->entreprise) {
        return response()->json(['message' => 'Seules les entreprises peuvent postuler.'], 403);
    }

    $projet = Projet::where('id', $projetId)
                    ->whereNotNull('client_id')
                    ->whereDoesntHave('entrepriseProjet')
                    ->first();

    if (!$projet) {
        return response()->json(['message' => 'Projet non éligible à la postulation.'], 404);
    }

    $request->validate([
        'motivation' => 'nullable|file|mimes:jpg,jpeg,png,pdf,jfif|max:2048',
    ]);

    if (CandidatureProjet::where('entreprise_id', $user->entreprise->id)->where('projet_id', $projetId)->exists()) {
        return response()->json(['message' => 'Vous avez déjà postulé à ce projet.'], 409);
    }

    if ($request->hasFile('motivation')) {
        $file = $request->file('motivation');
        $imageName = uniqid() . '.' . $file->getClientOriginalName();
        $path = $file->storeAs('documents', $imageName, 'public');
    } else {
        return response()->json(['message' => 'fichier introuvable'], 422);
    }

    $candidature = CandidatureProjet::create([
        'entreprise_id' => $user->entreprise->id,
        'projet_id' => $projetId,
        'motivation' => 'storage/' . $path,
    ]);

    return response()->json([
        'message' => 'Postulation envoyée avec succès.',
        'candidature' => $candidature
    ]);
}



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
