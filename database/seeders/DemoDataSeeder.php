<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private Company $company;
    private User $admin;
    private User $pm;
    private User $engineer;
    private User $accountant;

    public function run(): void
    {
        $this->company  = Company::where('slug', 'mason-demo')->firstOrFail();
        $this->admin    = User::where('email', 'admin@mason.demo')->firstOrFail();

        $this->createUsers();
        $this->createMaterials();
        $this->createVendors();
        $projects = $this->createProjects();
        $this->createLaborers();
        $this->createEquipment($projects);
        $this->createSubcontractors($projects);
        $this->createExpenseCategoriesAndExpenses($projects);
        $this->createProcurement($projects);

        $this->command->info('Demo data seeded successfully!');
    }

    // -------------------------------------------------------------------------
    private function createUsers(): void
    {
        $roles = Role::where('company_id', $this->company->id)->pluck('id', 'slug');

        $this->pm = User::firstOrCreate(['email' => 'pm@mason.demo'], [
            'name'       => 'Rajesh Kumar',
            'password'   => Hash::make('Admin@1234'),
            'company_id' => $this->company->id,
            'role_id'    => $roles['project_manager'],
            'status'     => 'active',
        ]);

        $this->engineer = User::firstOrCreate(['email' => 'engineer@mason.demo'], [
            'name'       => 'Priya Sharma',
            'password'   => Hash::make('Admin@1234'),
            'company_id' => $this->company->id,
            'role_id'    => $roles['site_engineer'],
            'status'     => 'active',
        ]);

        $this->accountant = User::firstOrCreate(['email' => 'accounts@mason.demo'], [
            'name'       => 'Suresh Patel',
            'password'   => Hash::make('Admin@1234'),
            'company_id' => $this->company->id,
            'role_id'    => $roles['accountant'],
            'status'     => 'active',
        ]);
    }

    // -------------------------------------------------------------------------
    private function createMaterials(): array
    {
        $materials = [
            ['name' => 'OPC Cement 53 Grade', 'code' => 'MAT-001', 'unit' => 'bag',  'category' => 'Civil',     'standard_rate' => 420],
            ['name' => 'River Sand',           'code' => 'MAT-002', 'unit' => 'cft',  'category' => 'Civil',     'standard_rate' => 55],
            ['name' => 'Crushed Stone 20mm',   'code' => 'MAT-003', 'unit' => 'cft',  'category' => 'Civil',     'standard_rate' => 65],
            ['name' => 'TMT Steel 12mm',       'code' => 'MAT-004', 'unit' => 'kg',   'category' => 'Civil',     'standard_rate' => 72],
            ['name' => 'TMT Steel 16mm',       'code' => 'MAT-005', 'unit' => 'kg',   'category' => 'Civil',     'standard_rate' => 70],
            ['name' => 'Red Bricks',           'code' => 'MAT-006', 'unit' => 'nos',  'category' => 'Civil',     'standard_rate' => 9],
            ['name' => 'AAC Blocks 200mm',     'code' => 'MAT-007', 'unit' => 'nos',  'category' => 'Civil',     'standard_rate' => 55],
            ['name' => 'Plywood 18mm BWP',     'code' => 'MAT-008', 'unit' => 'sft',  'category' => 'Finishing', 'standard_rate' => 120],
            ['name' => 'Ceramic Floor Tile',   'code' => 'MAT-009', 'unit' => 'sft',  'category' => 'Finishing', 'standard_rate' => 85],
            ['name' => 'PVC Door 32x80',       'code' => 'MAT-010', 'unit' => 'nos',  'category' => 'Finishing', 'standard_rate' => 3500],
            ['name' => 'CPVC Pipe 1inch',      'code' => 'MAT-011', 'unit' => 'rmt',  'category' => 'Plumbing',  'standard_rate' => 320],
            ['name' => 'MS Conduit 25mm',      'code' => 'MAT-012', 'unit' => 'rmt',  'category' => 'Electrical','standard_rate' => 180],
            ['name' => 'Admixture Plasticizer','code' => 'MAT-013', 'unit' => 'ltr',  'category' => 'Civil',     'standard_rate' => 110],
            ['name' => 'Shuttering Oil',       'code' => 'MAT-014', 'unit' => 'ltr',  'category' => 'Civil',     'standard_rate' => 95],
            ['name' => 'Binding Wire',         'code' => 'MAT-015', 'unit' => 'kg',   'category' => 'Civil',     'standard_rate' => 85],
        ];

        $ids = [];
        foreach ($materials as $m) {
            $row = DB::table('materials')->where('code', $m['code'])->first();
            if (!$row) {
                $ids[] = DB::table('materials')->insertGetId(array_merge($m, [
                    'company_id' => $this->company->id,
                    'status'     => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            } else {
                $ids[] = $row->id;
            }
        }
        return $ids;
    }

    // -------------------------------------------------------------------------
    private function createVendors(): array
    {
        $vendors = [
            ['name' => 'Bharat Building Supplies',    'code' => 'VND-001', 'contact_person' => 'Anil Gupta',   'phone' => '9876543210', 'email' => 'anil@bharatbuild.com',  'city' => 'Mumbai',    'gstin' => '27ABCDE1234F1Z5', 'pan' => 'ABCDE1234F', 'bank_name' => 'SBI',   'bank_account' => '30012345678', 'bank_ifsc' => 'SBIN0001234'],
            ['name' => 'Ramesh Steel Traders',        'code' => 'VND-002', 'contact_person' => 'Ramesh Shah',  'phone' => '9765432100', 'email' => 'ramesh@steeltraders.com','city' => 'Pune',      'gstin' => '27FGHIJ5678K2Z3', 'pan' => 'FGHIJ5678K', 'bank_name' => 'HDFC',  'bank_account' => '50012345678', 'bank_ifsc' => 'HDFC0001234'],
            ['name' => 'National Cement Agency',      'code' => 'VND-003', 'contact_person' => 'Kavita Joshi', 'phone' => '9654321009', 'email' => 'kavita@nca.in',         'city' => 'Nashik',    'gstin' => '27LMNOP9012Q3Z7', 'pan' => 'LMNOP9012Q', 'bank_name' => 'Axis',  'bank_account' => '91112345678', 'bank_ifsc' => 'UTIB0001234'],
            ['name' => 'Metro Tiles & Ceramics',      'code' => 'VND-004', 'contact_person' => 'Sanjay Mehta', 'phone' => '9543210987', 'email' => 'sanjay@metrotiles.com', 'city' => 'Nagpur',    'gstin' => '27RSTUV3456W4Z1', 'pan' => 'RSTUV3456W', 'bank_name' => 'ICICI', 'bank_account' => '00012345678', 'bank_ifsc' => 'ICIC0001234'],
            ['name' => 'Sunrise Electrical Works',    'code' => 'VND-005', 'contact_person' => 'Deepak Rao',   'phone' => '9432109876', 'email' => 'deepak@sunriseelec.com','city' => 'Aurangabad', 'gstin' => '27XYZAB7890C5Z9', 'pan' => 'XYZAB7890C', 'bank_name' => 'SBI',   'bank_account' => '30098765432', 'bank_ifsc' => 'SBIN0005678'],
        ];

        $ids = [];
        foreach ($vendors as $v) {
            $row = DB::table('vendors')->where('code', $v['code'])->where('company_id', $this->company->id)->first();
            if (!$row) {
                $ids[] = DB::table('vendors')->insertGetId(array_merge($v, [
                    'company_id' => $this->company->id,
                    'status'     => 'active',
                    'state'      => 'Maharashtra',
                    'address'    => '123 Industrial Area',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            } else {
                $ids[] = $row->id;
            }
        }
        return $ids;
    }

    // -------------------------------------------------------------------------
    private function createProjects(): array
    {
        $defs = [
            [
                'name'            => 'Skyline Residency - Phase 1',
                'code'            => 'PRJ-001',
                'description'     => 'Construction of 120-unit residential apartment complex with basement parking.',
                'client_name'     => 'Skyline Developers Pvt Ltd',
                'client_contact'  => '+91 98765 43210 | ceo@skylinedevelopers.com',
                'location'        => 'Andheri West, Mumbai',
                'start_date'      => '2025-01-15',
                'end_date'        => '2026-12-31',
                'contract_value'  => 18500000,
                'budget'          => 16000000,
                'status'          => 'in_progress',
                'progress_percent'=> 42,
            ],
            [
                'name'            => 'MegaMall Commercial Hub',
                'code'            => 'PRJ-002',
                'description'     => '4-floor commercial mall with food court and multiplex. G+4 structure.',
                'client_name'     => 'MegaMall Realty LLP',
                'client_contact'  => '+91 87654 32109 | projects@megamall.in',
                'location'        => 'Baner, Pune',
                'start_date'      => '2025-03-01',
                'end_date'        => '2026-09-30',
                'contract_value'  => 35000000,
                'budget'          => 30000000,
                'status'          => 'in_progress',
                'progress_percent'=> 28,
            ],
            [
                'name'            => 'Highway NH-48 Bridge Repair',
                'code'            => 'PRJ-003',
                'description'     => 'Structural repair and waterproofing of existing highway bridge.',
                'client_name'     => 'NHAI (National Highways Authority)',
                'client_contact'  => '+91 11 2345 6789 | projects@nhai.gov.in',
                'location'        => 'Vadodara – Ahmedabad NH-48',
                'start_date'      => '2024-10-01',
                'end_date'        => '2025-03-31',
                'contract_value'  => 4200000,
                'budget'          => 3800000,
                'status'          => 'completed',
                'progress_percent'=> 100,
            ],
            [
                'name'            => 'Green Valley School Campus',
                'code'            => 'PRJ-004',
                'description'     => 'New school building G+2 with 30 classrooms, labs, and playground.',
                'client_name'     => 'Green Valley Education Trust',
                'client_contact'  => '+91 76543 21098 | principal@greenvalley.edu',
                'location'        => 'Wakad, Pune',
                'start_date'      => '2026-06-01',
                'end_date'        => '2027-05-31',
                'contract_value'  => 9500000,
                'budget'          => 8500000,
                'status'          => 'planned',
                'progress_percent'=> 0,
            ],
            [
                'name'            => 'Industrial Warehouse Complex',
                'code'            => 'PRJ-005',
                'description'     => 'Pre-engineered steel structure warehouse 5000 sqm with offices.',
                'client_name'     => 'LogiPark Industries Ltd',
                'client_contact'  => '+91 65432 10987 | infra@logipark.co.in',
                'location'        => 'Bhiwandi, Thane',
                'start_date'      => '2025-06-01',
                'end_date'        => '2025-12-31',
                'contract_value'  => 7800000,
                'budget'          => 7000000,
                'status'          => 'on_hold',
                'progress_percent'=> 15,
            ],
        ];

        $projectIds = [];
        foreach ($defs as $p) {
            $existing = DB::table('projects')->where('code', $p['code'])->where('company_id', $this->company->id)->first();
            if ($existing) {
                $projectIds[] = $existing->id;
                continue;
            }
            $pid = DB::table('projects')->insertGetId(array_merge($p, [
                'company_id' => $this->company->id,
                'created_by' => $this->admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
            $projectIds[] = $pid;

            // Members
            DB::table('project_members')->insertOrIgnore([
                ['project_id' => $pid, 'user_id' => $this->pm->id,       'role' => 'project_manager', 'assigned_at' => $p['start_date'], 'created_at' => now(), 'updated_at' => now()],
                ['project_id' => $pid, 'user_id' => $this->engineer->id,  'role' => 'site_engineer',   'assigned_at' => $p['start_date'], 'created_at' => now(), 'updated_at' => now()],
                ['project_id' => $pid, 'user_id' => $this->accountant->id,'role' => 'accountant',      'assigned_at' => $p['start_date'], 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $this->createMilestones($projectIds);
        $this->createBoqItems($projectIds);

        return $projectIds;
    }

    private function createMilestones(array $pids): void
    {
        $sets = [
            $pids[0] => [
                ['name' => 'Foundation & Piling',       'due_date' => '2025-03-31', 'status' => 'completed', 'progress_percent' => 100, 'completed_date' => '2025-03-28'],
                ['name' => 'Basement Structure',        'due_date' => '2025-06-30', 'status' => 'completed', 'progress_percent' => 100, 'completed_date' => '2025-06-25'],
                ['name' => 'Ground Floor Slab',         'due_date' => '2025-09-30', 'status' => 'completed', 'progress_percent' => 100, 'completed_date' => '2025-09-28'],
                ['name' => '1st to 4th Floor Structure','due_date' => '2026-02-28', 'status' => 'in_progress','progress_percent' => 60, 'completed_date' => null],
                ['name' => 'MEP Rough-in Works',        'due_date' => '2026-06-30', 'status' => 'pending',   'progress_percent' => 0,  'completed_date' => null],
                ['name' => 'Finishing & Handover',      'due_date' => '2026-12-31', 'status' => 'pending',   'progress_percent' => 0,  'completed_date' => null],
            ],
            $pids[1] => [
                ['name' => 'Site Preparation & Excavation','due_date' => '2025-05-15', 'status' => 'completed', 'progress_percent' => 100, 'completed_date' => '2025-05-12'],
                ['name' => 'Foundation & Raft Slab',       'due_date' => '2025-08-31', 'status' => 'completed', 'progress_percent' => 100, 'completed_date' => '2025-08-28'],
                ['name' => 'Ground & Mezzanine Floor',     'due_date' => '2025-12-31', 'status' => 'in_progress','progress_percent' => 45, 'completed_date' => null],
                ['name' => 'Upper Floors 1 to 3',          'due_date' => '2026-05-31', 'status' => 'pending',   'progress_percent' => 0,  'completed_date' => null],
                ['name' => 'Facade & Interior Finishing',  'due_date' => '2026-09-30', 'status' => 'pending',   'progress_percent' => 0,  'completed_date' => null],
            ],
            $pids[2] => [
                ['name' => 'Survey & Condition Assessment','due_date' => '2024-11-15', 'status' => 'completed', 'progress_percent' => 100, 'completed_date' => '2024-11-10'],
                ['name' => 'Concrete Repair Works',        'due_date' => '2025-01-31', 'status' => 'completed', 'progress_percent' => 100, 'completed_date' => '2025-01-29'],
                ['name' => 'Waterproofing Application',    'due_date' => '2025-02-28', 'status' => 'completed', 'progress_percent' => 100, 'completed_date' => '2025-02-26'],
                ['name' => 'Load Testing & Handover',      'due_date' => '2025-03-31', 'status' => 'completed', 'progress_percent' => 100, 'completed_date' => '2025-03-28'],
            ],
            $pids[3] => [
                ['name' => 'Design & Drawing Approval',  'due_date' => '2026-05-31', 'status' => 'pending', 'progress_percent' => 0, 'completed_date' => null],
                ['name' => 'Foundation Works',           'due_date' => '2026-08-31', 'status' => 'pending', 'progress_percent' => 0, 'completed_date' => null],
                ['name' => 'Superstructure',             'due_date' => '2026-12-31', 'status' => 'pending', 'progress_percent' => 0, 'completed_date' => null],
                ['name' => 'Finishing & Handover',       'due_date' => '2027-05-31', 'status' => 'pending', 'progress_percent' => 0, 'completed_date' => null],
            ],
            $pids[4] => [
                ['name' => 'Site Clearing & Levelling', 'due_date' => '2025-07-31', 'status' => 'completed', 'progress_percent' => 100, 'completed_date' => '2025-07-28'],
                ['name' => 'Pile Foundation',           'due_date' => '2025-09-30', 'status' => 'in_progress', 'progress_percent' => 30, 'completed_date' => null],
                ['name' => 'Steel Structure Erection',  'due_date' => '2025-11-30', 'status' => 'pending', 'progress_percent' => 0, 'completed_date' => null],
            ],
        ];

        foreach ($sets as $pid => $milestones) {
            foreach ($milestones as $m) {
                DB::table('milestones')->insertOrIgnore(array_merge($m, [
                    'project_id' => $pid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    private function createBoqItems(array $pids): void
    {
        $items = [
            // Project 1 - Skyline Residency
            ['project_id' => $pids[0], 'item_code' => 'BOQ-001', 'description' => 'Earthwork Excavation',       'unit' => 'cum', 'quantity' => 2500, 'rate' => 350,   'category' => 'Civil',      'consumed_quantity' => 2500],
            ['project_id' => $pids[0], 'item_code' => 'BOQ-002', 'description' => 'PCC M10 for Foundation',    'unit' => 'cum', 'quantity' => 480,  'rate' => 4800,  'category' => 'Civil',      'consumed_quantity' => 480],
            ['project_id' => $pids[0], 'item_code' => 'BOQ-003', 'description' => 'RCC M25 Columns & Beams',   'unit' => 'cum', 'quantity' => 1200, 'rate' => 8500,  'category' => 'Civil',      'consumed_quantity' => 520],
            ['project_id' => $pids[0], 'item_code' => 'BOQ-004', 'description' => 'Brick Masonry Work',        'unit' => 'cum', 'quantity' => 800,  'rate' => 4200,  'category' => 'Civil',      'consumed_quantity' => 240],
            ['project_id' => $pids[0], 'item_code' => 'BOQ-005', 'description' => 'Internal Plastering',       'unit' => 'sqm', 'quantity' => 9500, 'rate' => 280,   'category' => 'Finishing',  'consumed_quantity' => 0],
            // Project 2 - MegaMall
            ['project_id' => $pids[1], 'item_code' => 'BOQ-006', 'description' => 'Raft Foundation M30',       'unit' => 'cum', 'quantity' => 1800, 'rate' => 9200,  'category' => 'Civil',      'consumed_quantity' => 1800],
            ['project_id' => $pids[1], 'item_code' => 'BOQ-007', 'description' => 'Shear Walls & Core',        'unit' => 'cum', 'quantity' => 960,  'rate' => 11500, 'category' => 'Civil',      'consumed_quantity' => 320],
            ['project_id' => $pids[1], 'item_code' => 'BOQ-008', 'description' => 'Structural Steel Work',     'unit' => 'MT',  'quantity' => 450,  'rate' => 85000, 'category' => 'Structural', 'consumed_quantity' => 90],
            // Project 3 - Bridge (completed)
            ['project_id' => $pids[2], 'item_code' => 'BOQ-009', 'description' => 'Concrete Crack Repair',     'unit' => 'rmt', 'quantity' => 320,  'rate' => 1800,  'category' => 'Civil',      'consumed_quantity' => 320],
            ['project_id' => $pids[2], 'item_code' => 'BOQ-010', 'description' => 'Polymer Modified Overlay',  'unit' => 'sqm', 'quantity' => 1200, 'rate' => 1200,  'category' => 'Civil',      'consumed_quantity' => 1200],
        ];

        foreach ($items as $item) {
            DB::table('boq_items')->insertOrIgnore(array_merge($item, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    // -------------------------------------------------------------------------
    private function createLaborers(): void
    {
        $laborers = [
            ['name' => 'Mohammed Irfan',   'code' => 'LAB-001', 'trade' => 'mason',       'daily_rate' => 850,  'phone' => '9111111101', 'aadhaar' => '1234 5678 9001', 'status' => 'active'],
            ['name' => 'Sukhdev Singh',    'code' => 'LAB-002', 'trade' => 'mason',       'daily_rate' => 820,  'phone' => '9111111102', 'aadhaar' => '1234 5678 9002', 'status' => 'active'],
            ['name' => 'Raju Yadav',       'code' => 'LAB-003', 'trade' => 'carpenter',   'daily_rate' => 950,  'phone' => '9111111103', 'aadhaar' => '1234 5678 9003', 'status' => 'active'],
            ['name' => 'Anwar Khan',       'code' => 'LAB-004', 'trade' => 'carpenter',   'daily_rate' => 900,  'phone' => '9111111104', 'aadhaar' => '1234 5678 9004', 'status' => 'active'],
            ['name' => 'Gopal Nair',       'code' => 'LAB-005', 'trade' => 'plumber',     'daily_rate' => 1000, 'phone' => '9111111105', 'aadhaar' => '1234 5678 9005', 'status' => 'active'],
            ['name' => 'Vikram Thakur',    'code' => 'LAB-006', 'trade' => 'electrician', 'daily_rate' => 1050, 'phone' => '9111111106', 'aadhaar' => '1234 5678 9006', 'status' => 'active'],
            ['name' => 'Sita Ram Meena',   'code' => 'LAB-007', 'trade' => 'helper',      'daily_rate' => 650,  'phone' => '9111111107', 'aadhaar' => '1234 5678 9007', 'status' => 'active'],
            ['name' => 'Bhushan Patil',    'code' => 'LAB-008', 'trade' => 'helper',      'daily_rate' => 640,  'phone' => '9111111108', 'aadhaar' => '1234 5678 9008', 'status' => 'active'],
            ['name' => 'Naresh Kumar',     'code' => 'LAB-009', 'trade' => 'painter',     'daily_rate' => 880,  'phone' => '9111111109', 'aadhaar' => '1234 5678 9009', 'status' => 'active'],
            ['name' => 'Deepak Vishwakarma','code'=> 'LAB-010', 'trade' => 'welder',      'daily_rate' => 1100, 'phone' => '9111111110', 'aadhaar' => '1234 5678 9010', 'status' => 'active'],
            ['name' => 'Santosh Mishra',   'code' => 'LAB-011', 'trade' => 'mason',       'daily_rate' => 830,  'phone' => '9111111111', 'aadhaar' => '1234 5678 9011', 'status' => 'inactive'],
            ['name' => 'Lakshman Das',     'code' => 'LAB-012', 'trade' => 'helper',      'daily_rate' => 620,  'phone' => '9111111112', 'aadhaar' => '1234 5678 9012', 'status' => 'active'],
        ];

        foreach ($laborers as $l) {
            $existing = DB::table('laborers')->where('code', $l['code'])->where('company_id', $this->company->id)->first();
            if ($existing) continue;
            DB::table('laborers')->insert(array_merge($l, [
                'company_id'       => $this->company->id,
                'emergency_contact'=> '9000000000',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]));
        }

        // Seed attendance for first project (last 10 days)
        $laborerIds = DB::table('laborers')->where('company_id', $this->company->id)->where('status', 'active')->pluck('id');
        $projectId  = DB::table('projects')->where('code', 'PRJ-001')->value('id');
        if (!$projectId) return;

        $statuses = ['present', 'present', 'present', 'present', 'half_day', 'present', 'present', 'absent', 'present', 'overtime'];
        foreach ($laborerIds->take(8) as $lid) {
            for ($i = 0; $i < 10; $i++) {
                $date = now()->subDays($i)->format('Y-m-d');
                $st   = $statuses[$i];
                DB::table('attendance')->insertOrIgnore([
                    'company_id'     => $this->company->id,
                    'project_id'     => $projectId,
                    'laborer_id'     => $lid,
                    'date'           => $date,
                    'status'         => $st,
                    'hours_worked'   => $st === 'half_day' ? 4 : ($st === 'absent' ? 0 : 8),
                    'overtime_hours' => $st === 'overtime' ? 2 : 0,
                    'marked_by'      => $this->engineer->id,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }
    }

    // -------------------------------------------------------------------------
    private function createEquipment(array $projectIds): void
    {
        $equipment = [
            ['name' => 'Tower Crane TC-60',          'code' => 'EQP-001', 'type' => 'crane',      'make' => 'Liebherr', 'model' => 'TC-60',    'registration_number' => 'MH-04-TU-1234', 'purchase_date' => '2022-01-15', 'purchase_value' => 8500000, 'ownership' => 'owned',  'rental_rate_per_day' => 12000, 'status' => 'deployed'],
            ['name' => 'Excavator Komatsu PC200',    'code' => 'EQP-002', 'type' => 'excavator',  'make' => 'Komatsu',  'model' => 'PC200-8',  'registration_number' => 'MH-04-TU-1235', 'purchase_date' => '2021-06-10', 'purchase_value' => 5500000, 'ownership' => 'owned',  'rental_rate_per_day' => 8000,  'status' => 'deployed'],
            ['name' => 'Concrete Batching Plant',    'code' => 'EQP-003', 'type' => 'batching_plant','make'=> 'Schwing', 'model' => 'Stetter M1','registration_number' => 'MH-04-TU-1236', 'purchase_date' => '2023-03-01', 'purchase_value' => 4200000, 'ownership' => 'owned',  'rental_rate_per_day' => 6000,  'status' => 'deployed'],
            ['name' => 'Transit Mixer 7.5m3',        'code' => 'EQP-004', 'type' => 'transit_mixer','make'=> 'Ashok Leyland','model'=> 'Boss','registration_number' => 'MH-04-TU-1237', 'purchase_date' => '2020-11-20', 'purchase_value' => 2800000, 'ownership' => 'owned',  'rental_rate_per_day' => 4500,  'status' => 'available'],
            ['name' => 'Concrete Pump Truck',        'code' => 'EQP-005', 'type' => 'pump',       'make' => 'CIFA',     'model' => 'K35L',     'registration_number' => 'MH-04-TU-1238', 'purchase_date' => '2023-07-05', 'purchase_value' => 3600000, 'ownership' => 'owned',  'rental_rate_per_day' => 5500,  'status' => 'available'],
            ['name' => 'Backhoe Loader JCB 3DX',    'code' => 'EQP-006', 'type' => 'backhoe',    'make' => 'JCB',      'model' => '3DX',      'registration_number' => 'MH-04-TU-1239', 'purchase_date' => '2019-04-15', 'purchase_value' => 1800000, 'ownership' => 'owned',  'rental_rate_per_day' => 3000,  'status' => 'maintenance'],
            ['name' => 'Welding Machine Lincoln',    'code' => 'EQP-007', 'type' => 'welding',    'make' => 'Lincoln',  'model' => 'Invertec', 'registration_number' => null,             'purchase_date' => '2021-08-01', 'purchase_value' => 85000,   'ownership' => 'owned',  'rental_rate_per_day' => 800,   'status' => 'available'],
            ['name' => 'Tower Light Set 4x1000W',   'code' => 'EQP-008', 'type' => 'lighting',   'make' => 'Generic',  'model' => 'TL-4000',  'registration_number' => null,             'purchase_date' => '2022-05-10', 'purchase_value' => 125000,  'ownership' => 'owned',  'rental_rate_per_day' => 500,   'status' => 'deployed'],
        ];

        $eqpIds = [];
        foreach ($equipment as $e) {
            $existing = DB::table('equipment')->where('code', $e['code'])->where('company_id', $this->company->id)->first();
            if ($existing) {
                $eqpIds[] = $existing->id;
                continue;
            }
            $eid = DB::table('equipment')->insertGetId(array_merge($e, [
                'company_id' => $this->company->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
            $eqpIds[] = $eid;
        }

        // Assign deployed equipment to Project 1
        $deployedCodes = ['EQP-001', 'EQP-002', 'EQP-003', 'EQP-008'];
        foreach ($deployedCodes as $code) {
            $eid = DB::table('equipment')->where('code', $code)->where('company_id', $this->company->id)->value('id');
            if (!$eid) continue;
            DB::table('equipment_assignments')->insertOrIgnore([
                'equipment_id'  => $eid,
                'project_id'    => $projectIds[0],
                'assigned_date' => '2025-01-20',
                'assigned_by'   => $this->admin->id,
                'remarks'       => 'Assigned for Skyline Phase 1 construction.',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        // Maintenance log for JCB
        $jcbId = DB::table('equipment')->where('code', 'EQP-006')->where('company_id', $this->company->id)->value('id');
        if ($jcbId) {
            DB::table('maintenance_logs')->insertOrIgnore([
                'equipment_id'         => $jcbId,
                'maintenance_date'     => now()->subDays(3)->format('Y-m-d'),
                'type'                 => 'breakdown',
                'description'          => 'Hydraulic pump failure. Replacement in progress.',
                'cost'                 => 45000,
                'done_by'              => 'Sai Hydraulics Pvt Ltd',
                'next_maintenance_date'=> now()->addDays(60)->format('Y-m-d'),
                'status'               => 'in_progress',
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }
    }

    // -------------------------------------------------------------------------
    private function createSubcontractors(array $projectIds): void
    {
        $subs = [
            ['name' => 'Supreme MEP Solutions',        'code' => 'SUB-001', 'contact_person' => 'Harish Nair',    'phone' => '9222222201', 'email' => 'harish@suprememep.com',  'specialization' => 'MEP',           'gstin' => '27ABCMEP234F1Z5', 'pan' => 'ABCME1234F'],
            ['name' => 'Alpha Civil Contractors',      'code' => 'SUB-002', 'contact_person' => 'Ramakant Desai', 'phone' => '9222222202', 'email' => 'ramakant@alphacivil.com','specialization' => 'Civil',         'gstin' => '27FGHCI678K2Z3', 'pan' => 'FGHCI5678K'],
            ['name' => 'BrightStar Interior Works',   'code' => 'SUB-003', 'contact_person' => 'Neha Kulkarni',  'phone' => '9222222203', 'email' => 'neha@brightstar.in',     'specialization' => 'Interiors',     'gstin' => '27LMNIN012Q3Z7', 'pan' => 'LMNIN9012Q'],
            ['name' => 'PeakLoad Structural Works',   'code' => 'SUB-004', 'contact_person' => 'Aakash Verma',   'phone' => '9222222204', 'email' => 'aakash@peakload.com',    'specialization' => 'Structural',    'gstin' => '27RSTST456W4Z1', 'pan' => 'RSTST3456W'],
            ['name' => 'TechFab Waterproofing Co.',   'code' => 'SUB-005', 'contact_person' => 'Farhan Shaikh',  'phone' => '9222222205', 'email' => 'farhan@techfab.in',      'specialization' => 'Waterproofing', 'gstin' => '27XYZWP890C5Z9', 'pan' => 'XYZWP7890C'],
        ];

        $subIds = [];
        foreach ($subs as $s) {
            $existing = DB::table('subcontractors')->where('code', $s['code'])->where('company_id', $this->company->id)->first();
            if ($existing) {
                $subIds[$s['code']] = $existing->id;
                continue;
            }
            $sid = DB::table('subcontractors')->insertGetId(array_merge($s, [
                'company_id' => $this->company->id,
                'address'    => 'Mumbai, Maharashtra',
                'bank_name'  => 'HDFC Bank',
                'bank_account'=> '50' . rand(1000000000, 9999999999),
                'bank_ifsc'  => 'HDFC0001234',
                'status'     => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
            $subIds[$s['code']] = $sid;
        }

        // Contracts
        $contracts = [
            ['sub_code' => 'SUB-001', 'project_idx' => 0, 'contract_number' => 'CON-2025-001', 'start_date' => '2025-09-01', 'end_date' => '2026-10-31', 'scope_of_work' => 'Complete MEP works including electrical, plumbing, firefighting, HVAC for 120-unit residential complex.', 'contract_value' => 2800000, 'retention_percent' => 5, 'status' => 'active'],
            ['sub_code' => 'SUB-002', 'project_idx' => 0, 'contract_number' => 'CON-2025-002', 'start_date' => '2025-01-20', 'end_date' => '2026-06-30', 'scope_of_work' => 'RCC structural works, masonry, plastering for all floors.', 'contract_value' => 5500000, 'retention_percent' => 5, 'status' => 'active'],
            ['sub_code' => 'SUB-003', 'project_idx' => 1, 'contract_number' => 'CON-2025-003', 'start_date' => '2026-01-01', 'end_date' => '2026-09-30', 'scope_of_work' => 'Mall interior fit-out including false ceiling, flooring, glazing, signage.', 'contract_value' => 4200000, 'retention_percent' => 3, 'status' => 'draft'],
            ['sub_code' => 'SUB-004', 'project_idx' => 1, 'contract_number' => 'CON-2025-004', 'start_date' => '2025-03-10', 'end_date' => '2026-05-31', 'scope_of_work' => 'Structural steel erection and concrete frame for G+4 mall building.', 'contract_value' => 8900000, 'retention_percent' => 5, 'status' => 'active'],
            ['sub_code' => 'SUB-005', 'project_idx' => 2, 'contract_number' => 'CON-2024-005', 'start_date' => '2025-01-01', 'end_date' => '2025-03-31', 'scope_of_work' => 'Polymer modified waterproofing system application on bridge deck and piers.', 'contract_value' => 850000, 'retention_percent' => 10, 'status' => 'completed'],
        ];

        $contractMap = [];
        foreach ($contracts as $c) {
            $sid = $subIds[$c['sub_code']] ?? null;
            $pid = $projectIds[$c['project_idx']] ?? null;
            if (!$sid || !$pid) continue;

            $existing = DB::table('subcontractor_contracts')->where('contract_number', $c['contract_number'])->first();
            if ($existing) {
                $contractMap[$c['contract_number']] = ['cid' => $existing->id, 'sid' => $sid];
                continue;
            }

            $cid = DB::table('subcontractor_contracts')->insertGetId([
                'company_id'        => $this->company->id,
                'project_id'        => $pid,
                'subcontractor_id'  => $sid,
                'contract_number'   => $c['contract_number'],
                'start_date'        => $c['start_date'],
                'end_date'          => $c['end_date'],
                'scope_of_work'     => $c['scope_of_work'],
                'contract_value'    => $c['contract_value'],
                'payment_terms'     => 'Monthly RA Bills based on certified work done.',
                'retention_percent' => $c['retention_percent'],
                'status'            => $c['status'],
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
            $contractMap[$c['contract_number']] = ['cid' => $cid, 'sid' => $sid];
        }

        // Bills
        $bills = [
            ['contract' => 'CON-2025-001', 'bill_number' => 'BILL-2025-001', 'bill_date' => '2025-11-30', 'description' => 'RA Bill #1 – MEP rough-in works basement & GF completed.', 'gross_amount' => 650000, 'retention_amount' => 32500, 'tax_deducted' => 13000, 'net_payable' => 604500, 'paid_amount' => 604500, 'status' => 'paid',           'payment_date' => '2025-12-15', 'payment_reference' => 'NEFT/TXN-12345'],
            ['contract' => 'CON-2025-001', 'bill_number' => 'BILL-2025-002', 'bill_date' => '2026-01-31', 'description' => 'RA Bill #2 – MEP works 1F to 3F rough-in.', 'gross_amount' => 420000, 'retention_amount' => 21000, 'tax_deducted' => 8400,  'net_payable' => 390600, 'paid_amount' => 0,      'status' => 'approved',       'payment_date' => null, 'payment_reference' => null],
            ['contract' => 'CON-2025-002', 'bill_number' => 'BILL-2025-003', 'bill_date' => '2025-06-30', 'description' => 'RA Bill #1 – Foundation & basement RCC work.', 'gross_amount' => 1200000,'retention_amount' => 60000, 'tax_deducted' => 24000, 'net_payable' => 1116000,'paid_amount' => 1116000,'status' => 'paid',           'payment_date' => '2025-07-15', 'payment_reference' => 'NEFT/TXN-23456'],
            ['contract' => 'CON-2025-002', 'bill_number' => 'BILL-2025-004', 'bill_date' => '2025-10-31', 'description' => 'RA Bill #2 – GF & 1F structural works.', 'gross_amount' => 950000, 'retention_amount' => 47500, 'tax_deducted' => 19000, 'net_payable' => 883500, 'paid_amount' => 500000, 'status' => 'partially_paid', 'payment_date' => '2025-11-10', 'payment_reference' => 'NEFT/TXN-34567'],
            ['contract' => 'CON-2025-002', 'bill_number' => 'BILL-2025-005', 'bill_date' => '2026-02-28', 'description' => 'RA Bill #3 – 2F & 3F structural progress.', 'gross_amount' => 780000, 'retention_amount' => 39000, 'tax_deducted' => 15600, 'net_payable' => 725400, 'paid_amount' => 0,      'status' => 'pending',        'payment_date' => null, 'payment_reference' => null],
            ['contract' => 'CON-2024-005', 'bill_number' => 'BILL-2024-006', 'bill_date' => '2025-03-31', 'description' => 'Final Bill – Waterproofing works completed & accepted.', 'gross_amount' => 850000,'retention_amount' => 85000, 'tax_deducted' => 17000, 'net_payable' => 748000, 'paid_amount' => 748000, 'status' => 'paid',           'payment_date' => '2025-04-10', 'payment_reference' => 'NEFT/TXN-45678'],
        ];

        foreach ($bills as $b) {
            if (!isset($contractMap[$b['contract']])) continue;
            $cid = $contractMap[$b['contract']]['cid'];
            $sid = $contractMap[$b['contract']]['sid'];

            DB::table('subcontractor_bills')->insertOrIgnore(array_merge(
                array_diff_key($b, array_flip(['contract'])),
                [
                    'company_id'               => $this->company->id,
                    'subcontractor_contract_id'=> $cid,
                    'subcontractor_id'         => $sid,
                    'other_deductions'         => 0,
                    'approved_by'              => in_array($b['status'], ['approved','paid','partially_paid']) ? $this->admin->id : null,
                    'created_at'               => now(),
                    'updated_at'               => now(),
                ]
            ));
        }
    }

    // -------------------------------------------------------------------------
    private function createExpenseCategoriesAndExpenses(array $projectIds): void
    {
        $categories = [
            ['name' => 'Site Labour',       'type' => 'labor'],
            ['name' => 'Material Purchase', 'type' => 'material'],
            ['name' => 'Equipment Rental',  'type' => 'equipment'],
            ['name' => 'Site Overheads',    'type' => 'overhead'],
            ['name' => 'Transport & Fuel',  'type' => 'operational'],
            ['name' => 'Office & Admin',    'type' => 'overhead'],
            ['name' => 'Safety & PPE',      'type' => 'overhead'],
        ];

        $catIds = [];
        foreach ($categories as $cat) {
            $slug = Str::slug($cat['name']);
            $existing = DB::table('expense_categories')->where('company_id', $this->company->id)->where('slug', $slug)->first();
            if ($existing) {
                $catIds[$cat['name']] = $existing->id;
            } else {
                $catIds[$cat['name']] = DB::table('expense_categories')->insertGetId([
                    'company_id' => $this->company->id,
                    'name'       => $cat['name'],
                    'slug'       => $slug,
                    'type'       => $cat['type'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $pid1 = $projectIds[0];
        $pid2 = $projectIds[1];
        $pid3 = $projectIds[2];

        $expenses = [
            // Project 1
            ['project_id' => $pid1, 'category' => 'Site Labour',       'description' => 'Labour wages – January 2025',            'amount' => 285000, 'expense_date' => '2025-01-31', 'payment_mode' => 'bank',   'reference_number' => 'NEFT-JAN-001', 'status' => 'approved'],
            ['project_id' => $pid1, 'category' => 'Material Purchase', 'description' => 'Cement bags – 500 bags OPC 53',          'amount' => 210000, 'expense_date' => '2025-02-10', 'payment_mode' => 'cheque', 'reference_number' => 'CHQ-001245',   'status' => 'approved'],
            ['project_id' => $pid1, 'category' => 'Equipment Rental',  'description' => 'Tower Crane monthly hire Jan-Feb',        'amount' => 360000, 'expense_date' => '2025-02-28', 'payment_mode' => 'bank',   'reference_number' => 'NEFT-CRANE-001','status' => 'approved'],
            ['project_id' => $pid1, 'category' => 'Site Labour',       'description' => 'Labour wages – February 2025',           'amount' => 310000, 'expense_date' => '2025-02-28', 'payment_mode' => 'bank',   'reference_number' => 'NEFT-FEB-001', 'status' => 'approved'],
            ['project_id' => $pid1, 'category' => 'Transport & Fuel',  'description' => 'Diesel for site vehicles – Feb',         'amount' => 42000,  'expense_date' => '2025-02-28', 'payment_mode' => 'cash',   'reference_number' => null,            'status' => 'approved'],
            ['project_id' => $pid1, 'category' => 'Material Purchase', 'description' => 'TMT Steel 12mm & 16mm – 8 MT',           'amount' => 576000, 'expense_date' => '2025-03-15', 'payment_mode' => 'bank',   'reference_number' => 'NEFT-STL-001', 'status' => 'approved'],
            ['project_id' => $pid1, 'category' => 'Safety & PPE',      'description' => 'Safety helmets, harness, gloves – 50 sets','amount' => 38500, 'expense_date' => '2025-03-20', 'payment_mode' => 'cash',   'reference_number' => null,            'status' => 'approved'],
            ['project_id' => $pid1, 'category' => 'Site Labour',       'description' => 'Labour wages – March 2025',              'amount' => 295000, 'expense_date' => '2025-03-31', 'payment_mode' => 'bank',   'reference_number' => 'NEFT-MAR-001', 'status' => 'approved'],
            ['project_id' => $pid1, 'category' => 'Material Purchase', 'description' => 'River sand & aggregate – 200 cft each',  'amount' => 24000,  'expense_date' => '2025-04-05', 'payment_mode' => 'cash',   'reference_number' => null,            'status' => 'approved'],
            ['project_id' => $pid1, 'category' => 'Site Overheads',    'description' => 'Site office rental & utilities – Q1',    'amount' => 45000,  'expense_date' => '2025-03-31', 'payment_mode' => 'bank',   'reference_number' => 'NEFT-RENT-001','status' => 'approved'],
            ['project_id' => $pid1, 'category' => 'Material Purchase', 'description' => 'Shuttering plates & props – 200 nos',    'amount' => 180000, 'expense_date' => '2025-05-12', 'payment_mode' => 'cheque', 'reference_number' => 'CHQ-002340',   'status' => 'approved'],
            ['project_id' => $pid1, 'category' => 'Transport & Fuel',  'description' => 'Transit mixer fuel & maintenance',       'amount' => 55000,  'expense_date' => '2025-06-30', 'payment_mode' => 'cash',   'reference_number' => null,            'status' => 'approved'],
            ['project_id' => $pid1, 'category' => 'Site Labour',       'description' => 'Overtime wages – Festival season',       'amount' => 68000,  'expense_date' => now()->subDays(5)->format('Y-m-d'), 'payment_mode' => 'cash', 'reference_number' => null, 'status' => 'pending'],
            ['project_id' => $pid1, 'category' => 'Material Purchase', 'description' => 'Admixture & binding wire – latest batch', 'amount' => 32000,  'expense_date' => now()->subDays(2)->format('Y-m-d'), 'payment_mode' => 'upi',  'reference_number' => 'UPI-' . rand(10000, 99999), 'status' => 'pending'],
            // Project 2
            ['project_id' => $pid2, 'category' => 'Site Labour',       'description' => 'Skilled labour wages – April 2025',      'amount' => 420000, 'expense_date' => '2025-04-30', 'payment_mode' => 'bank',   'reference_number' => 'NEFT-MM-APR',  'status' => 'approved'],
            ['project_id' => $pid2, 'category' => 'Material Purchase', 'description' => 'Cement OPC – 1200 bags',                 'amount' => 504000, 'expense_date' => '2025-05-15', 'payment_mode' => 'cheque', 'reference_number' => 'CHQ-003456',   'status' => 'approved'],
            ['project_id' => $pid2, 'category' => 'Equipment Rental',  'description' => 'Concrete pump hire – 2 months',          'amount' => 330000, 'expense_date' => '2025-06-30', 'payment_mode' => 'bank',   'reference_number' => 'NEFT-PUMP-001','status' => 'approved'],
            ['project_id' => $pid2, 'category' => 'Site Overheads',    'description' => 'Temporary electrification & water',      'amount' => 85000,  'expense_date' => '2025-07-31', 'payment_mode' => 'bank',   'reference_number' => 'NEFT-OHD-001', 'status' => 'approved'],
            ['project_id' => $pid2, 'category' => 'Material Purchase', 'description' => 'Structural steel – 50 MT',              'amount' => 4250000,'expense_date' => '2025-08-10', 'payment_mode' => 'bank',   'reference_number' => 'NEFT-STL-002', 'status' => 'approved'],
            ['project_id' => $pid2, 'category' => 'Transport & Fuel',  'description' => 'Heavy transport & material hauling',     'amount' => 120000, 'expense_date' => now()->subDays(7)->format('Y-m-d'), 'payment_mode' => 'bank', 'reference_number' => 'NEFT-TRNSP-002', 'status' => 'pending'],
            // Project 3 (completed)
            ['project_id' => $pid3, 'category' => 'Material Purchase', 'description' => 'Polymer waterproofing compound',         'amount' => 285000, 'expense_date' => '2024-12-15', 'payment_mode' => 'bank',   'reference_number' => 'NEFT-WP-001',  'status' => 'approved'],
            ['project_id' => $pid3, 'category' => 'Site Labour',       'description' => 'Skilled applicators – 3 months',         'amount' => 195000, 'expense_date' => '2025-01-31', 'payment_mode' => 'bank',   'reference_number' => 'NEFT-LAB-003', 'status' => 'approved'],
            ['project_id' => $pid3, 'category' => 'Transport & Fuel',  'description' => 'Site transport – bridge location',       'amount' => 35000,  'expense_date' => '2025-02-28', 'payment_mode' => 'cash',   'reference_number' => null,            'status' => 'approved'],
            ['project_id' => $pid3, 'category' => 'Site Overheads',    'description' => 'Scaffolding erection & dismantling',     'amount' => 110000, 'expense_date' => '2025-03-15', 'payment_mode' => 'cheque', 'reference_number' => 'CHQ-004567',   'status' => 'approved'],
        ];

        foreach ($expenses as $e) {
            $catId = $catIds[$e['category']] ?? null;
            if (!$catId) continue;

            DB::table('expenses')->insertOrIgnore([
                'company_id'          => $this->company->id,
                'project_id'          => $e['project_id'],
                'expense_category_id' => $catId,
                'description'         => $e['description'],
                'amount'              => $e['amount'],
                'expense_date'        => $e['expense_date'],
                'payment_mode'        => $e['payment_mode'],
                'reference_number'    => $e['reference_number'],
                'status'              => $e['status'],
                'created_by'          => $this->accountant->id,
                'approved_by'         => $e['status'] === 'approved' ? $this->admin->id : null,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }
    }

    // -------------------------------------------------------------------------
    private function createProcurement(array $projectIds): void
    {
        $matIds  = DB::table('materials')->where('company_id', $this->company->id)->pluck('id', 'code');
        $vendIds = DB::table('vendors')->where('company_id', $this->company->id)->pluck('id', 'code');

        if ($matIds->isEmpty() || $vendIds->isEmpty()) return;

        $pid1 = $projectIds[0];
        $pid2 = $projectIds[1];

        // Indents
        $indents = [
            ['project_id' => $pid1, 'indent_number' => 'IND-2025-001', 'indent_date' => '2025-01-10', 'required_by_date' => '2025-01-20', 'status' => 'ordered',    'remarks' => 'Urgent – Foundation works starting.', 'items' => [['mat' => 'MAT-001', 'qty' => 1000, 'unit' => 'bag'], ['mat' => 'MAT-002', 'qty' => 5000, 'unit' => 'cft'], ['mat' => 'MAT-003', 'qty' => 3000, 'unit' => 'cft']]],
            ['project_id' => $pid1, 'indent_number' => 'IND-2025-002', 'indent_date' => '2025-02-05', 'required_by_date' => '2025-02-15', 'status' => 'approved',   'remarks' => 'Steel for columns floor 1-3.',         'items' => [['mat' => 'MAT-004', 'qty' => 5000, 'unit' => 'kg'],  ['mat' => 'MAT-005', 'qty' => 3000, 'unit' => 'kg'],  ['mat' => 'MAT-015', 'qty' => 200,  'unit' => 'kg']]],
            ['project_id' => $pid1, 'indent_number' => 'IND-2025-003', 'indent_date' => now()->subDays(3)->format('Y-m-d'), 'required_by_date' => now()->addDays(7)->format('Y-m-d'), 'status' => 'submitted', 'remarks' => 'Masonry material for upper floors.', 'items' => [['mat' => 'MAT-006', 'qty' => 5000, 'unit' => 'nos'], ['mat' => 'MAT-007', 'qty' => 2000, 'unit' => 'nos']]],
            ['project_id' => $pid2, 'indent_number' => 'IND-2025-004', 'indent_date' => '2025-04-01', 'required_by_date' => '2025-04-15', 'status' => 'ordered',    'remarks' => 'Foundation and structural materials.', 'items' => [['mat' => 'MAT-001', 'qty' => 2000, 'unit' => 'bag'], ['mat' => 'MAT-004', 'qty' => 10000,'unit' => 'kg']]],
            ['project_id' => $pid1, 'indent_number' => 'IND-2025-005', 'indent_date' => now()->subDays(1)->format('Y-m-d'), 'required_by_date' => now()->addDays(5)->format('Y-m-d'), 'status' => 'draft',     'remarks' => 'Admixture and shuttering materials.', 'items' => [['mat' => 'MAT-013', 'qty' => 200,  'unit' => 'ltr'], ['mat' => 'MAT-014', 'qty' => 100,  'unit' => 'ltr']]],
        ];

        $indentIdMap = [];
        foreach ($indents as $ind) {
            $existing = DB::table('indents')->where('indent_number', $ind['indent_number'])->first();
            if ($existing) {
                $indentIdMap[$ind['indent_number']] = $existing->id;
                continue;
            }
            $iid = DB::table('indents')->insertGetId([
                'company_id'       => $this->company->id,
                'project_id'       => $ind['project_id'],
                'indent_number'    => $ind['indent_number'],
                'indent_date'      => $ind['indent_date'],
                'required_by_date' => $ind['required_by_date'],
                'status'           => $ind['status'],
                'requested_by'     => $this->engineer->id,
                'approved_by'      => in_array($ind['status'], ['approved','ordered']) ? $this->pm->id : null,
                'approved_at'      => in_array($ind['status'], ['approved','ordered']) ? now() : null,
                'remarks'          => $ind['remarks'],
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
            $indentIdMap[$ind['indent_number']] = $iid;

            foreach ($ind['items'] as $item) {
                $mid = $matIds[$item['mat']] ?? null;
                if (!$mid) continue;
                DB::table('indent_items')->insert([
                    'indent_id'   => $iid,
                    'material_id' => $mid,
                    'quantity'    => $item['qty'],
                    'unit'        => $item['unit'],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }

        // Purchase Orders linked to ordered indents
        $poData = [
            [
                'indent' => 'IND-2025-001', 'vendor' => 'VND-003', 'project_id' => $pid1,
                'po_number' => 'PO-2025-001', 'po_date' => '2025-01-12', 'delivery_date' => '2025-01-20',
                'status' => 'received',
                'items' => [
                    ['mat' => 'MAT-001', 'qty' => 1000, 'unit' => 'bag', 'rate' => 415, 'tax_percent' => 28, 'received_qty' => 1000],
                ],
            ],
            [
                'indent' => 'IND-2025-001', 'vendor' => 'VND-001', 'project_id' => $pid1,
                'po_number' => 'PO-2025-002', 'po_date' => '2025-01-13', 'delivery_date' => '2025-01-22',
                'status' => 'received',
                'items' => [
                    ['mat' => 'MAT-002', 'qty' => 5000, 'unit' => 'cft', 'rate' => 52, 'tax_percent' => 5, 'received_qty' => 5000],
                    ['mat' => 'MAT-003', 'qty' => 3000, 'unit' => 'cft', 'rate' => 62, 'tax_percent' => 5, 'received_qty' => 3000],
                ],
            ],
            [
                'indent' => 'IND-2025-002', 'vendor' => 'VND-002', 'project_id' => $pid1,
                'po_number' => 'PO-2025-003', 'po_date' => '2025-02-08', 'delivery_date' => '2025-02-16',
                'status' => 'partially_received',
                'items' => [
                    ['mat' => 'MAT-004', 'qty' => 5000, 'unit' => 'kg', 'rate' => 70, 'tax_percent' => 18, 'received_qty' => 3000],
                    ['mat' => 'MAT-005', 'qty' => 3000, 'unit' => 'kg', 'rate' => 68, 'tax_percent' => 18, 'received_qty' => 1500],
                ],
            ],
            [
                'indent' => 'IND-2025-004', 'vendor' => 'VND-003', 'project_id' => $pid2,
                'po_number' => 'PO-2025-004', 'po_date' => '2025-04-05', 'delivery_date' => '2025-04-18',
                'status' => 'received',
                'items' => [
                    ['mat' => 'MAT-001', 'qty' => 2000, 'unit' => 'bag', 'rate' => 418, 'tax_percent' => 28, 'received_qty' => 2000],
                ],
            ],
        ];

        foreach ($poData as $po) {
            $existing = DB::table('purchase_orders')->where('po_number', $po['po_number'])->first();
            if ($existing) continue;

            $indentId = $indentIdMap[$po['indent']] ?? null;
            $vendorId = $vendIds[$po['vendor']] ?? null;
            if (!$vendorId) continue;

            $subtotal = 0;
            $taxTotal = 0;
            foreach ($po['items'] as $item) {
                $subtotal += $item['qty'] * $item['rate'];
                $taxTotal += $item['qty'] * $item['rate'] * $item['tax_percent'] / 100;
            }

            $poid = DB::table('purchase_orders')->insertGetId([
                'company_id'       => $this->company->id,
                'project_id'       => $po['project_id'],
                'vendor_id'        => $vendorId,
                'indent_id'        => $indentId,
                'po_number'        => $po['po_number'],
                'po_date'          => $po['po_date'],
                'delivery_date'    => $po['delivery_date'],
                'delivery_address' => 'Site Office, Project Location',
                'subtotal'         => $subtotal,
                'tax_amount'       => $taxTotal,
                'total_amount'     => $subtotal + $taxTotal,
                'status'           => $po['status'],
                'created_by'       => $this->pm->id,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            foreach ($po['items'] as $item) {
                $mid = $matIds[$item['mat']] ?? null;
                if (!$mid) continue;
                DB::table('purchase_order_items')->insert([
                    'purchase_order_id'  => $poid,
                    'material_id'        => $mid,
                    'quantity'           => $item['qty'],
                    'unit'               => $item['unit'],
                    'rate'               => $item['rate'],
                    'tax_percent'        => $item['tax_percent'],
                    'received_quantity'  => $item['received_qty'],
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }
        }
    }
}
