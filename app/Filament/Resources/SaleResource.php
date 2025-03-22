<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Models\Product;
use App\Models\Sale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Transactions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Sale Information')
                            ->schema([
                                Forms\Components\TextInput::make('invoice_number')
                                    ->default('INV-' . Str::random(8))
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),

                                Forms\Components\Select::make('customer_id')
                                    ->relationship('customer', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required(),
                                        Forms\Components\TextInput::make('email'),
                                        Forms\Components\TextInput::make('phone'),
                                        Forms\Components\Textarea::make('address'),
                                    ])
                                    ->required(),

                                Forms\Components\DatePicker::make('date')
                                    ->default(now())
                                    ->required(),

                                Forms\Components\Select::make('status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'completed' => 'Completed',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->default('pending')
                                    ->required(),

                                Forms\Components\Textarea::make('notes')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('Sale Items')
                            ->schema([
                                Forms\Components\Repeater::make('saleItems')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Select::make('product_id')
                                            ->label('Product')
                                            ->options(function () {
                                                return Product::where('stock', '>', 0)
                                                    ->get()
                                                    ->pluck('name', 'id');
                                            })
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set, $get) {
                                                if ($state) {
                                                    $product = Product::find($state);
                                                    $set('price', $product->selling_price);
                                                    $set('available_stock', $product->stock);
                                                    $quantity = $get('quantity');
                                                    $price = $product->selling_price;
                                                    $set('total', $quantity * $price);
                                                }
                                            }),

                                        Forms\Components\TextInput::make('available_stock')
                                            ->label('Available Stock')
                                            ->disabled()
                                            ->dehydrated(false),

                                        Forms\Components\TextInput::make('quantity')
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set, $get) {
                                                $productId = $get('product_id');
                                                if ($productId) {
                                                    $product = Product::find($productId);
                                                    $availableStock = $product->stock;

                                                    // Validate quantity doesn't exceed available stock
                                                    if ($state > $availableStock) {
                                                        $set('quantity', $availableStock);
                                                        $state = $availableStock;
                                                        Notification::make()
                                                            ->title('Quantity exceeds available stock')
                                                            ->danger()
                                                            ->send();
                                                    }

                                                    $price = $get('price');
                                                    $set('total', $state * $price);
                                                }
                                            }),

                                        Forms\Components\TextInput::make('quantity')
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set, $get) {
                                                $productId = $get('product_id');
                                                if ($productId) {
                                                    $price = $get('price');
                                                    $set('total', $state * $price);
                                                }
                                            }),

                                        Forms\Components\TextInput::make('price')
                                            ->numeric()
                                            ->required()
                                            ->prefix('Rp')
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set, $get) {
                                                $quantity = $get('quantity');
                                                $set('total', $quantity * $state);
                                            }),

                                        Forms\Components\TextInput::make('total')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->disabled(),
                                    ])
                                    ->columns(4)
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        $subtotal = collect($state ?? [])->sum('total');
                                        $set('subtotal', $subtotal);

                                        // Calculate total with tax and discount
                                        $tax = $subtotal * 0.1; // 10% tax
                                        $set('tax', $tax);

                                        $discount = $subtotal * 0.05; // 5% discount
                                        $set('discount', $discount);

                                        $total = $subtotal + $tax - $discount;
                                        $set('total_amount', $total);
                                    })
                                    ->deleteAction(
                                        fn (Forms\Components\Actions\Action $action, Forms\Set $set, array $state) =>
                                            $action->requiresConfirmation()->after(function () use ($set, $state) {
                                                $subtotal = collect($state ?? [])->sum('total');
                                                $set('subtotal', $subtotal);

                                                // Calculate total with tax and discount
                                                $tax = $subtotal * 0.1; // 10% tax
                                                $set('tax', $tax);

                                                $discount = $subtotal * 0.05; // 5% discount
                                                $set('discount', $discount);

                                                $total = $subtotal + $tax - $discount;
                                                $set('total_amount', $total);
                                            }),
                                    ),
                            ]),
                    ])->columnSpanFull(),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('tax')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('10% of subtotal'),

                        Forms\Components\TextInput::make('discount')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('5% of subtotal'),

                        Forms\Components\TextInput::make('total_amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('print')
                    ->label('Print Invoice')
                    ->color('success')
                    ->icon('heroicon-o-printer')
                    ->url(fn (Sale $record): string => route('print.invoice', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
