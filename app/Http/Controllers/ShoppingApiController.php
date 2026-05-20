<?php

namespace App\Http\Controllers;

use App\Models\ShoppingAssignment;
use App\Models\ShoppingCheck;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShoppingApiController extends Controller
{
    public function show(string $token): JsonResponse
    {
        $assignment = ShoppingAssignment::where('token', $token)->firstOrFail();
        $shoppingList = $assignment->getShoppingList();
        $checks = $assignment->checks->keyBy('item_key');

        // Flatten the shopping list into a simple array with category info
        $items = [];
        foreach ($shoppingList as $category => $categoryItems) {
            foreach ($categoryItems as $itemName => $qty) {
                $items[] = [
                    'key' => $itemName,
                    'category' => $category,
                    'quantity' => $qty,
                ];
            }
        }

        $checksData = [];
        foreach ($checks as $key => $check) {
            $checksData[$key] = [
                'checked_by' => $check->checked_by,
                'checked_at' => $check->checked_at->toIso8601String(),
            ];
        }

        return response()->json([
            'assignment' => [
                'id' => $assignment->id,
                'display_name' => $assignment->getDisplayName(),
                'split_type' => $assignment->split_type,
                'description' => $assignment->getDescription(),
                'notes' => $assignment->notes,
                'family_start' => $assignment->family_start,
                'family_end' => $assignment->family_end,
            ],
            'items' => $items,
            'checks' => $checksData,
            'total_items' => array_sum(array_column($items, 'quantity')),
        ]);
    }

    public function toggle(string $token, \App\Http\Requests\ToggleShoppingItemRequest $request): JsonResponse
    {
        $assignment = ShoppingAssignment::where('token', $token)->firstOrFail();

        if (! in_array($request->item_key, $this->validItemKeys($assignment), true)) {
            throw ValidationException::withMessages([
                'item_key' => 'This item is not part of the shopping assignment.',
            ]);
        }

        DB::transaction(function () use ($assignment, $request) {
            $existing = ShoppingCheck::where('shopping_assignment_id', $assignment->id)
                ->where('item_key', $request->item_key)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $existing->delete();
                return;
            }

            try {
                ShoppingCheck::create([
                    'shopping_assignment_id' => $assignment->id,
                    'item_key' => $request->item_key,
                    'checked_by' => $request->ninja_name,
                    'checked_at' => now(),
                ]);
            } catch (UniqueConstraintViolationException) {
                // Another request checked this item between our lookup and insert.
            }
        });

        // Return updated checks
        $checks = $assignment->checks()->get()->keyBy('item_key');
        $checksData = [];
        foreach ($checks as $key => $check) {
            $checksData[$key] = [
                'checked_by' => $check->checked_by,
                'checked_at' => $check->checked_at->toIso8601String(),
            ];
        }

        return response()->json(['checks' => $checksData]);
    }

    private function validItemKeys(ShoppingAssignment $assignment): array
    {
        $keys = [];
        foreach ($assignment->getShoppingList() as $categoryItems) {
            foreach ($categoryItems as $itemName => $qty) {
                $keys[] = (string) $itemName;
            }
        }

        return array_values(array_unique($keys));
    }
}
