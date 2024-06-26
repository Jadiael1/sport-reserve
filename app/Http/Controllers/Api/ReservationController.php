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
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Reservation"))
     *     )
     * )
     */
    public function index()
    {
        try {
            if (Auth::user()->is_admin) {
                $reservations = Reservation::with('field')->paginate();
            } else {
                $reservations = Reservation::with('field')->where('user_id', Auth::id())->paginate();
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
            // Excluir reservas pendentes que passaram 30 minutos
            $this->cleanupPendingReservations($validatedData['field_id'], $validatedData['start_time'], $validatedData['end_time']);

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
            $reservation = Reservation::with('field')->findOrFail($id);

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

    /**
     * Cleanup pending reservations older than 30 minutes.
     */
    protected function cleanupPendingReservations($fieldId, $startTime, $endTime)
    {
        $thresholdTime = Carbon::now()->subMinutes(30);

        $pendingReservations = Reservation::where('field_id', $fieldId)
            ->where('status', 'pending')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($query) use ($startTime, $endTime) {
                        $query->where('start_time', '<', $startTime)
                            ->where('end_time', '>', $endTime);
                    });
            })
            ->where('created_at', '<', $thresholdTime)
            ->get();

        foreach ($pendingReservations as $reservation) {
            $reservation->delete();
        }
    }
}
