<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\PerumahanCreateRequest;
use App\Http\Requests\PerumahanDeleteRequest;
use App\Http\Requests\PerumahanUpdateRequest;
use App\Http\Resources\PerumahanResource;
use App\Perumahan;
use App\Unit;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class PerumahanController extends Controller
{
    public function getPerumahan(): JsonResponse
    {
        try {
            $user = Auth::user();
            $perumahans = Perumahan::where('user_id', $user->id)->get();

            if ($perumahans->isEmpty()) {
                return response()->json(ApiResponse::error('Perumahan not found', 404));
            }

            return response()->json(ApiResponse::success('Get data successfully', PerumahanResource::collection($perumahans)));
            // return PerumahanResource::collection($perumahans);
        } catch (\Exception $e) {
            // Handle the exception, log it, and return an appropriate response
            return ApiResponse::error('Internal Server Error' . $e->getMessage(), 500);
        }
    }

    public function getPerumahanById(Request $request)
    {

        try {
            $perumahan = Perumahan::findOrFail($request->id);
            return response()->json(ApiResponse::success('Perumahans fetched successfully', new PerumahanResource($perumahan)));
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Perumahan not found', 404);
        }
    }

    public function createPerumahan(PerumahanCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();

        if (Perumahan::where('kode_unit', $data['kode_unit'])->where('user_id', $user->id)->count() == 1) {
            return ApiResponse::error('kode unit already registered, try another one.', 400);
        }
        
        $data['user_id'] = $user->id;
        $perumahan = new Perumahan($data);
        $perumahan->save();
        
        // Initialize an array to store all units
        $units = [];
        
        // Create units based on jml_unit
        for ($i = 0; $i < $data['jml_unit']; $i++) {
            $units[] = [
                'name' => $data['kode_unit'] . '-' . ($i + 1),
                'kode_unit' => $data['kode_unit'],
                'id_parent' => $perumahan->id,
                'user_id' => $user->id,
                'type' => 'perumahan',
                'status' => 'empty',
                'purchase_type' => 'sewa',
                'tenor' => 0,
                'payment_no' => 0,
                // Set other attributes of Unit here
            ];
        }

        // Save all units in one go
        Unit::insert($units);

        return response()->json(ApiResponse::success('Success create perumahan', new PerumahanResource($perumahan)), 201);
    }

    public function updatePerumahan(PerumahanUpdateRequest $request, $id): JsonResponse
    {
        $data = $request->validated();
        $perumahan = Perumahan::where('id', $id)->first();

        if (!$perumahan) {
            return ApiResponse::error('Perumahan not found', 400);
        }

        $perumahan->update($data);

        return response()->json(ApiResponse::success('Success update perumahan', new PerumahanResource($perumahan)), 201);
    }

    public function deletePerumahan($id): JsonResponse
    {
        try {
            $perumahan = Perumahan::where('id', $id)->first();

            if (!$perumahan) {
                return ApiResponse::error('Perumahan not found', 400);
            }

            $perumahan->delete();

            return response()->json(ApiResponse::success('Perumahan deleted successfully'), 200);
        } catch (QueryException $e) {
            // Handle any database errors
            return ApiResponse::error('Failed to delete Perumahan', 500);
        }
    }
}
