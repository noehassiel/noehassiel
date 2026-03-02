<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    public function run()
    {
        // Crear roles si no existen
        $webmaster = Role::firstOrCreate(['name' => 'webmaster']);
        $client = Role::firstOrCreate(['name' => 'client']);

        // Asignar permisos al rol webmaster
        $webmaster->givePermissionTo('all_access');

        // Asignar permisos al rol citizen
        $client->givePermissionTo('client_access');
    }
}
