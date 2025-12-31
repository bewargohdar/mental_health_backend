<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DoctorProfileResource\Pages;
use App\Models\DoctorProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DoctorProfileResource extends Resource
{
    protected static ?string $model = DoctorProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $navigationLabel = 'Doctors';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Doctor Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('specialization')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('license_number')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('experience_years')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(50),
                        Forms\Components\TextInput::make('hourly_rate')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0),
                        Forms\Components\Textarea::make('bio')
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Consultation Settings')
                    ->schema([
                        Forms\Components\TagsInput::make('languages')
                            ->placeholder('Add language'),
                        Forms\Components\CheckboxList::make('consultation_types')
                            ->options([
                                'video' => 'Video Call',
                                'chat' => 'Chat',
                                'in_person' => 'In Person',
                            ])
                            ->columns(3),
                    ]),
                Forms\Components\Section::make('Verification')
                    ->schema([
                        Forms\Components\Toggle::make('is_verified')
                            ->label('Verified Doctor'),
                        Forms\Components\DateTimePicker::make('verified_at')
                            ->visible(fn ($get) => $get('is_verified')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Doctor Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('specialization')
                    ->searchable(),
                Tables\Columns\TextColumn::make('license_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('experience_years')
                    ->suffix(' years')
                    ->sortable(),
                Tables\Columns\TextColumn::make('hourly_rate')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_verified')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_verified'),
                Tables\Filters\SelectFilter::make('specialization')
                    ->options(fn () => DoctorProfile::distinct()->pluck('specialization', 'specialization')->toArray()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('verify')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => !$record->is_verified)
                    ->action(fn ($record) => $record->update([
                        'is_verified' => true,
                        'verified_at' => now(),
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDoctorProfiles::route('/'),
            'create' => Pages\CreateDoctorProfile::route('/create'),
            'view' => Pages\ViewDoctorProfile::route('/{record}'),
            'edit' => Pages\EditDoctorProfile::route('/{record}/edit'),
        ];
    }
}
