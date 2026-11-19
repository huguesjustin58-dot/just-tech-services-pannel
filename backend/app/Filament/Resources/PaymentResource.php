<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\PaymentStatus;
use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Paiements Mobile Money';
    protected static ?string $navigationGroup = 'Facturation';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('invoice_number')->disabled(),
            Forms\Components\Select::make('status')
                ->options(collect(PaymentStatus::cases())->mapWithKeys(fn (PaymentStatus $s) => [$s->value => $s->label()]))
                ->required(),
            Forms\Components\TextInput::make('amount_fcfa')->numeric()->disabled(),
            Forms\Components\TextInput::make('external_reference')->disabled(),
            Forms\Components\Textarea::make('rejection_reason')->maxLength(500),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.email')->label('Client')->searchable(),
                Tables\Columns\TextColumn::make('plan.name')->label('Forfait'),
                Tables\Columns\TextColumn::make('amount_fcfa')->money('XAF')->sortable(),
                Tables\Columns\TextColumn::make('method')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('external_reference')->searchable(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
