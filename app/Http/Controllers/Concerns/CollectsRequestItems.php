<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Chemical;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait CollectsRequestItems
{
    protected function collectRequestedItems(Request $request, ?int $laboratoryId = null): array
    {
        $errors = [];
        $items = [];

        foreach ((array) $request->input('equipment_items', []) as $equipmentId => $payload) {
            $rawQuantity = $payload['quantity'] ?? null;

            if ($rawQuantity === null || $rawQuantity === '') {
                continue;
            }

            if (is_numeric($rawQuantity) && (float) $rawQuantity === 0.0) {
                continue;
            }

            if (filter_var($rawQuantity, FILTER_VALIDATE_INT) === false || (int) $rawQuantity < 1) {
                $errors['equipment_items.' . $equipmentId . '.quantity'] = 'Equipment quantities must be a whole number of at least 1.';
                continue;
            }

            $equipment = Equipment::find($equipmentId);

            if (! $equipment) {
                $errors['equipment_items.' . $equipmentId . '.quantity'] = 'Selected equipment was not found.';
                continue;
            }

            if ($laboratoryId !== null && (int) $equipment->laboratory_id !== $laboratoryId) {
                $errors['equipment_items.' . $equipmentId . '.quantity'] = 'This equipment does not belong to the selected laboratory.';
                continue;
            }

            if ($equipment->status !== 'Available') {
                $errors['equipment_items.' . $equipmentId . '.quantity'] = 'This equipment is not currently available.';
                continue;
            }

            $quantity = (int) $rawQuantity;

            if ($quantity > (int) $equipment->available_quantity) {
                $errors['equipment_items.' . $equipmentId . '.quantity'] = 'Requested quantity exceeds the available quantity.';
                continue;
            }

            $items[] = [
                'item_type' => 'Equipment',
                'item_id' => $equipment->id,
                'laboratory_id' => $equipment->laboratory_id,
                'quantity' => $quantity,
                'unit' => 'pcs',
                'remarks' => trim((string) ($payload['remarks'] ?? '')) ?: null,
            ];
        }

        foreach ((array) $request->input('chemical_items', []) as $chemicalId => $payload) {
            $rawQuantity = $payload['quantity'] ?? null;

            if ($rawQuantity === null || $rawQuantity === '') {
                continue;
            }

            if (is_numeric($rawQuantity) && (float) $rawQuantity === 0.0) {
                continue;
            }

            if (! is_numeric($rawQuantity) || (float) $rawQuantity <= 0) {
                $errors['chemical_items.' . $chemicalId . '.quantity'] = 'Chemical quantities must be a positive number.';
                continue;
            }

            $chemical = Chemical::find($chemicalId);

            if (! $chemical) {
                $errors['chemical_items.' . $chemicalId . '.quantity'] = 'Selected chemical was not found.';
                continue;
            }

            if ($laboratoryId !== null && (int) $chemical->laboratory_id !== $laboratoryId) {
                $errors['chemical_items.' . $chemicalId . '.quantity'] = 'This chemical does not belong to the selected laboratory.';
                continue;
            }

            if ($chemical->status !== 'Available') {
                $errors['chemical_items.' . $chemicalId . '.quantity'] = 'This chemical is not currently available.';
                continue;
            }

            $quantity = (float) $rawQuantity;

            if ($quantity > (float) $chemical->quantity) {
                $errors['chemical_items.' . $chemicalId . '.quantity'] = 'Requested quantity exceeds the available quantity.';
                continue;
            }

            $items[] = [
                'item_type' => 'Chemical',
                'item_id' => $chemical->id,
                'laboratory_id' => $chemical->laboratory_id,
                'quantity' => $quantity,
                'unit' => $payload['unit'] ?? $chemical->unit,
                'remarks' => trim((string) ($payload['remarks'] ?? '')) ?: null,
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $items;
    }

    protected function resolveRequestLaboratoryId(array $items): int
    {
        $laboratoryIds = collect($items)
            ->pluck('laboratory_id')
            ->filter()
            ->unique()
            ->values();

        if ($laboratoryIds->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Unable to determine the laboratory for the selected request items.',
            ]);
        }

        if ($laboratoryIds->count() > 1) {
            throw ValidationException::withMessages([
                'items' => 'All request items must belong to the same laboratory.',
            ]);
        }

        return (int) $laboratoryIds->first();
    }

    protected function requesterRules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'student_id' => ['required', 'string', 'regex:/^[SC]\d{2}-\d{4}$/'],
            'email' => ['required', 'email', 'regex:/^[^@\s]+@lccdo\.edu\.ph$/i', 'max:255'],
            'contact_number' => ['required', 'string', 'regex:/^(?:09\d{9}|\+639\d{9})$/'],
            'department_id' => ['required', 'exists:departments,id'],
        ];
    }
}
