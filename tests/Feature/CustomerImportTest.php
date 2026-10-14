<?php

use App\Filament\Imports\CustomerImporter;
use App\Models\Customer;
use App\Models\User;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

it('imports customers from an uploaded csv', function () {
    Storage::fake('local');

    $csv = <<<'CSV'
name,email,company
Ada Lovelace,ada@analytical.example,Analytical Engines
Grace Hopper,grace@navy.example,US Navy
CSV;

    $uploaded = UploadedFile::fake()->createWithContent('customers.csv', $csv);
    $path = $uploaded->storeAs('imports', 'customers.csv', 'local');

    $lines = array_map('str_getcsv', preg_split('/\r\n|\n|\r/', trim(Storage::disk('local')->get($path))));
    $headers = array_shift($lines);
    $rows = array_map(fn (array $line): array => array_combine($headers, $line), $lines);

    $user = User::factory()->create();

    $import = Import::query()->create([
        'file_name' => 'customers.csv',
        'file_path' => Storage::disk('local')->path($path),
        'importer' => CustomerImporter::class,
        'total_rows' => count($rows),
        'user_id' => $user->id,
    ]);

    Bus::dispatchSync(new ImportCsv(
        import: $import,
        rows: base64_encode(serialize($rows)),
        columnMap: [
            'name' => 'name',
            'email' => 'email',
            'company' => 'company',
        ],
    ));

    $this->assertDatabaseHas(Customer::class, [
        'name' => 'Ada Lovelace',
        'email' => 'ada@analytical.example',
        'company' => 'Analytical Engines',
    ]);

    $this->assertDatabaseHas(Customer::class, [
        'name' => 'Grace Hopper',
        'email' => 'grace@navy.example',
        'company' => 'US Navy',
    ]);
});
