<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserRoleSeeder extends Seeder
{
    public function run()
    {
        // Créer les rôles s'ils n'existent pas
        $roles = ['admin', 'entreprise', 'ouvrier'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Attribuer le rôle en fonction du type
        $users = User::all();

        foreach ($users as $user) {
            if (in_array($user->type, $roles)) {
                $user->assignRole($user->type);
            }
        }
    }
}

