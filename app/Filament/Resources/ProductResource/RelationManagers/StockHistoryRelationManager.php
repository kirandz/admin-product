<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'stockHistories';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('type')
                    ->options([
                        'initial' => 'Initial',
                        'purchase' => 'Purchase',
                        'sale' => 'Sale',
                        'adjustment' => 'Adjustment',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('before')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('after')
                    ->numeric()
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Date'),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'initial' => 'info',
                        'purchase' => 'success',
                        'sale' => 'danger',
                        'adjustment' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('before')
                    ->numeric(),
                Tables\Columns\TextColumn::make('after')
                    ->numeric(),
                Tables\Columns\TextColumn::make('reference_type')
                    ->label('Source'),
                Tables\Columns\TextColumn::make('notes'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'initial' => 'Initial',
                        'purchase' => 'Purchase',
                        'sale' => 'Sale',
                        'adjustment' => 'Adjustment',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Adjustment')
                    ->mutateFormDataUsing(function (array $data): array {
                        $product = $this->getOwnerRecord();
                        $data['before'] = $product->stock;
                        $data['after'] = $product->stock + $data['quantity'];
                        $data['type'] = 'adjustment';

                        return $data;
                    })
                    ->after(function ($record): void {
                        $product = $this->getOwnerRecord();
                        $product->stock = $record->after;
                        $product->save();
                    }),
            ])
            ->actions([])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }
}
