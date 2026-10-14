<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if (request()->missing('relation')) {
            $this->activeRelationManager = 'status';
        }
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getTabComponents(): array
    {
        return [
            'status' => Tab::make('Status')
                ->icon(Heroicon::OutlinedSignal)
                ->badge(fn (): string => $this->getRecord()->status->getLabel())
                ->badgeColor(fn (): string => $this->getRecord()->status->getColor())
                ->schema([
                    Text::make('Account standing')
                        ->color('gray')
                        ->weight(FontWeight::Medium),
                    Text::make(fn (): string => $this->getRecord()->status->getLabel())
                        ->badge()
                        ->color(fn (): string => $this->getRecord()->status->getColor()),
                    Text::make(fn (): string => (string) $this->getRecord()->company),
                    Text::make(fn (): string => (string) $this->getRecord()->email)
                        ->color('gray'),
                ]),
            'notes' => $this->getRelationManagerTabComponent('notes'),
            '' => $this->getContentTabComponent(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
