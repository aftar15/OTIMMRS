<?php

namespace App\Http\Controllers;

use App\Models\Arrival;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ArrivalController extends Controller
{
    public function index()
    {
        try {
            $arrivals = Arrival::with(['tourist', 'accommodation'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $arrivals
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch arrivals', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch arrivals'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tourist_id' => 'required|exists:tourists,id',
                'accommodation_id' => 'nullable|exists:accommodations,id',
                'arrival_date' => 'required|date',
                'departure_date' => 'required|date|after:arrival_date',
                'purpose_of_visit' => 'required|string',
                'transportation_mode' => 'required|string',
                'number_of_companions' => 'required|integer|min:0',
                'contact_number' => 'required|string',
                'emergency_contact' => 'required|string',
                'emergency_contact_number' => 'required|string',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $arrival = Arrival::create($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Arrival created successfully',
                'data' => $arrival->load(['tourist', 'accommodation'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create arrival', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create arrival'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $arrival = Arrival::with(['tourist', 'accommodation'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $arrival
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch arrival', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch arrival details'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $arrival = Arrival::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'tourist_id' => 'sometimes|exists:tourists,id',
                'accommodation_id' => 'nullable|exists:accommodations,id',
                'arrival_date' => 'sometimes|date',
                'departure_date' => 'sometimes|date|after:arrival_date',
                'purpose_of_visit' => 'sometimes|string',
                'transportation_mode' => 'sometimes|string',
                'number_of_companions' => 'sometimes|integer|min:0',
                'status' => 'sometimes|string|in:pending,confirmed,cancelled,completed',
                'contact_number' => 'sometimes|string',
                'emergency_contact' => 'sometimes|string',
                'emergency_contact_number' => 'sometimes|string',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $arrival->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Arrival updated successfully',
                'data' => $arrival->fresh(['tourist', 'accommodation'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update arrival', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update arrival'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $arrival = Arrival::findOrFail($id);

            DB::beginTransaction();

            $arrival->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Arrival deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete arrival', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete arrival'
            ], 500);
        }
    }

    public function getAllForAdmin()
    {
        try {
            $arrivals = Arrival::with(['tourist', 'accommodation'])
                ->orderBy('arrival_date', 'desc')
                ->get()
                ->map(function ($arrival) {
                    return [
                        'id' => $arrival->id,
                        'tourist_name' => $arrival->tourist->full_name,
                        'accommodation_name' => $arrival->accommodation ? $arrival->accommodation->name : null,
                        'arrival_date' => $arrival->arrival_date->format('Y-m-d H:i:s'),
                        'departure_date' => $arrival->departure_date->format('Y-m-d H:i:s'),
                        'purpose_of_visit' => $arrival->purpose_of_visit,
                        'transportation_mode' => $arrival->transportation_mode,
                        'number_of_companions' => $arrival->number_of_companions,
                        'status' => $arrival->status,
                        'contact_number' => $arrival->contact_number,
                        'emergency_contact' => $arrival->emergency_contact,
                        'emergency_contact_number' => $arrival->emergency_contact_number,
                        'notes' => $arrival->notes,
                        'created_at' => $arrival->created_at->format('Y-m-d H:i:s')
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $arrivals
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch arrivals for admin', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch arrivals'
            ], 500);
        }
    }
} 