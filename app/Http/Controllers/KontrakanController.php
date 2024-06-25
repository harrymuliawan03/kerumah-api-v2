<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\KontrakanCreateRequest;
use App\Http\Requests\KontrakanDeleteRequest;
use App\Http\Requests\KontrakanUpdateRequest;
use App\Http\Resources\KontrakanResource;
use App\Kontrakan;
use App\Unit;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class KontrakanController extends Controller
{
    public function getKontrakan(): JsonResponse
    {
        try {
            $user = Auth::user();
            $kontrakans = Kontrakan::where('user_id', $user->id)->get();

            if ($kontrakans->isEmpty()) {
                return response()->json(ApiResponse::error('Kontrakan not found', 404));
            }

            return response()->json(ApiResponse::success('Get data successfully', KontrakanResource::collection($kontrakans)));
            // return KontrakanResource::collection($kontrakans);
        } catch (\Exception $e) {
            // Handle the exception, log it, and return an appropriate response
            return ApiResponse::error('Internal Server Error' . $e->getMessage(), 500);
        }
    }

    public function getKontrakanById(Request $request)
    {

        try {
            $kontrakan = Kontrakan::findOrFail($request->id);
            return response()->json(ApiResponse::success('Kontrakans fetched successfully', new KontrakanResource($kontrakan)));
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Kontrakan not found', 404);
        }
    }

    public function createKontrakan(KontrakanCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();

        if (Kontrakan::where('kode_unit', $data['kode_unit'])->where('user_id', $user->id)->count() == 1) {
            return ApiResponse::error('kode unit already registered, try another one.', 400);
        }

        $data['user_id'] = $user->id;
        $kontrakan = new Kontrakan($data);
        $kontrakan->save();

        // Initialize an array to store all units
        $units = [];

        // Create units based on jml_unit
        for ($i = 0; $i < $data['jml_unit']; $i++) {
            $units[] = [
                'name' => $data['kode_unit'] . '-' . ($i + 1),
                'kode_unit' => $data['kode_unit'],
                'id_parent' => $kontrakan->id,
                'user_id' => $user->id,
                'type' => 'kontrakan',
                'status' => 'empty',
                'purchase_type' => 'sewa',
                'tenor' => 0,
                'payment_no' => 0,
                // Set other attributes of Unit here
            ];
        }

        // Save all units in one go
        Unit::insert($units);

        return response()->json(ApiResponse::success('Success create Kontrakan', new KontrakanResource($kontrakan)), 201);
    }

    public function updateKontrakan(KontrakanUpdateRequest $request, $id): JsonResponse
    {
        $data = $request->validated();
        $kontrakan = Kontrakan::where('id', $id)->first();

        if (!$kontrakan) {
            return ApiResponse::error('Kontrakan not found', 400);
        }

        $kontrakan->update($data);

        return response()->json(ApiResponse::success('Success update Kontrakan', new KontrakanResource($kontrakan)), 201);
    }

    public function deleteKontrakan($id): JsonResponse
    {
        try {
            $kontrakan = Kontrakan::where('id', $id)->first();

            if (!$kontrakan) {
                return ApiResponse::error('Kontrakan not found', 400);
            }

            $kontrakan->delete();

            return response()->json(ApiResponse::success('Kontrakan deleted successfully'), 200);
        } catch (QueryException $e) {
            // Handle any database errors
            return ApiResponse::error('Failed to delete Kontrakan', 500);
        }
    }
}
