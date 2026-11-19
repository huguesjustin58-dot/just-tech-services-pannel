<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PlanResource\Pages;
use App\Models\Plan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Forfaits & Tarifs';
    protected static ?string $navigationGroup = 'Catalogue & VPN';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('name')->required()->maxLength(120),
            Forms\Components\TextInput::make('price_fcfa')->numeric()->required(),
            Forms\Components\TextInput::make('reseller_cost_fcfa')->numeric()->required(),
            Forms\Components\TextInput::make('duration_days')->numeric()->required(),
            Forms\Components\TextInput::make('quota_gb')->numeric()->required(),
            Forms\Components\TextInput::make('max_concurrent_sessions')->numeric()->default(1)->required(),
            Forms\Components\Toggle::make('is_vip'),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('price_fcfa')->money('XAF')->sortable(),
                Tables\Columns\TextColumn::make('duration_days')->label('Jours'),
                Tables\Columns\TextColumn::make('quota_gb')->label('Go (0=Illimité)'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
