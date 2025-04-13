<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AppointmentConfirmation;
use App\Models\Appointment;
use App\Notifications\AppointmentConfirmed;
use App\Notifications\AppointmentCreated;
use App\Notifications\AppointmentRescheduled;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Services\AppointmentService;
use Illuminate\Support\Facades\Mail;

class AppointmentController extends Controller
{
    use AuthorizesRequests;
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }
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

        // Check if the time slot is available
        if (!$this->appointmentService->isTimeSlotAvailable($validated)) {
            return response()->json(['message' => 'This time slot is not available.'], 422);
        }

        $appointment = Appointment::create($validated);

        // Notify user about new appointment
        $appointment->user->notify(new AppointmentCreated($appointment));

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
    public function update(Request $request, Appointment $appointment)
    {
       // $this->authorize('update', $appointment);

        $validated = $request->validate([
            'user_id'       => 'required|exists:users,id',
            'service_id'    => 'required|exists:services,id',
            'start_time'    => 'required|date',
            'end_time'      => 'required|date|after:start_time'
        ]);

        // Check if the new time slot is available (exclude current appointment)
        if (!$this->appointmentService->isTimeSlotAvailable($validated)) {
            return response()->json(['message' => 'This time slot is not available.'], 422);
        }

        // Update the appointment
        $appointment->update($validated);

        // Reload the model to get all relationships if needed
        $appointment->refresh();

        return response()->json([
            'message' => 'Appointment updated successfully.',
            'appointment' => $appointment,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return response()->json([
            'message' => 'Appointment Deleted successfully.'
        ], 200);
    }

    public function cancel(Request $request, Appointment $appointment)
    {
        //dd(auth()->user());
        //$this->authorize('cancel', $appointment);

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
        //$this->authorize('reschedule', $appointment);

        $request->validate([
            'new_start_time' => 'required|date|after:now',
            'new_end_time' => 'required|date|after:new_start_time',
        ]);

        $appointment->update([
            'start_time' => $request->new_start_time,
            'end_time' => $request->new_end_time,
        ]);

        // Notify user about rescheduling
        $appointment->user->notify(new AppointmentRescheduled($appointment)); 

        return response()->json([
            'message' => 'Appointment rescheduled successfully.',
            'appointment' => $appointment,
        ]);
    }

    public function availableSlots(Request $request)
    {
        //
        $request->validate([
            'date' => 'required|date',
            'service_id' => 'required|exists:services,id',
        ]);

        $slots = $this->appointmentService->getAvailableTimeSlots($request->date, $request->service_id);

        return response()->json($slots);
    }

    public function confirm(Appointment $appointment)
    {
        // Update appointment status
        $appointment->update([
            'status'        => 'confirmed',
            'confirmed_at'  => now()
        ]);

        // Send confirmation email
        Mail::to($appointment->user->email)->send(new AppointmentConfirmation($appointment));

        // Notify user about confirmation the appointment
        $appointment->user->notify(new AppointmentConfirmed($appointment));

        return response()->json([
            'message' => 'Appointment confirmed successfully.',
            'appointment' => $appointment,
        ]);
    }


}
