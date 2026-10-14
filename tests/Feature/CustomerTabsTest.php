<?php

use App\Enums\CustomerStatus;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Models\Customer;
use App\Models\Note;
use App\Models\User;

use function Pest\Livewire\livewire;

it('combines the edit form with the notes relation manager', function () {
    $customer = Customer::factory()
        ->has(Note::factory()->count(2))
        ->create(['status' => CustomerStatus::Active]);

    $this->actingAs(User::factory()->create());

    $page = livewire(EditCustomer::class, ['record' => $customer->getKey()])
        ->assertOk()
        ->assertSee('Edit')
        ->assertSee('Notes')
        ->assertDontSee('Account standing');

    expect(array_keys($page->instance()->getTabComponents()))->toBe(['', 'notes']);
    expect($page->instance()->getTabComponents()['notes']->getBadge())->toBe('2');
});
