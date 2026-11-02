<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class InventoryCsvReader
{
    /**
     * @param  array<int, string>  $requiredHeaders
     * @return array<int, array{row: int, data: array<string, ?string>}>
     */
    public function read(UploadedFile $file, array $requiredHeaders): array
    {
        $path = $file->getRealPath();

        if (! is_string($path) || $path === '') {
            throw ValidationException::withMessages(['file' => 'The uploaded CSV file could not be read.']);
        }

        $stream = fopen($path, 'rb');

        if ($stream === false) {
            throw ValidationException::withMessages(['file' => 'The uploaded CSV file could not be read.']);
        }

        try {
            $headerRow = fgetcsv($stream, null, ',', '"', '');

            if ($headerRow === false || $headerRow === [null]) {
                throw ValidationException::withMessages(['file' => 'The CSV file must contain a header row.']);
            }

            $headerRow[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headerRow[0]) ?? (string) $headerRow[0];
            $headers = array_map(
                static fn ($header): string => strtolower(trim((string) $header)),
                $headerRow,
            );

            if (count($headers) !== count(array_unique($headers))) {
                throw ValidationException::withMessages(['file' => 'The CSV file contains duplicate column names.']);
            }

            $missingHeaders = array_values(array_diff($requiredHeaders, $headers));

            if ($missingHeaders !== []) {
                throw ValidationException::withMessages([
                    'file' => 'The CSV file is missing required columns: '.implode(', ', $missingHeaders).'.',
                ]);
            }

            $rows = [];
            $errors = [];
            $rowNumber = 1;

            while (true) {
                $values = fgetcsv($stream, null, ',', '"', '');

                if ($values === false) {
                    if (! feof($stream)) {
                        $errors[] = 'The uploaded CSV file could not be read completely.';
                    }

                    break;
                }

                $rowNumber++;

                if ($values === [null] || count(array_filter($values, static fn ($value): bool => trim((string) $value) !== '')) === 0) {
                    continue;
                }

                if (count($values) > count($headers)) {
                    $errors[] = "CSV row {$rowNumber} contains more values than the header row.";

                    continue;
                }

                $values = array_pad($values, count($headers), null);
                $rows[] = [
                    'row' => $rowNumber,
                    'data' => array_combine($headers, array_map(
                        static fn ($value): ?string => trim((string) $value) === '' ? null : trim((string) $value),
                        $values,
                    )),
                ];
            }

            if ($errors !== []) {
                throw ValidationException::withMessages(['file' => $errors]);
            }

            if ($rows === []) {
                throw ValidationException::withMessages(['file' => 'The CSV file does not contain any inventory rows.']);
            }

            return $rows;
        } finally {
            fclose($stream);
        }
    }
}
