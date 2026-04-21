<?php

namespace App\Services;

use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $company = Company::create([
                'name'   => $data['company_name'],
                'slug'   => Str::slug($data['company_name']) . '-' . uniqid(),
                'email'  => $data['email'],
                'status' => 'active',
            ]);

            // Create default roles for the company
            $adminRole = Role::create([
                'company_id'  => $company->id,
                'name'        => 'Admin',
                'slug'        => 'admin',
                'description' => 'Company Administrator',
                'is_system'   => true,
            ]);

            $defaultRoles = [
                ['name' => 'Project Manager', 'slug' => 'project_manager'],
                ['name' => 'Site Engineer', 'slug' => 'site_engineer'],
                ['name' => 'Accountant', 'slug' => 'accountant'],
                ['name' => 'Subcontractor', 'slug' => 'subcontractor'],
            ];

            foreach ($defaultRoles as $roleData) {
                Role::create(array_merge($roleData, [
                    'company_id' => $company->id,
                    'is_system'  => true,
                ]));
            }

            $user = User::create([
                'name'       => $data['name'],
                'email'      => $data['email'],
                'password'   => Hash::make($data['password']),
                'company_id' => $company->id,
                'role_id'    => $adminRole->id,
                'status'     => 'active',
            ]);

            $token = $user->createToken('mason-erp', ['*'], now()->addDays(30));

            return [
                'user'    => $user->load('company', 'role'),
                'token'   => $token->plainTextToken,
                'company' => $company,
            ];
        });
    }

    public function login(string $email, string $password, ?string $deviceName = null): array
    {
        $user = User::where('email', $email)->with('company', 'role')->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated. Contact your administrator.'],
            ]);
        }

        $user->update(['last_login_at' => now()]);

        // Revoke old tokens on same device
        $user->tokens()->where('name', $deviceName ?? 'mason-erp')->delete();

        $token = $user->createToken($deviceName ?? 'mason-erp', ['*'], now()->addDays(30));

        return [
            'user'  => $user,
            'token' => $token->plainTextToken,
        ];
    }

    public function logout(User $user): void
    {
        /** @var PersonalAccessToken|null $token */
        $token = $user->currentAccessToken();
        $token?->delete();
    }

    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
    }

    public function refreshToken(User $user): array
    {
        /** @var PersonalAccessToken|null $token */
        $token = $user->currentAccessToken();
        $token?->delete();
        $token = $user->createToken('mason-erp', ['*'], now()->addDays(30));

        return ['token' => $token->plainTextToken];
    }
}
