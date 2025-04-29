<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeedBack;
use Symfony\Component\HttpFoundation\Response;

class FeedBackController extends Controller
{
    public function index()
    {
        $all = FeedBack::orderBy('created_at', 'desc')->get();
        return response()->json($all, Response::HTTP_OK);
    }

    /**
     * Create new feedback.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        $fb = FeedBack::create([
            'name'    => $request->name,
            'message' => $request->message,
        ]);

        return response()->json($fb, Response::HTTP_CREATED);
    }

    /**
     * Show one feedback entry.
     */
    public function show($id)
    {
        $fb = FeedBack::findOrFail($id);
        return response()->json($fb, Response::HTTP_OK);
    }

    /**
     * Delete a feedback entry.
     */
    public function destroy($id)
    {
        $fb = FeedBack::findOrFail($id);
        $fb->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
