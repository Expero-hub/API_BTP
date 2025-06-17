<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidatureEmploiController;
use App\Http\Controllers\CandidatureProjetController;
use App\Http\Controllers\CandidatureSousTraitanceController;
use App\Http\Controllers\CandidatureStageController;
use App\Http\Controllers\EntrepriseController;
use App\Http\Controllers\EntrepriseProjetController;
use App\Http\Controllers\OffreEmploiController;
use App\Http\Controllers\OffreStageController;
use App\Http\Controllers\OuvrierController;
use App\Http\Controllers\PartenaireController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\SousTraitanceController;
use App\Http\Controllers\StagiaireController;
use Illuminate\Support\Facades\Route;



Route::middleware('auth:sanctum')->get('/profile', function () {
    return auth()->user;
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::patch('/renameProfile',[AuthController::class, 'renameProfile']);

});

//Routes non protégées
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-reset-code', [AuthController::class, 'verifyResetCode']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/entreprises', [AuthController::class, 'entreprises']);

Route::get('/offreEmploi', [OffreEmploiController::class, 'index']);

Route::get('/offreEmploi/{offreEmploi}', [OffreEmploiController::class, 'show']);
Route::get('/offreStage', [OffreStageController::class, 'index']);
Route::get('/offreStage/{offreStage}', [OffreStageController::class, 'show']);
Route::get('/entreprises/offres/{id}', [EntrepriseController::class, 'offresParEntreprise']);

Route::get('/projets', [ProjetController::class, 'index']);
Route::get('/produits', [ProduitController::class, 'index']);




//Routes protégées

Route::middleware('auth:sanctum')->group(function(){
    //Admin
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/users', [AuthController::class, 'index']);
        Route::delete('/deleteUser/{id}', [AuthController::class, 'destroy']);
        Route::patch('/modifier/{id}', [AuthController ::class, 'update']);
        
    });
    //Entreprises
    
    Route::middleware('role:entreprise|admin')->prefix('entreprise')->group(function () {
        Route::post('/completer', [EntrepriseController::class, 'store']);
        //Emploi
        Route::post('/creer/offreEmploi', [OffreEmploiController::class, 'store']);
        Route::delete('/offreEmploi/{offreEmploi}', [OffreEmploiController::class, 'destroy']);
        Route::patch('/modifierEmploi/{offreEmploi}', [OffreEmploiController ::class, 'update']);
        Route::get('/mesOffreEmploi', [OffreEmploiController::class, 'mesOffreEmploi']);
        Route::get('/candidaturesEmpoi', [CandidatureEmploiController::class, 'index']);
        Route::get('/ouvriers', [OuvrierController::class, 'index']);

         //Stage
        Route::post('/creer/offreStage', [OffreStageController::class, 'store']);
        Route::delete('/offreStage/{offreStage}', [OffreStageController::class, 'destroy']);
        Route::patch('/modifierStage/{offreStage}', [OffreStageController ::class, 'update']);
        Route::get('/candidatureStage', [CandidatureStageController::class, 'index']);
        Route::get('/candidatureParOffreStage/{id}', [CandidatureStageController::class, 'voirCandidaturesParOffre']);

         //Sous-traitance
        Route::post('/creer/tache', [SousTraitanceController::class, 'store']);
        Route::delete('/supprimerTache/{sousTraitance}', [SousTraitanceController::class, 'destroy']);
        Route::patch('/modifierTache/{sousTraitance}', [SousTraitanceController ::class, 'update']);
        Route::get('/candidatureTache', [CandidatureSousTraitanceController::class, 'index']);
        Route::get('/taches',  [SousTraitanceController::class, 'index']);
        Route::PATCH('/accepterAssignation/{tache}',  [SousTraitanceController::class, 'repondreAssignation']);
        Route::post('/candidater/{tache}', [CandidatureSousTraitanceController::class, 'store']);
        Route::patch('/candidature_tache/accepter/{id}', [CandidatureSousTraitanceController::class, 'accepter'] );
        Route::patch('/candidature_tache/rejeter/{id}', [CandidatureSousTraitanceController::class, 'rejeter'] );


        // Gestion des candidatures(Accepter ou Rejeter)
        Route::patch('/candidature_emploi/accepter/{id}', [CandidatureEmploiController::class, 'accepter']);
        Route::patch('/candidature_emploi/rejeter/{id}', [CandidatureEmploiController::class, 'rejeter']);
        Route::patch('/candidature_stage/accepter/{id}', [CandidatureStageController::class, 'accepter']);
        Route::patch('/candidature_stage/rejeter/{id}', [CandidatureStageController::class, 'rejeter']);
    });

    //Création de projet
    Route::middleware('role:entreprise|client')->prefix('projet')->group(function () {
        
        // Projet
        Route::post('/creer', [ProjetController::class, 'store']);
        Route::delete('/supprimer/{offreEmploi}', [ProjetController::class, 'destroy']);
        Route::patch('/modifier/{offreEmploi}', [ProjetController ::class, 'update']);
        Route::post('/entrepriseProjet', [EntrepriseProjetController::class, 'store']);
        Route::PATCH('/accepterProjet/{projet}',  [EntrepriseProjetController::class, 'repondreAssignation']);
        Route::get('/projetOuvert', [ProjetController::class, 'projetOuverts']);
        Route::post('/postuler/{projetId}',[CandidatureProjetController::class, 'postuler']);

        
        
    
    });

    //Partenaire
    Route::middleware(['role:partenaire|admin'])->prefix('partenaire')->group(function () {
        Route::post('/produit/ajouter', [ProduitController::class, 'store']);
        Route::post('/completer', [PartenaireController::class, 'store']);
       
        
        
    });
    //Ouvrier
    Route::middleware(['role:ouvrier|admin'])->prefix('ouvrier')->group(function () {
        Route::post('/completer', [OuvrierController::class, 'store']);
        Route::post('/postulerEmploi/{offreEmploi}', [CandidatureEmploiController::class, 'store']);
        Route::get('/mesCandidatures', [CandidatureEmploiController::class, 'mesCandidatures']);
        
        
    });

    //Stagiaire
    Route::middleware(['role:stagiaire|admin'])->prefix('stagiaire')->group(function () {
        Route::post('/completer', [StagiaireController::class, 'store']);
        Route::post('/postulerStage/{offreStage}', [CandidatureStageController::class, 'store']);
        Route::get('/mesCandidatures', [CandidatureStageController::class, 'mesCandidatures']);
        
        
    });
});
Route::middleware(['auth:sanctum', 'role:admin'])->get('/test-role', function () {
    return response()->json(['message' =>' You dispose now the admin privileges ']);
});
