<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Aniket',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $customers = [
            ['Ada Lovelace', 'ada@analytical.example', 'Analytical Engines'],
            ['Grace Hopper', 'grace@navy.example', 'US Navy'],
            ['Katherine Johnson', 'katherine@nasa.example', 'NASA'],
            ['Margaret Hamilton', 'margaret@mit.example', 'MIT Instrumentation Laboratory'],
            ['Barbara Liskov', 'barbara@mit.example', 'MIT'],
            ['Radia Perlman', 'radia@digital.example', 'Digital Equipment'],
            ['Frances Allen', 'frances@ibm.example', 'IBM'],
            ['Sophie Wilson', 'sophie@acorn.example', 'Acorn Computers'],
        ];

        foreach ($customers as [$name, $email, $company]) {
            Customer::factory()->create([
                'name' => $name,
                'email' => $email,
                'company' => $company,
            ]);
        }
    }
}
