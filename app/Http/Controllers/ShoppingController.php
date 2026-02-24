<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\GroceryItem;
use App\Models\ShoppingAssignment;
use Illuminate\View\View;

class ShoppingController extends Controller
{
    /**
     * Mobile-optimized shopping checklist for a family.
     * No auth required — volunteers access via link or QR code.
     */
    public function checklist(int $family_number): View
    {
        $family = Family::where('family_number', $family_number)->firstOrFail();
        $shoppingList = GroceryItem::calculateForFamily($family);

        // Group by category
        $grouped = [];
        foreach ($shoppingList as $itemName => $info) {
            $grouped[$info['category']][$itemName] = $info['quantity'];
        }

        $totalItems = array_sum(array_map(fn($info) => $info['quantity'], $shoppingList));

        return view('shopping.checklist', compact('family', 'grouped', 'totalItems'));
    }

    /**
     * Mobile shopping checklist accessed by token.
     * No auth required — NINJAs and coordinators access via shareable link.
     */
    public function assignmentByToken(string $token): View
    {
        $assignment = ShoppingAssignment::where('token', $token)->firstOrFail();

        return view('shopping.assignment', compact('assignment'));
    }
}
