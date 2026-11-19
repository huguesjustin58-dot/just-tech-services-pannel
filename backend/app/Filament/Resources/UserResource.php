<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Services\Billing\CreditLedgerService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Utilisateurs, Revendeurs & Gigas';
    protected static ?string $navigationGroup = 'Gestion Clients';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identité & Rôle')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')->label('Nom / Enseigne')->required()->maxLength(120),
                    Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('phone')->label('Téléphone')->tel()->maxLength(32),
                    Forms\Components\Select::make('role')
                        ->label('Rôle')
                        ->options(collect(UserRole::cases())->mapWithKeys(fn (UserRole $r) => [$r->value => $r->label()]))
                        ->live()
                        ->required(),
                    Forms\Components\TextInput::make('password')
                        ->label('Mot de passe')
                        ->password()
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->required(fn (string $operation): bool => $operation === 'create'),
                    Forms\Components\Toggle::make('is_active')->label('Compte actif')->default(true),
                ]),

            Forms\Components\Section::make('Contrôle Total Revendeur / Sous-revendeur & Stock Gigas')
                ->columns(2)
                ->visible(fn (Forms\Get $get): bool => in_array(
                    $get('role'),
                    [UserRole::RESELLER->value, UserRole::SUB_RESELLER->value, UserRole::RESELLER, UserRole::SUB_RESELLER],
                    true
                ))
                ->schema([
                    Forms\Components\TextInput::make('gb_balance')
                        ->label('Stock Gigas attribués (Go)')
                        ->numeric()
                        ->minValue(0)
                        ->default(100)
                        ->suffix('Go')
                        ->helperText('Le revendeur/sous-revendeur achète ses forfaits directement avec ce stock de Gigas.')
                        ->required(),
                    Forms\Components\Select::make('parent_reseller_id')
                        ->label('Revendeur Parent (si Sous-revendeur)')
                        ->options(fn () => User::withoutGlobalScopes()
                            ->where('role', UserRole::RESELLER->value)
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),
                    Forms\Components\TextInput::make('client_seat_limit')
                        ->label('Plafond places clients')
                        ->numeric()
                        ->minValue(1)
                        ->default(50),
                    Forms\Components\TextInput::make('commission_bps')
                        ->label('Commission (bps, 1000 = 10%)')
                        ->numeric()
                        ->minValue(0)
                        ->default(1000),
                    Forms\Components\Toggle::make('can_create_sub_resellers')
                        ->label('Autoriser la création de sous-revendeurs')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('role')->label('Rôle')->badge(),
                Tables\Columns\TextColumn::make('gb_balance')
                    ->label('Stock Gigas')
                    ->suffix(' Go')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Actif')->boolean(),
                Tables\Columns\TextColumn::make('last_login_at')->label('Dernière connexion')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('allocate_gb')
                    ->label('Attribuer Go')
                    ->icon('heroicon-o-plus-circle')
                    ->visible(fn (User $record): bool => $record->isResellerTier())
                    ->form([
                        Forms\Components\TextInput::make('gb_amount')
                            ->label('Gigas à ajouter (Go)')
                            ->numeric()
                            ->minValue(1)
                            ->default(100)
                            ->required(),
                    ])
                    ->action(function (User $record, array $data, CreditLedgerService $ledger): void {
                        $admin = auth()->user();
                        if ($admin instanceof User) {
                            $ledger->allocateGb(
                                partner: $record,
                                gbAmount: (int) $data['gb_amount'],
                                idempotencyKey: 'adm-gb-'.Str::uuid(),
                                description: "Attribution Admin (+{$data['gb_amount']} Go)",
                                adminActor: $admin,
                            );
                        }
                    }),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
