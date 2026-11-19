<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\LicenseStatus;
use App\Filament\Resources\LicenseResource\Pages;
use App\Models\License;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LicenseResource extends Resource
{
    protected static ?string $model = License::class;
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationLabel = 'Licences & Comptes VPN';
    protected static ?string $navigationGroup = 'Catalogue & VPN';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('vpn_username')->required(),
            Forms\Components\Select::make('status')
                ->options(collect(LicenseStatus::cases())->mapWithKeys(fn (LicenseStatus $s) => [$s->value => $s->value]))
                ->required(),
            Forms\Components\TextInput::make('max_concurrent_sessions')->numeric()->required(),
            Forms\Components\DateTimePicker::make('expires_at')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vpn_username')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.email')->label('Client')->searchable(),
                Tables\Columns\TextColumn::make('plan.name')->label('Forfait'),
                Tables\Columns\TextColumn::make('server.name')->label('Serveur'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('expires_at')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLicenses::route('/'),
            'edit' => Pages\EditLicense::route('/{record}/edit'),
        ];
    }
}
