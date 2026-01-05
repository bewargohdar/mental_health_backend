<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DoctorAvailabilityResource\Pages;
use App\Models\DoctorAvailability;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DoctorAvailabilityResource extends Resource
{
    protected static ?string $model = DoctorAvailability::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    
    protected static ?string $navigationGroup = 'Appointments';
    
    protected static ?string $navigationLabel = 'Doctor Schedules';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('doctor_id')
                    ->label('Doctor')
                    ->options(
                        User::whereHas('doctorProfile')->pluck('name', 'id')
                    )
                    ->required()
                    ->searchable(),
                
                Forms\Components\DatePicker::make('specific_date')
                    ->label('Specific Date (optional)')
                    ->helperText('Leave empty for recurring weekly schedule'),
                    
                Forms\Components\Select::make('day_of_week')
                    ->label('Day of Week (for recurring)')
                    ->options([
                        0 => 'Sunday',
                        1 => 'Monday',
                        2 => 'Tuesday',
                        3 => 'Wednesday',
                        4 => 'Thursday',
                        5 => 'Friday',
                        6 => 'Saturday',
                    ])
                    ->helperText('Used when no specific date is set'),
                    
                Forms\Components\TimePicker::make('start_time')
                    ->required()
                    ->seconds(false),
                    
                Forms\Components\TimePicker::make('end_time')
                    ->required()
                    ->seconds(false),
                    
                Forms\Components\TextInput::make('slot_duration')
                    ->label('Slot Duration (minutes)')
                    ->numeric()
                    ->default(30)
                    ->required(),
                    
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('doctor.name')
                    ->label('Doctor')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('specific_date')
                    ->label('Date')
                    ->date()
                    ->placeholder('Recurring')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('day_of_week')
                    ->label('Day')
                    ->formatStateUsing(fn ($state) => $days[$state] ?? '-')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('start_time')
                    ->time('H:i'),
                    
                Tables\Columns\TextColumn::make('end_time')
                    ->time('H:i'),
                    
                Tables\Columns\TextColumn::make('slot_duration')
                    ->label('Duration')
                    ->suffix(' min'),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('doctor_id')
                    ->label('Doctor')
                    ->options(User::whereHas('doctorProfile')->pluck('name', 'id')),
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
            ->defaultSort('specific_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDoctorAvailabilities::route('/'),
        ];
    }
}
