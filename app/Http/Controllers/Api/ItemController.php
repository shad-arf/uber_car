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
        // Eager-load the user relationship
        $items = Item::with('users:id,name,email,phone')->get();

        $data = $items->map(function ($item) {
            return [
                'id'          => $item->id,
                'title'       => $item->title,
                'description' => $item->description,
                'phone'       => $item->phone,
                'user_id'     => $item->user_id,
                'user_email'  => optional($item->user)->email,
                'user_phone'  => optional($item->user)->phone,
                'destination' => $item->destination,
                'time'        => $item->time,
                'address'     => $item->address,
                'date'        => $item->date,
                'is_taken'    => $item->is_taken,
            ];
        });

        return response()->json($data, Response::HTTP_OK);
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
            'post_type'   => 'nullable|string',
        ]);

        $user = $request->user(); // or Auth::user()
        dd($user);
        $item = Item::create([
            'title'       => $request->title,
            'description' => $request->description,
            'address'     => $request->address,
            'phone'       => $request->phone,
            'date'        => $request->date,
            'destination' => $request->destination,
            'time'        => $request->time,
            'is_taken'    => false,
            'user_id'     => $user->id,
            'post_type'   => $request->post_type,
        ]);

        $item->load('users:id,email,phone');

        return response()->json([
            'id'          => $item->id,
            'title'       => $item->title,
            'description' => $item->description,
            'user_id'     => $item->user_id,
            'user_email'  => optional($item->user)->email,
            'user_phone'  => optional($item->user)->phone,
            'phone'       => $item->phone,
            'destination' => $item->destination,
            'time'        => $item->time,
            'address'     => $item->address,
            'date'        => $item->date,
            'post_type'   => $item->post_type,
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
            'user_email'  => optional($item->user)->email,
            'user_phone'  => optional($item->user)->phone,
            'destination' => $item->destination,
            'time'        => $item->time,
            'address'     => $item->address,
            // defalute post type to 'lost'
            'post_type'   => $item->post_type ?? 'user',
            'date'        => $item->date,
            'is_taken'    => $item->is_taken,
        ], Response::HTTP_OK);
    }

    /**
     * Update an item
     */
    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

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
            'post_type'   => 'sometimes|nullable|string',
            'date'        => 'sometimes|nullable|date',
            'is_taken'    => 'sometimes|boolean',
        ]);

        $item->update($request->only([
            'title', 'description', 'address', 'phone', 'destination', 'time', 'date', 'is_taken', 'post_type'
        ]));

        $item->load('users:id,email,phone');

        return response()->json([
            'id'          => $item->id,
            'title'       => $item->title,
            'description' => $item->description,
            'user_id'     => $item->user_id,
            'user_email'  => optional($item->user)->email,
            'user_phone'  => optional($item->user)->phone,
            'phone'       => $item->phone,
            'destination' => $item->destination,
            'post_type'   => $item->post_type,
            'time'        => $item->time,
            'address'     => $item->address,
            'date'        => $item->date,
            'is_taken'    => $item->is_taken,
        ], Response::HTTP_OK);
    }

    /**
     * Mark item as taken
     */
    public function takeItem($id)
    {
        $item = Item::findOrFail($id);
        if ($item->is_taken) {
            return response()->json(['message' => 'Already taken'], Response::HTTP_BAD_REQUEST);
        }

        $item->update(['is_taken' => true]);

        return response()->json(['message' => 'Item is now taken'], Response::HTTP_OK);
    }

    /**
     * Delete an item
     */
    public function destroy($id)
    {
        $item = Item::findOrFail($id);

        if ($item->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $item->delete();

        return response()->json(['message' => 'Item deleted successfully'], Response::HTTP_OK);
    }

}
