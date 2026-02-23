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
     * Mobile coordinator assignment checklist.
     * No auth required — access via link or QR code.
     */
    public function assignment(ShoppingAssignment $assignment): View
    {
        $assignment->load('user');
        $shoppingList = $assignment->getShoppingList();
        $totalItems = $assignment->getTotalItems();

        return view('shopping.assignment', compact('assignment', 'shoppingList', 'totalItems'));
    }
}
