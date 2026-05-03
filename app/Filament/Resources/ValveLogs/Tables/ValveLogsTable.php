<?php

namespace App\Filament\Resources\ValveLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ValveLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('apartment.number')
                    ->label('Apto')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Morador (Usuário)')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.roles.name')
                    ->label('Cargo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => \App\Enums\RoleEnum::tryFrom($state)?->label() ?? $state),
                TextColumn::make('action')
                    ->label('Ação')
                    ->badge()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->timezone('America/Sao_Paulo')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->timezone('America/Sao_Paulo')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\ViewAction::make(),
                    \Filament\Actions\EditAction::make(),
                    \Filament\Actions\DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
