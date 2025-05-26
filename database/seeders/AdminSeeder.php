<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Crée le rôle s'il n'existe pas
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $admin = User::create([
            'nom' => 'Admin',
            'prenom' => 'Super',
            'email' => 'admin@gmail.com',
            'telephone' => '0000000000',
            'type' => 'admin',
            'password' => Hash::make('password'),
        ]);
    
        // Attribue le rôle
        $admin->assignRole($adminRole);
    }
}
