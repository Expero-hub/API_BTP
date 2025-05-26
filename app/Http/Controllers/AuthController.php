<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule; //gérér l'unicité de l' email sauf celui de user
use Illuminate\Support\Facades\Log ;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    // public function register()
    // {
    //     $user_type = Role::all();
        
    //     return view('register', compact('user_type'));
    // }

    /**
     * Store a newly created resource in storage.
     */
    
    public function register(Request $request)
    {
        Log::info('Avant le log de register');
        try{
            Log::info('register');
            $request->validate([
                'nom' => 'required',
                'prenom' => 'nullable',
                'email' => 'required|email|unique:users',
                'telephone' => 'required',
                'type' => 'required|in:entreprise,ouvrier,stagiaire,partenaire,client,chef_equipe',
                'password' => 'required|min:6',
            ]);
    
            $user = User::create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'type' => $request->type,
                'password' => Hash::make($request->password),
            ]);
    
            // Attribuer le rôle automatiquement
            $user->assignRole($request->type);
    
            //Se connecter automatiquement après inscription
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([

                'message' => 'Inscrition réussie',
                'user' => $user,
                'token' => $token,
            ]);

    
          
        }catch(\Exception $e){

            return response()->json([
                "message" => "Une erreur est survenue lors de l'inscription' ",
                "erreur" => $e->getMessage()
 
            ], 500);
 
        }
        
    }
    

    public function login(Request $request)
    {
       
        // Validation des champs
        try{
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);
    
            // Vérifie si les identifiants sont valides
            if (!Auth::attempt($credentials)) {
                throw ValidationException::withMessages([
                    'email' => ['Les identifiants sont invalides.'],
                ]);
            }
    
            //$request->session()->regenerate(); // optionnel en API
    
            $user = Auth::user();
    
            return response()->json([
                'token' => $user->createToken('api-token')->plainTextToken,
                'user' => $user
            ]);
        }catch(\Exception $e){

            return response()->json([
                "message" => "connexion echouée' ",
                "erreur" => $e->getMessage()
 
            ], 500);
 
        }

    }

    //Déconnexion
    public function logout(Request $request)
        {try{
            Log::info('la deconnexion', $request->all());

            
            $token = $request->user()->currentAccessToken();

            /** @var \Laravel\Sanctum\PersonalAccessToken $token */
            $token->delete();
            
          
            return response()->json([
                'message' => 'Déconnexion réussie.'
            ]);
        }catch(\Exception $e){

            return response()->json([
                "message" => "vous êtes toujours en ligne' ",
                "erreur" => $e->getMessage()
 
            ], 500);
 
        }
    }

    //Profil Utilisateur

    public function profile(Request $request)
    {
        return response()->json([
            'message' => 'Profil récupéré avec succès.',
            'data' => $request->user(),
        ]);
    }

    //Mettre à jour profil utilisateur
    public function renameProfile(Request $request){
        try{

            $request->validate([
                'nom' => 'nullable|string|max:255',
                'prenom' => 'nullable|string|max:255',
                'email' => ['nullable', 'email',Rule::unique('users')->ignore(Auth::id()), ],
                'telephone' => 'nullable|string|max:20',
                'password' => 'required|string|min:6',
                'profile_image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        
            $user = User::findOrFail(Auth::id()); 

            $data = $request->only(['nom', 'prenom', 'telephone',  'email']);
        
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }


            //Traiter l'image
            if($request->hasFile('profile_image_path')){
                //Récupération du fichier
                $file = $request->file('profile_image_path');
                //Générer un nom unique pour l'image
                $imageName = $file->getClientOriginalName();

                // Stockage dans storage/app/public/images
                if($user->profile_image_path && file_exists(public_path($user->profile_image_path))){
                    unlink(public_path($user->profile_image_path));
                }

                $path = $file->storeAs('images', $imageName, 'public');

                $data['profile_image_path'] = 'storage/' . $path;

            }

           
            logger($data);
        
            $user->update($data);
        
            return response()->json([
                'message' => 'Profil mis à jour avec succès.',
                'user' => $user,
            ]);
            
        }catch(\Exception $e){
            return response()->json([
                'message' => 'erreur lors de la mise à jour de votre profil',
                'erreur' => $e->getMessage()
                 
            ], 404);
        }
    }

    //Mot de passe oublié
    public function forgotPassword(Request $request)
    {
        //\Log::info('Début de forgotPassword', ['email' => $request->email]);

        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();
        //\Log::info('Utilisateur trouvé', ['user_id' => $user->id]);

        // Générer un code à 5 chiffres
        $code = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
        Log::info('Code généré', ['code' => $code]);

        // Hacher le code avant de le stocker
        $hashedCode = Hash::make($code);
        Log::info('Code haché', ['hashed_code' => $hashedCode]);

        // Créer un token de réinitialisation avec le code haché
        $resetToken = PasswordResetToken::create([
            'user_id' => $user->id,
            'code' => $hashedCode, // Stocker le code haché
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);
        Log::info('Token de réinitialisation créé', ['reset_token_id' => $resetToken->id]);

        // Envoyer le code (non haché) par email
        try {
            Log::info('Tentative d\'envoi d\'email', ['email' => $user->email]);
            Mail::to($user->email)->send(new \App\Mail\PasswordResetToken($code));

            Log::info('Email envoyé avec succès');
            return response()->json([
                'message' => 'Un code de réinitialisation a été envoyé à votre adresse email.',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Échec de l\'envoi de l\'email', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Échec de l\'envoi de l\'email.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

        public function verifyResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:5',
        ]);
        Log::info('verification',  $request->all());
        

        $user = User::where('email', $request->email)->first();

        $resetToken = PasswordResetToken::where('user_id', $user->id)
            ->where('expires_at', '>', Carbon::now())
            ->first();
           

        if (!$resetToken || !Hash::check($request->code, $resetToken->code)) {
            return response()->json([
                'message' => 'Code invalide ou expiré.',
            ], 400);
        }

        return response()->json([
            'message' => 'Code vérifié avec succès. Vous pouvez maintenant changer votre mot de passe.',
        ]);
    }

        public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:5',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        $resetToken = PasswordResetToken::where('user_id', $user->id)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$resetToken || !Hash::check($request->code, $resetToken->code)) {
            return response()->json([
                'message' => 'Code invalide ou expiré.',
            ], 400);
        }

        // Mettre à jour le mot de passe
        $user->password = Hash::make($request->password);
        $user->save();

        // Supprimer le token de réinitialisation
        $resetToken->delete();

        return response()->json([
            'message' => 'Mot de passe réinitialisé avec succès.',
        ]);
    }

    public function index()
{
    $users = User::all();

    // Charger toutes les relations possibles pour éviter les requêtes multiples (N+1)
    $users->load('entreprise', 'ouvrier', 'stagiaire', 'partenaire', 'client');

    $users = $users->map(function ($user) {
        $data = $user->toArray();

        $relation = $user->type;

        // Supprimer les autres relations de la réponse
        unset($data['entreprise'], $data['ouvrier'], $data['partenaire'], $data['client']);

        // Ajouter le profil correspondant s'il existe
        if (method_exists($user, $relation) && $user->$relation) {
            $data['profil'] = $user->$relation;
        } else {
            $data['profil'] = null;
        }

        return $data;
    });

    return response()->json([
        'message' => 'Utilisateurs',
        'data' => $users
    ]);
}

    public function entreprises()
    {
       $entreprises = User::where('type', 'entreprise')
        ->with('entreprise')
        ->get();


        return response()->json([
            'données' => 'Entreprises',
            'data' => $entreprises, 
            ], 200);
    }


    public function destroy( string $id)
    {
      

            $user = User::where('id', $id)->first();

            if (!$user) {
                return response()->json(['message' => 'Utilisateur introuvable ou déjà supprimé'], 404);
            }

            $user->delete();

            return response()->json(['message' =>' Utilisateur supprimé ']);
        
    }

}
