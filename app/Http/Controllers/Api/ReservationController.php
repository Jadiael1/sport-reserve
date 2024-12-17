<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Models\Field;
use App\Models\Reservation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $sortBy = $request->query('sort_by', 'created_at'); // Default sort by start_time
            $sortOrder = $request->query('sort_order', 'desc'); // Default sort order asc

            $validSortFields = ['start_time', 'end_time', 'status', 'created_at', 'updated_at'];

            if (!in_array($sortBy, $validSortFields)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid sort field.',
                    'data' => null,
                    'errors' => null
                ], 400);
            }

            if (!in_array($sortOrder, ['asc', 'desc'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid sort order.',
                    'data' => null,
                    'errors' => null
                ], 400);
            }

            $now = Carbon::now('America/Recife');
            $reservations = Reservation::where('start_time', '<', $now)
                ->where('status', 'WAITING')
                ->whereDoesntHave('payments', function ($query) {
                    $query->where('status', 'PAID');
                })
                ->get();
            foreach ($reservations as $reservation) {
                $reservation->status = 'CANCELED';
                $reservation->save();
                $reservation->payments()->where('status', 'WAITING')->update(['status' => 'CANCELED']);
            }


            if (Auth::user()->is_admin) {
                $reservations = Reservation::with(['field', 'user', 'payments'])
                    ->orderBy($sortBy, $sortOrder)
                    ->paginate();
            } else {
                $reservations = Reservation::with(['field', 'payments'])
                    ->where('user_id', Auth::id())
                    ->orderBy($sortBy, $sortOrder)
                    ->paginate();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Reservations successfully recovered.',
                'data' => $reservations,
                'errors' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve reservations.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReservationRequest $request)
    {
        $validatedData = $request->validated();

        try {
            $field = Field::findOrFail($validatedData['field_id']);

            if ($field->status === 'inactive') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot create a reservation for an inactive field.',
                    'data' => null,
                    'errors' => null
                ], 403);
            }
            $reservation = Reservation::create([
                'user_id' => Auth::id(),
                'field_id' => $validatedData['field_id'],
                'start_time' => $validatedData['start_time'],
                'end_time' => $validatedData['end_time'],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Reservation created successfully.',
                'data' => $reservation,
                'errors' => null
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create reservation.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $reservation = Reservation::with(['field', 'user'])->findOrFail($id);

            $startTime = Carbon::parse($reservation->start_time)->setTimezone('America/Recife');
            $hasPaidPayments = $reservation->payments()->where('status', 'PAID')->exists();
            if ($startTime->isPast() && $reservation->status === 'WAITING' && !$hasPaidPayments) {
                $reservation->status = 'CANCELED';
                $reservation->save();
                $reservation->payments()->where('status', 'WAITING')->update(['status' => 'CANCELED']);
            }

            if (Auth::user()->is_admin || $reservation->user_id == Auth::id()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Reservation successfully recovered.',
                    'data' => $reservation,
                    'errors' => null
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access.',
                    'data' => null,
                    'errors' => null
                ], 403);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Reservation not found.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReservationRequest $request, string $id)
    {
        $validatedData = $request->validated();

        try {
            $reservation = Reservation::findOrFail($id);

            // Verifica se o campo da reserva está inativo
            $field = Field::findOrFail($reservation->field_id);
            if ($field->status === 'inactive') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot update reservation for an inactive field.',
                    'data' => null,
                    'errors' => null
                ], 403);
            }

            if (Auth::user()->is_admin || $reservation->user_id == Auth::id()) {
                $reservation->update($validatedData);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Reservation updated successfully.',
                    'data' => $reservation,
                    'errors' => null
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access.',
                    'data' => null,
                    'errors' => null
                ], 403);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update reservation.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $reservation = Reservation::findOrFail($id);

            if (Auth::user()->is_admin || $reservation->user_id == Auth::id()) {
                $reservation->status = 'CANCELED';
                $reservation->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Reservation canceled successfully.',
                    'data' => null,
                    'errors' => null
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access.',
                    'data' => null,
                    'errors' => null
                ], 403);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to cancel reservation.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function filterByDay(Request $request)
    {
        try {
            // Verifica se o usuário é administrador
            if (!Auth::user()->is_admin) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access denied. Only administrators can access this endpoint.',
                    'data' => null,
                    'errors' => null
                ], 403);
            }

            // Validação dos parâmetros
            $validated = $request->validate([
                'day_of_week' => 'required|string|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
                'status' => 'nullable|string|in:WAITING,CANCELED,PAID',
            ]);

            $dayOfWeek = strtolower($validated['day_of_week']);
            $status = $validated['status'] ?? 'PAID'; // Default para 'PAID'

            // Definir o intervalo de datas baseado no dia da semana
            $now = Carbon::now('America/Recife');
            $startOfWeek = $now->startOfWeek(Carbon::SUNDAY); // Define o domingo como início da semana
            $targetDay = $startOfWeek->copy()->addDays(array_search($dayOfWeek, ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']));

            $startOfDay = $targetDay->copy()->startOfDay();
            $endOfDay = $targetDay->copy()->endOfDay();

            // Query de reservas
            $reservations = Reservation::with(['field', 'user', 'payments'])
                ->whereBetween('start_time', [$startOfDay, $endOfDay])
                ->when($status, function ($query) use ($status) {
                    if ($status === 'WAITING') {
                        return $query->where('status', 'WAITING')
                            ->whereDoesntHave('payments', function ($q) {
                                $q->where('status', 'PAID');
                            });
                    } elseif ($status === 'CANCELED') {
                        return $query->where('status', 'CANCELED');
                    } else {
                        return $query->where('status', 'PAID')
                            ->whereHas('payments', function ($q) {
                                $q->where('status', 'PAID');
                            });
                    }
                })
                ->orderBy('start_time', 'asc')
                ->get();

            // Retorno da API
            return response()->json([
                'status' => 'success',
                'message' => "Reservations for {$dayOfWeek} successfully retrieved.",
                'data' => $reservations,
                'errors' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve reservations by day.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
