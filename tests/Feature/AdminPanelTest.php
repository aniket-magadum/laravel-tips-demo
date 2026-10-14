<?php

use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\Customer;
use App\Models\User;

use function Pest\Livewire\livewire;

it('shows the admin login screen', function () {
    $this->get('/admin/login')->assertOk();
});

it('lists customers for an admin', function () {
    $this->actingAs(User::factory()->create());

    $customer = Customer::factory()->create([
        'name' => 'Ada Lovelace',
        'email' => 'ada@analytical.example',
        'company' => 'Analytical Engines',
    ]);

    livewire(ListCustomers::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$customer]);
});
