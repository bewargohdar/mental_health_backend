<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Community';

    public static function getNavigationBadge(): ?string
    {
        $count = Post::pending()->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn () => auth()->id()),
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('category')
                            ->options(collect(\App\Enums\ContentCategory::cases())->mapWithKeys(fn ($cat) => [$cat->value => $cat->label()])),
                        Forms\Components\Toggle::make('is_approved')
                            ->label('Approved')
                            ->default(false)
                            ->helperText('Approve this post to make it publicly visible'),
                        Forms\Components\Toggle::make('is_anonymous'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Approval Information')
                    ->schema([
                        Forms\Components\Placeholder::make('approved_at')
                            ->label('Approved At')
                            ->content(fn (?Post $record) => $record?->approved_at?->format('M d, Y H:i') ?? 'Not approved yet'),
                        Forms\Components\Placeholder::make('approver')
                            ->label('Approved By')
                            ->content(fn (?Post $record) => $record?->approver?->name ?? 'N/A'),
                    ])
                    ->columns(2)
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Author')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge(),
                Tables\Columns\TextColumn::make('is_approved')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state) => $state ? 'Approved' : 'Pending')
                    ->color(fn (bool $state) => $state ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Approved At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Approved By')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_anonymous')
                    ->boolean(),
                Tables\Columns\TextColumn::make('likes_count')
                    ->label('Likes'),
                Tables\Columns\TextColumn::make('comments_count')
                    ->label('Comments'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending Approval',
                        'approved' => 'Approved',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return match ($data['value']) {
                            'pending' => $query->where('is_approved', false),
                            'approved' => $query->where('is_approved', true),
                            default => $query,
                        };
                    })
                    ->default('pending'),
                Tables\Filters\SelectFilter::make('category'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->action(fn (Post $record) => $record->update([
                        'is_approved' => true,
                        'approved_at' => now(),
                        'approved_by' => auth()->id(),
                    ]))
                    ->visible(fn (Post $record) => !$record->is_approved)
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Post')
                    ->modalDescription('Are you sure you want to approve this post? It will become publicly visible.'),
                Tables\Actions\Action::make('reject')
                    ->action(fn (Post $record) => $record->delete())
                    ->visible(fn (Post $record) => !$record->is_approved)
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Post')
                    ->modalDescription('Are you sure you want to reject and delete this post? This action cannot be undone.'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update([
                            'is_approved' => true,
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                        ])))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('reject')
                        ->action(fn ($records) => $records->each->delete())
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}

