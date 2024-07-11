<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
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
    /**
     * @OA\Get(
     *     path="/api/v1/reservations",
     *     operationId="getReservationsList",
     *     tags={"Reservations"},
     *     summary="Get list of reservations",
     *     description="Returns list of reservations",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"created_at", "updated_at", "start_time", "end_time", "status"}),
     *         description="Field to sort by, e.g., created_at, updated_at, start_time, end_time, status"
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}),
     *         description="Sort order: asc or desc"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Reservation"))
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to retrieve reservations",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to retrieve reservations."),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $sortBy = $request->query('sort_by', 'start_time'); // Default sort by start_time
            $sortOrder = $request->query('sort_order', 'asc'); // Default sort order asc

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
    /**
     * @OA\Post(
     *     path="/api/v1/reservations",
     *     operationId="storeReservation",
     *     tags={"Reservations"},
     *     summary="Store a new reservation",
     *     description="Stores a new reservation and returns the reservation data",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreReservationRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Reservation")
     *     )
     * )
     */
    public function store(StoreReservationRequest $request)
    {
        $validatedData = $request->validated();

        try {
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
    /**
     * @OA\Get(
     *     path="/api/v1/reservations/{id}",
     *     operationId="getReservationById",
     *     tags={"Reservations"},
     *     summary="Get reservation information",
     *     description="Returns reservation data",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Reservation")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Reservation not found"
     *     )
     * )
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
    /**
     * @OA\Patch(
     *     path="/api/v1/reservations/{id}",
     *     operationId="updateReservation",
     *     tags={"Reservations"},
     *     summary="Update an existing reservation",
     *     description="Updates an existing reservation and returns the updated data",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateReservationRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Reservation")
     *     )
     * )
     */
    public function update(UpdateReservationRequest $request, string $id)
    {
        $validatedData = $request->validated();

        try {
            $reservation = Reservation::findOrFail($id);

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
    /**
     * @OA\Delete(
     *     path="/api/v1/reservations/{id}",
     *     operationId="deleteReservation",
     *     tags={"Reservations"},
     *     summary="Delete an existing reservation",
     *     description="Deletes an existing reservation and returns success message",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Reservation")
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try {
            $reservation = Reservation::findOrFail($id);

            if (Auth::user()->is_admin || $reservation->user_id == Auth::id()) {
                $reservation->delete();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Reservation successfully deleted.',
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
                'message' => 'Failed to delete reservation.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
