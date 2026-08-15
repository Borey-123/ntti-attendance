<?php

namespace App\Http\Controllers;

use App\Models\RfidCard;
use App\Models\Teacher;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class RfidCardController extends Controller
{
    /**
     * Return the last unregistered card UID scanned by the ESP32.
     * The RFID assignment page polls this to auto-fill the UID field.
     */
    public function pendingScan(): JsonResponse
    {
        $pending = Cache::get('pending_rfid_uid');

        if ($pending) {
            Cache::forget('pending_rfid_uid'); // consume it
            return response()->json([
                'found' => true,
                'uid'   => $pending['uid'],
                'scanned_at' => $pending['scanned_at'],
            ]);
        }

        return response()->json(['found' => false]);
    }

    public function index(Request $request)
    {
        $query = RfidCard::with('teacher');

        if ($request->department) {
            $query->whereHas('teacher', function($q) use ($request) {
                $q->where('department', $request->department);
            });
        }

        $cards = $query->orderBy('created_at', 'desc')->get();
        
        $teachersQuery = Teacher::where('status', 'active')->doesntHave('rfidCard');
        if ($request->department) {
            $teachersQuery->where('department', $request->department);
        }
        $teachers = $teachersQuery->orderBy('name')->get();
        
        $departments = Department::pluck('name')->sort();

        if ($request->expectsJson()) {
            return response()->json($cards);
        }
        return view('rfid.index', compact('cards', 'teachers', 'departments'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'uid' => 'required|string|unique:rfid_cards,uid',
            'teacher_id' => 'required|exists:teachers,id',
            'status' => 'in:active,inactive',
        ]);

        // Remove any existing card for this teacher
        RfidCard::where('teacher_id', $validated['teacher_id'])->delete();

        $card = RfidCard::create([
            'uid' => strtoupper(trim($validated['uid'])),
            'teacher_id' => $validated['teacher_id'],
            'status' => $validated['status'] ?? 'active',
            'assigned_at' => now(),
        ]);

        return response()->json(['status' => 'success', 'card' => $card->load('teacher')], 201);
    }

    public function update(Request $request, RfidCard $rfidCard): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive',
            'uid' => 'sometimes|string|unique:rfid_cards,uid,' . $rfidCard->id,
        ]);
        $rfidCard->update($validated);
        return response()->json(['status' => 'success', 'card' => $rfidCard->load('teacher')]);
    }

    public function destroy(RfidCard $rfidCard): JsonResponse
    {
        $rfidCard->delete();
        return response()->json(['status' => 'success', 'message' => 'Card removed.']);
    }

    public function checkUid(Request $request): JsonResponse
    {
        $uid = strtoupper(trim($request->uid));
        $exists = RfidCard::where('uid', $uid)->with('teacher')->first();
        return response()->json([
            'exists' => (bool)$exists,
            'card' => $exists,
        ]);
    }
}
