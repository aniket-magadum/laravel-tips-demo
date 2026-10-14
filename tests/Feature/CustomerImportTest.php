<?php

use App\Filament\Imports\CustomerImporter;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\Customer;
use App\Models\User;
use Filament\Actions\ImportAction;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Http\UploadedFile;

use function Pest\Livewire\livewire;

it('imports a customer row', function () {
    $record = CustomerImporter::test()
        ->import([
            'name' => 'Ada Lovelace',
            'email' => 'ada@analytical.example',
            'company' => 'Analytical Engines',
        ])
        ->assertImported()
        ->getRecord();

    $this->assertDatabaseHas(Customer::class, [
        'id' => $record->getKey(),
        'name' => 'Ada Lovelace',
        'email' => 'ada@analytical.example',
        'company' => 'Analytical Engines',
    ]);
});

it('rejects an invalid email', function () {
    CustomerImporter::test()
        ->import([
            'name' => 'Ada Lovelace',
            'email' => 'not-an-email',
            'company' => 'Analytical Engines',
        ])
        ->assertHasErrors(['email' => 'email']);

    $this->assertDatabaseMissing(Customer::class, [
        'email' => 'not-an-email',
    ]);
});

it('skips unknown customers when only updating', function () {
    CustomerImporter::test(options: [
        'updateExisting' => true,
    ])->import([
        'name' => 'Ada Lovelace',
        'email' => 'missing@analytical.example',
        'company' => 'Analytical Engines',
    ])->assertSkipped();

    $this->assertDatabaseMissing(Customer::class, [
        'email' => 'missing@analytical.example',
    ]);
});

it('fails a row from a blocked email domain', function () {
    CustomerImporter::test()
        ->import([
            'name' => 'Ada Lovelace',
            'email' => 'ada@blocked.test',
            'company' => 'Analytical Engines',
        ])
        ->assertHasRowFailure('Email domain is blocked [ada@blocked.test].');

    $this->assertDatabaseMissing(Customer::class, [
        'email' => 'ada@blocked.test',
    ]);
});

it('requests a customer import from the action', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $imports = ImportAction::fake();

    livewire(ListCustomers::class)
        ->mountAction('import')
        ->fillForm([
            'file' => UploadedFile::fake()->createWithContent(
                'customers.csv',
                "name,email,company\nAda Lovelace,ada@analytical.example,Analytical Engines\n",
            ),
        ])
        ->fillForm([
            'columnMap' => [
                'name' => 'name',
                'email' => 'email',
                'company' => 'company',
            ],
            'updateExisting' => true,
        ])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    $imports->assertDispatched(
        CustomerImporter::class,
        function (Import $import, array $columnMap, array $options) use ($user): bool {
            return $import->user->is($user)
                && $columnMap['email'] === 'email'
                && $options['updateExisting'] === true;
        },
    );
});

it('does not dispatch an import when a required column is unmapped', function () {
    $this->actingAs(User::factory()->create());

    $imports = ImportAction::fake();

    livewire(ListCustomers::class)
        ->mountAction('import')
        ->fillForm([
            'file' => UploadedFile::fake()->createWithContent(
                'customers.csv',
                "name,email,company\nAda Lovelace,ada@analytical.example,Analytical Engines\n",
            ),
        ])
        ->fillForm([
            'columnMap' => [
                'name' => 'name',
                'email' => null,
                'company' => 'company',
            ],
        ])
        ->callMountedAction()
        ->assertHasFormErrors(['columnMap.email' => 'required']);

    $imports->assertNothingDispatched();
});
