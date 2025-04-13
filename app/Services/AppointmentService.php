<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;

class AppointmentService
{
    /**
     * Check if a requested appointment time is valid and available
     */
    public function isTimeSlotAvailable(array $data): bool
    {
        $startDateTime = Carbon::parse($data['start_time']);
        $endDateTime   = Carbon::parse($data['end_time']);

        // Dynamic working hours (can be replaced from DB or config)
        $openTime  = Carbon::parse($startDateTime->format('Y-m-d') . ' 09:00:00');
        $closeTime = Carbon::parse($startDateTime->format('Y-m-d') . ' 17:00:00');

        // Check if it's weekend (0 = Sunday, 6 = Saturday)
        if ($startDateTime->isWeekend()) {
            return false;
        }

        // Check if time is outside working hours
        if ($startDateTime < $openTime || $endDateTime > $closeTime) {
            return false;
        }

        // Check if this appointment overlaps with another
        return !$this->hasOverlappingAppointments($data);
    }

    /**
     * Check for overlapping appointments with existing ones
     */
    private function hasOverlappingAppointments(array $data): bool
    {
        $query = Appointment::where(function ($query) use ($data) {
            $query->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                  ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']])
                  ->orWhere(function ($q) use ($data) {
                      $q->where('start_time', '<=', $data['start_time'])
                        ->where('end_time', '>=', $data['end_time']);
                  });
        })->where('status', '!=', 'cancelled');

        // If updating an appointment, exclude it from the check
        if (isset($data['id'])) {
            $query->where('id', '!=', $data['id']);
        }

        return $query->exists();
    }

    /**
     * Get all available time slots for a specific day and service
     */
    public function getAvailableTimeSlots(string $date, int $serviceId): array
    {
        $service      = Service::findOrFail($serviceId);
        $duration     = $service->duration_minutes;
        $selectedDate = Carbon::parse($date)->startOfDay();

        // Skip weekends
        if ($selectedDate->isWeekend()) {
            return [];
        }

        $slots = [];
        $start = Carbon::parse($selectedDate->format('Y-m-d') . ' 09:00:00');
        $end   = Carbon::parse($selectedDate->format('Y-m-d') . ' 17:00:00');

        // Fetch all existing appointments for that date
        $appointments = Appointment::whereDate('start_time', $selectedDate)
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_time')
            ->get();

        $currentSlot = $start->copy();

        // Iterate in 30-minute increments to find available slots
        while ($currentSlot->copy()->addMinutes($duration) <= $end) {
            $slotEnd = $currentSlot->copy()->addMinutes($duration);

            $isAvailable = true;
            foreach ($appointments as $appointment) {
                $apptStart = Carbon::parse($appointment->start_time);
                $apptEnd   = Carbon::parse($appointment->end_time);

                if (
                    ($currentSlot >= $apptStart && $currentSlot < $apptEnd) ||
                    ($slotEnd > $apptStart && $slotEnd <= $apptEnd) ||
                    ($currentSlot <= $apptStart && $slotEnd >= $apptEnd)
                ) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable) {
                $slots[] = [
                    'start' => $currentSlot->format('Y-m-d H:i:s'),
                    'end'   => $slotEnd->format('Y-m-d H:i:s'),
                ];
            }

            $currentSlot->addMinutes(30);
        }

        return $slots;
    }
}
