<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Vendas';
    protected static ?string $navigationLabel = 'Clientes';
    protected static ?string $modelLabel      = 'Cliente';
    protected static ?string $pluralModelLabel = 'Clientes';
    protected static ?int $navigationSort     = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Tipo de cadastro')
                ->schema([
                    Forms\Components\Radio::make('type')
                        ->label('Tipo de pessoa')
                        ->options(['pf' => 'Pessoa Física', 'pj' => 'Pessoa Jurídica'])
                        ->default('pf')
                        ->inline()
                        ->required()
                        ->live(),
                ]),

            Forms\Components\Section::make('Dados da Pessoa Física')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome completo')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('cpf')
                        ->label('CPF')
                        ->mask('999.999.999-99')
                        ->placeholder('000.000.000-00')
                        ->maxLength(14)
                        ->unique(ignoreRecord: true),
                    Forms\Components\DatePicker::make('birth_date')
                        ->label('Data de nascimento')
                        ->maxDate(now()->subYears(18)),
                ])
                ->visible(fn (Get $get) => $get('type') === 'pf' || ! $get('type'))
                ->columns(3),

            Forms\Components\Section::make('Dados da Pessoa Jurídica')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome do responsável')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('company_name')
                        ->label('Razão Social')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('cnpj')
                        ->label('CNPJ')
                        ->mask('99.999.999/9999-99')
                        ->placeholder('00.000.000/0000-00')
                        ->maxLength(18)
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('state_registration')
                        ->label('Inscrição Estadual')
                        ->maxLength(30),
                    Forms\Components\TextInput::make('responsible_name')
                        ->label('Nome do responsável legal')
                        ->maxLength(255),
                ])
                ->visible(fn (Get $get) => $get('type') === 'pj')
                ->columns(2),

            Forms\Components\Section::make('Contato e Acesso')
                ->schema([
                    Forms\Components\TextInput::make('email')
                        ->label('E-mail')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Forms\Components\TextInput::make('phone')
                        ->label('Telefone')
                        ->tel()
                        ->maxLength(20),
                    Forms\Components\TextInput::make('mobile')
                        ->label('Celular')
                        ->tel()
                        ->maxLength(20),
                    Forms\Components\TextInput::make('password')
                        ->label('Senha')
                        ->password()
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $operation) => $operation === 'create')
                        ->maxLength(255)
                        ->helperText('Deixe em branco para não alterar a senha.'),
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(['active' => 'Ativo', 'inactive' => 'Inativo'])
                        ->default('active')
                        ->required(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'pf' ? 'PF' : 'PJ')
                    ->color(fn ($state) => $state === 'pf' ? 'info' : 'warning'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->description(fn (Customer $record) => $record->company_name)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),

                Tables\Columns\TextColumn::make('mobile')
                    ->label('Celular')
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state === 'active' ? 'Ativo' : 'Inativo')
                    ->colors([
                        'success' => 'active',
                        'danger'  => 'inactive',
                    ]),

                Tables\Columns\TextColumn::make('addresses_count')
                    ->label('Endereços')
                    ->counts('addresses')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Cadastro')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(['pf' => 'Pessoa Física', 'pj' => 'Pessoa Jurídica']),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(['active' => 'Ativo', 'inactive' => 'Inativo']),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AddressesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit'   => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
