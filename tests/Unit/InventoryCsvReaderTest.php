<?php

use App\Services\InventoryCsvReader;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\TestCase;

uses(TestCase::class);

it('normalizes CSV headers and skips blank rows', function () {
    $path = tempnam(sys_get_temp_dir(), 'inventory-csv-');
    file_put_contents($path, "\xEF\xBB\xBF Equipment_Name , quantity \nMicroscope, 5 \n,\n");
    $file = new UploadedFile($path, 'inventory.csv', 'text/csv', null, true);

    try {
        $rows = (new InventoryCsvReader)->read($file, ['equipment_name', 'quantity']);
    } finally {
        unlink($path);
    }

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['row'])->toBe(2)
        ->and($rows[0]['data'])->toBe([
            'equipment_name' => 'Microscope',
            'quantity' => '5',
        ]);
});

it('rejects CSV rows with more values than the header', function () {
    $path = tempnam(sys_get_temp_dir(), 'inventory-csv-');
    file_put_contents($path, "equipment_name,quantity\nMicroscope,5,unexpected\n");
    $file = new UploadedFile($path, 'inventory.csv', 'text/csv', null, true);

    try {
        expect(fn () => (new InventoryCsvReader)->read($file, ['equipment_name', 'quantity']))
            ->toThrow(ValidationException::class);
    } finally {
        unlink($path);
    }
});
