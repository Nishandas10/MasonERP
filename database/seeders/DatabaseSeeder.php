<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'View Projects',        'slug' => 'projects.view',         'module' => 'projects'],
            ['name' => 'Manage Projects',       'slug' => 'projects.manage',       'module' => 'projects'],
            ['name' => 'View Indents',          'slug' => 'indents.view',          'module' => 'procurement'],
            ['name' => 'Create Indents',        'slug' => 'indents.create',        'module' => 'procurement'],
            ['name' => 'Approve Indents',       'slug' => 'indents.approve',       'module' => 'procurement'],
            ['name' => 'View POs',              'slug' => 'pos.view',              'module' => 'procurement'],
            ['name' => 'Manage POs',            'slug' => 'pos.manage',            'module' => 'procurement'],
            ['name' => 'View Subcontractors',   'slug' => 'subcontractors.view',   'module' => 'subcontractors'],
            ['name' => 'Manage Subcontractors', 'slug' => 'subcontractors.manage', 'module' => 'subcontractors'],
            ['name' => 'View Finance',          'slug' => 'finance.view',          'module' => 'finance'],
            ['name' => 'Manage Finance',        'slug' => 'finance.manage',        'module' => 'finance'],
            ['name' => 'Manage Users',          'slug' => 'users.manage',          'module' => 'admin'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['slug' => $p['slug']], $p);
        }

        $company = Company::firstOrCreate(['slug' => 'mason-demo'], [
            'name'   => 'Mason Construction Pvt Ltd',
            'slug'   => 'mason-demo',
            'email'  => 'admin@mason.demo',
            'status' => 'active',
        ]);

        $roleDefinitions = [
            ['name' => 'Admin',           'slug' => 'admin'],
            ['name' => 'Project Manager', 'slug' => 'project_manager'],
            ['name' => 'Site Engineer',   'slug' => 'site_engineer'],
            ['name' => 'Accountant',      'slug' => 'accountant'],
            ['name' => 'Subcontractor',   'slug' => 'subcontractor'],
        ];

        $createdRoles = [];
        foreach ($roleDefinitions as $r) {
            $createdRoles[$r['slug']] = Role::firstOrCreate(
                ['company_id' => $company->id, 'slug' => $r['slug']],
                array_merge($r, ['company_id' => $company->id, 'is_system' => true])
            );
        }

        $allPermissions = Permission::all();
        $createdRoles['admin']->permissions()->sync($allPermissions->pluck('id'));

        $pmPerms = $allPermissions->whereIn('slug', ['projects.view', 'projects.manage', 'indents.view', 'indents.create', 'subcontractors.view']);
        $createdRoles['project_manager']->permissions()->sync($pmPerms->pluck('id'));

        $accPerms = $allPermissions->whereIn('slug', ['finance.view', 'finance.manage', 'projects.view', 'subcontractors.view']);
        $createdRoles['accountant']->permissions()->sync($accPerms->pluck('id'));

        User::firstOrCreate(['email' => 'admin@mason.demo'], [
            'name'       => 'System Admin',
            'email'      => 'admin@mason.demo',
            'password'   => Hash::make('Admin@1234'),
            'company_id' => $company->id,
            'role_id'    => $createdRoles['admin']->id,
            'status'     => 'active',
        ]);

        $this->command->info('Demo data seeded! Login: admin@mason.demo / Admin@1234');

        $this->call(DemoDataSeeder::class);
    }
}
