<?php

namespace Database\Seeders;

use App\Enums\CustomerStatus;
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
            ['Ada Lovelace', 'ada@analytical.example', 'Analytical Engines', CustomerStatus::Active, [
                'Asked for an annual invoice.',
                'Prefers email over phone.',
                'Renewed for 2026.',
            ]],
            ['Grace Hopper', 'grace@navy.example', 'US Navy', CustomerStatus::Trial, [
                'Trial ends next Friday.',
            ]],
            ['Katherine Johnson', 'katherine@nasa.example', 'NASA', CustomerStatus::Churned, [
                'Cancelled after the pilot.',
                'Left the door open for next year.',
            ]],
            ['Margaret Hamilton', 'margaret@mit.example', 'MIT Instrumentation Laboratory', CustomerStatus::Active, []],
            ['Barbara Liskov', 'barbara@mit.example', 'MIT', CustomerStatus::Trial, [
                'Wants a walkthrough of the admin panel.',
            ]],
            ['Radia Perlman', 'radia@digital.example', 'Digital Equipment', CustomerStatus::Active, [
                'Added two extra seats.',
                'Billing contact is finance@digital.example.',
            ]],
            ['Frances Allen', 'frances@ibm.example', 'IBM', CustomerStatus::Churned, [
                'Moved to an internal tool.',
            ]],
            ['Sophie Wilson', 'sophie@acorn.example', 'Acorn Computers', CustomerStatus::Trial, []],
        ];

        foreach ($customers as [$name, $email, $company, $status, $notes]) {
            $customer = Customer::factory()->create([
                'name' => $name,
                'email' => $email,
                'company' => $company,
                'status' => $status,
            ]);

            foreach ($notes as $body) {
                $customer->notes()->create(['body' => $body]);
            }
        }
    }
}
