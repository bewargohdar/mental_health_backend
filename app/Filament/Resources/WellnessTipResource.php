<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WellnessTipResource\Pages;
use App\Models\WellnessTip;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WellnessTipResource extends Resource
{
    protected static ?string $model = WellnessTip::class;

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Wellness Tips';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tip Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('content')
                            ->required()
                            ->maxLength(1000)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('icon')
                            ->label('Icon/Emoji')
                            ->placeholder('💡')
                            ->maxLength(10),
                        Forms\Components\Select::make('category')
                            ->options([
                                'mindfulness' => 'Mindfulness',
                                'sleep' => 'Sleep',
                                'exercise' => 'Exercise',
                                'nutrition' => 'Nutrition',
                                'social' => 'Social Connection',
                                'stress' => 'Stress Management',
                                'general' => 'General Wellness',
                            ]),
                        Forms\Components\Select::make('language')
                            ->options([
                                'en' => 'English',
                                'ar' => 'العربية (Arabic)',
                                'ku' => 'کوردی (Kurdish)',
                            ])
                            ->required()
                            ->default('en'),
                        Forms\Components\TextInput::make('display_order')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Select::make('author_id')
                            ->relationship('author', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('icon')
                    ->label(''),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('category')
                    ->badge(),
                Tables\Columns\TextColumn::make('language')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'en' => 'info',
                        'ar' => 'success',
                        'ku' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('display_order')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('display_order')
            ->filters([
                Tables\Filters\SelectFilter::make('language')
                    ->options([
                        'en' => 'English',
                        'ar' => 'Arabic',
                        'ku' => 'Kurdish',
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'mindfulness' => 'Mindfulness',
                        'sleep' => 'Sleep',
                        'exercise' => 'Exercise',
                        'nutrition' => 'Nutrition',
                        'social' => 'Social Connection',
                        'stress' => 'Stress Management',
                        'general' => 'General Wellness',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ReplicateAction::make()
                    ->label('Translate')
                    ->beforeReplicaSaved(function ($replica) {
                        $replica->language = 'ku'; // Default to Kurdish for translation
                    }),
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
            'index' => Pages\ListWellnessTips::route('/'),
            'create' => Pages\CreateWellnessTip::route('/create'),
            'view' => Pages\ViewWellnessTip::route('/{record}'),
            'edit' => Pages\EditWellnessTip::route('/{record}/edit'),
        ];
    }
}
