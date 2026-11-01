
<?php

use Illuminate\Support\Facades\Schema;

test('tabela de permissões possui as colunas obrigatórias', function () {
    expect(Schema::hasColumns('permissions', [
        'id',
        'slug',
        'description',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('coluna description não permite valores nulos', function () {
    $description = collect(Schema::getColumns('permissions'))
        ->firstWhere('name', 'description');

    expect($description)->not->toBeNull();

    expect($description['nullable'])->toBeFalse();
});
