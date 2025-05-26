<?php

namespace App\Http\Controllers;

use App\Models\Projet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $projet = Projet::where('date_fin', '>=', Carbon::today())->get();

        return response()->json([
            'message' => 'Projet disponibles',
            'Projets' => $projet
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lieu' => 'required|string|max:255',
            'date_debut' => 'nullable|date',
            'date_fin' => 'required|date',
        ]);

        $user = Auth::user();

       
        if (!$user->entreprise || !$user->entreprise->nom_entreprise || !$user->entreprise->IFU){
            return response()->json(['message' => 'Veuillez completer votre profil.'], 403);
        }

        $projet = Projet::create([
            'titre' => $request->titre,
            'description' => $request->description,
            'lieu' => $request->lieu,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'entreprise_id' => $user->entreprise->id ?? null,
            'client_id' => $user->client->id ?? null,
        ]);

        return response()->json(['message' => 'Projet créé avec succès.', 'projet' => $projet], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Projet $projet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Projet $projet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Projet $projet)
    {
        //
    }
}
