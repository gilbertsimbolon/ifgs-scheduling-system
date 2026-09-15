<?php

namespace Database\Seeders;

use App\Models\Trainer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TrainerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'Trainer', 'guard_name' => 'web']);

        $trainers = [
            [
                'name' => 'Coach Mario Rumagit',
                'email' => 'mario.trainer@ifgs.test',
                'phone' => '081234567891',
                'specialization' => 'Fitness & Bodybuilding',
                'bio' => 'Instruktur senior kebugaran dan pembentukan otot bersertifikasi APKI dengan pengalaman 5 tahun.',
                'status' => Trainer::STATUS_ACTIVE,
            ],
            [
                'name' => 'Zin Rini Walangitan',
                'email' => 'rini.trainer@ifgs.test',
                'phone' => '081234567892',
                'specialization' => 'Aerobic & Zumba',
                'bio' => 'Instruktur resmi Zumba & Aerobic berlisensi internasional yang memandu sesi rutin Senin & Kamis.',
                'status' => Trainer::STATUS_ACTIVE,
            ],
        ];

        foreach ($trainers as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'status' => User::STATUS_ACTIVE,
                ]
            );

            if (! $user->hasRole('Trainer')) {
                $user->assignRole('Trainer');
            }

            Trainer::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'phone' => $data['phone'],
                    'specialization' => $data['specialization'],
                    'bio' => $data['bio'],
                    'status' => $data['status'],
                ]
            );
        }
    }
}
