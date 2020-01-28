<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = ['Super Admin', 'Admin', 'User'];
        $permissions = ['Create User', 'View User', 'Edit User', 'Delete User'];

        for ($i=0; $i<count($data); $i++){
            Role::create([
                'name' => $data[$i],
                'guard_name' => 'web',
            ]);
        }

        for ($i=0; $i<count($permissions); $i++){
            Permission::create([
                'name' => $permissions[$i],
                'guard_name' => 'web',
            ]);
        }
    }
}
