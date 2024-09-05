<?php

namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\FoodItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class OrderController extends Controller
{
    public function index()
    {
        $orders = Auth::user()->orders()->with('orderItems.foodItem')->get();
        return response()->json($orders);
    }
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'items' => 'required|array',  // Array of food items with quantity
            'items.*.food_item_id' => 'required|exists:food_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'delivery_address' => 'required|string',
            'payment_method' => 'required|string',
        ]);

        // Initialize total price
        $totalPrice = 0;

        // Calculate total price based on food items and quantity
        foreach ($request->items as $item) {
            $foodItem = FoodItem::findOrFail($item['food_item_id']);
            $totalPrice += $foodItem->price * $item['quantity'];
        }

        // Create the order with total price
        $order = Order::create([
            'user_id' => Auth::id(),
            'status' => 'pending',  // Default status
            'payment_method' => $request->payment_method,
            'delivery_address' => $request->delivery_address,
            'total_price' => $totalPrice,
        ]);

        // Add items to the order
        foreach ($request->items as $item) {
            $foodItem = FoodItem::findOrFail($item['food_item_id']);
            $order->orderItems()->create([
                'food_item_id' => $foodItem->id,
                'quantity' => $item['quantity'],
            ]);
        }

        return response()->json(['message' => 'Order created successfully', 'order' => $order], 201);
    }

    
    public function show(Order $order)
    {
        // Ensure the user is authorized to view the order
        if ($order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $order->load('orderItems.foodItem');
        return response()->json($order);
    }
    public function update(Request $request, Order $order)
    {
        // Ensure the user is authorized to update the order
        if ($order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Update the order status
        $request->validate([
            'status' => 'required|string',
        ]);

        $order->update([
            'status' => $request->status,
        ]);

        return response()->json(['message' => 'Order updated successfully', 'order' => $order]);
    }

    public function destroy(Order $order)
    {
        // Ensure the user is authorized to delete the order
        if ($order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }

}
