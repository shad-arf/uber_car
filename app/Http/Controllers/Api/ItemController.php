<?php


namespace App\Http\Controllers\Api;

use App\Models\Item;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends Controller
{
    /**
     * List all items
     */
    public function index()
    {
        // Load user so we can easily return user_email, user_phone
        $items = Item::with('users:id,name,email,phone')->get();

        // Transform them into the shape your Flutter code wants
        $data = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'phone' => $item->phone,
                'user_id' => $item->user_id,
                'user_email' => $item->user->email ?? null,
                'user_phone' => $item->user->phone ?? null,
                'destination' => $item->destination,
                'time' => $item->time,
                'address' => $item->address,
                'date' => $item->date,
                'is_taken' => $item->is_taken,
            ];
        });

        return response()->json($data, 200);
    }

    /**
     * Create new item
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone'       => 'nullable|string',
            'address'     => 'nullable|string',
            'destination' => 'nullable|string',
            'time'        => 'nullable|string',
            'date'        => 'nullable|date',
        ]);

        $item = Item::create([
            'title'       => $request->title,
            'description' => $request->description,
            'address'     => $request->address,
            'phone'       => $request->phone,
            'date'        => $request->date,
            'destination' => $request->destination,
            'time'        => $request->time,
            'is_taken'    => false,       // default
            'user_id'     => Auth::id(),  // current user
        ]);


        // Load the user relationship so we can return user_email, user_phone
        $item->load('users:id,email,phone');

        // Return item in the same shape
        return response()->json([
            'id'          => $item->id,
            'title'       => $item->title,
            'description' => $item->description,
            'user_id'     => $item->user_id,
            'user_email'  => $item->user->email ?? null,
            'user_phone'  => $item->user->phone ?? null,
            'phone'       => $item->phone,
            'destination' => $item->destination,
            'time'        => $item->time,
            'address'     => $item->address,
            'date'        => $item->date,
            'is_taken'    => $item->is_taken,
        ], Response::HTTP_CREATED);
    }

    /**
     * Show one item
     */
    public function show($id)
    {
        $item = Item::with('users:id,email,phone')->findOrFail($id);

        return response()->json([
            'id'          => $item->id,
            'title'       => $item->title,
            'phone'       => $item->phone,
            'description' => $item->description,
            'user_id'     => $item->user_id,
            'user_email'  => $item->user->email ?? null,
            'user_phone'  => $item->user->phone ?? null,
            'destination' => $item->destination,
            'time'        => $item->time,
            'address'     => $item->address,
            'date'        => $item->date,
            'is_taken'    => $item->is_taken,
        ], 200);
    }

    /**
     * Update an item
     */
    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        // Make sure only the owner can update
        if ($item->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'address'     => 'sometimes|nullable|string',
            'phone'       => 'sometimes|nullable|string',
            'destination' => 'sometimes|nullable|string',
            'time'        => 'sometimes|nullable|string',
            'date'        => 'sometimes|nullable|date',
            'is_taken'    => 'sometimes|boolean',
        ]);

        $item->update($request->only([
            'title',
            'description',
            'address',
            'phone',
            'destination',
            'time',
            'date',
            'is_taken',
        ]));

        // load user relationship
        $item->load('users:id,email,phone');

        return response()->json([
            'id'          => $item->id,
            'title'       => $item->title,
            'description' => $item->description,
            'user_id'     => $item->user_id,
            'user_email'  => $item->user->email ?? null,
            'user_phone'  => $item->user->phone ?? null,
            'phone'       => $item->phone,
            'destination' => $item->destination,
            'time'        => $item->time,
            'address'     => $item->address,
            'date'        => $item->date,
            'is_taken'    => $item->is_taken,
        ], 200);
    }

    /**
     * Mark item as taken
     * (An alternative to setting is_taken via update)
     */
    public function takeItem($id)
    {
        $item = Item::findOrFail($id);
        if ($item->is_taken) {
            return response()->json(['message' => 'Already taken'], 400);
        }

        // You could also require authentication if needed
        $item->is_taken = true;
        $item->save();

        return response()->json(['message' => 'Item is now taken'], 200);
    }

    /**
     * Delete an item
     */
    public function destroy($id)
    {
        $item = Item::findOrFail($id);

        // Only owner can delete
        if ($item->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $item->delete();

        return response()->json(['message' => 'Item deleted successfully'], Response::HTTP_OK);
    }
}
