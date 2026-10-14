<?php

namespace App\Filament\Imports;

use App\Models\Customer;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Checkbox;
use Illuminate\Support\Number;

class CustomerImporter extends Importer
{
    protected static ?string $model = Customer::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('Ada Lovelace'),
            ImportColumn::make('email')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255'])
                ->example('ada@analytical.example'),
            ImportColumn::make('company')
                ->rules(['max:255'])
                ->example('Analytical Engines'),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Checkbox::make('updateExisting')
                ->label('Update existing customers only'),
        ];
    }

    public function resolveRecord(): ?Customer
    {
        $email = $this->data['email'] ?? null;

        if (filled($email)) {
            $existing = Customer::query()->where('email', $email)->first();

            if ($existing) {
                return $existing;
            }

            if ($this->options['updateExisting'] ?? false) {
                return null;
            }
        }

        return new Customer;
    }

    public function beforeValidate(): void
    {
        $email = strtolower((string) ($this->data['email'] ?? ''));

        if (str_ends_with($email, '@blocked.test')) {
            throw new RowImportFailedException("Email domain is blocked [{$email}].");
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your customer import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
