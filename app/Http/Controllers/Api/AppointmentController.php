<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Policies\AppointmentPolicy;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\User;

class AppointmentController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Appointment::with(['user', 'service'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'       => 'required|exists:users,id',
            'service_id'    => 'required|exists:services,id',
            'start_time'    => 'required|date',
            'end_time'      => 'required|date|after:start_time'
        ]);

        $appointment = Appointment::create($validated);

        return response()->json($appointment, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $appointment = Appointment::findOrFail($id);
        return response()->json($appointment,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function cancel(Request $request, Appointment $appointment)
    {
        //dd(auth()->user());
        $this->authorize('cancel', $appointment);

        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $appointment->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->reason,
        ]);

        return response()->json([
            'message' => 'Appointment cancelled successfully.',
            'appointment' => $appointment,
        ]);
    }

    public function reschedule(Request $request, Appointment $appointment)
    {
        $this->authorize('reschedule', $appointment);

        $request->validate([
            'new_start_time' => 'required|date|after:now',
            'new_end_time' => 'required|date|after:new_start_time',
        ]);

        $appointment->update([
            'start_time' => $request->new_start_time,
            'end_time' => $request->new_end_time,
        ]);

        return response()->json([
            'message' => 'Appointment rescheduled successfully.',
            'appointment' => $appointment,
        ]);
    }


}
