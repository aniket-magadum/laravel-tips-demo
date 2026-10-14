<?php

use App\Enums\CustomerStatus;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Models\Customer;
use App\Models\Note;
use App\Models\User;

use function Pest\Livewire\livewire;

it('mixes a status tab between the form and the notes manager', function () {
    $customer = Customer::factory()
        ->has(Note::factory()->count(2))
        ->create([
            'name' => 'Katherine Johnson',
            'email' => 'katherine@nasa.example',
            'status' => CustomerStatus::Churned,
            'company' => 'NASA',
        ]);

    $this->actingAs(User::factory()->create());

    $page = livewire(EditCustomer::class, ['record' => $customer->getKey()])
        ->assertOk()
        ->assertSee('Status')
        ->assertSee('Notes')
        ->assertSee('Edit')
        ->assertSee('Account standing')
        ->assertSee('Churned')
        ->assertSee('NASA')
        ->assertSee('katherine@nasa.example');

    $tabs = $page->instance()->getTabComponents();

    expect(array_keys($tabs))->toBe(['status', 'notes', '']);
    expect($tabs['notes'])->not->toBeNull();
    expect($tabs['notes']->getBadge())->toBe('2');
    expect($tabs['status']->getBadge())->toBe('Churned');
    expect($tabs['status']->getBadgeColor())->toBe('danger');
    expect($page->instance()->activeRelationManager)->toBe('status');
});
