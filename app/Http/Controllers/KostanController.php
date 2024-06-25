<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\KostanCreateRequest;
use App\Http\Requests\KostanDeleteRequest;
use App\Http\Requests\KostanUpdateRequest;
use App\Http\Resources\KostanResource;
use App\Kostan;
use App\Unit;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class KostanController extends Controller
{
    public function getKostan(): JsonResponse
    {
        try {
            $user = Auth::user();
            $kontans = Kostan::where('user_id', $user->id)->get();

            if ($kontans->isEmpty()) {
                return response()->json(ApiResponse::error('Kostan not found', 404));
            }

            return response()->json(ApiResponse::success('Get data successfully', KostanResource::collection($kontans)));
            // return KostanResource::collection($kontans);
        } catch (\Exception $e) {
            // Handle the exception, log it, and return an appropriate response
            return ApiResponse::error('Internal Server Error' . $e->getMessage(), 500);
        }
    }

    public function getKostanById(Request $request)
    {

        try {
            $kostan = Kostan::findOrFail($request->id);
            return response()->json(ApiResponse::success('Kostans fetched successfully', new KostanResource($kostan)));
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Kostan not found', 404);
        }
    }

    public function createKostan(KostanCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();

        if (Kostan::where('kode_unit', $data['kode_unit'])->where('user_id', $user->id)->count() == 1) {
            return ApiResponse::error('kode unit already registered, try another one.', 400);
        }

        $data['user_id'] = $user->id;
        $kostan = new Kostan($data);
        $kostan->save();

        // Initialize an array to store all units
        $units = [];

        // Create units based on jml_unit
        for ($i = 0; $i < $data['jml_unit']; $i++) {
            $units[] = [
                'name' => $data['kode_unit'] . '-' . ($i + 1),
                'kode_unit' => $data['kode_unit'],
                'id_parent' => $kostan->id,
                'user_id' => $user->id,
                'type' => 'kostan',
                'status' => 'empty',
                'purchase_type' => 'sewa',
                'tenor' => 0,
                'payment_no' => 0,
                // Set other attributes of Unit here
            ];
        }

        // Save all units in one go
        Unit::insert($units);

        return response()->json(ApiResponse::success('Success create Kostan', new KostanResource($kostan)), 201);
    }

    public function updateKostan(KostanUpdateRequest $request, $id): JsonResponse
    {
        $data = $request->validated();
        $kostan = Kostan::where('id', $id)->first();

        if (!$kostan) {
            return ApiResponse::error('Kostan not found', 400);
        }

        $kostan->update($data);

        return response()->json(ApiResponse::success('Success update Kostan', new KostanResource($kostan)), 201);
    }

    public function deleteKostan($id): JsonResponse
    {
        try {
            $kostan = Kostan::where('id', $id)->first();

            if (!$kostan) {
                return ApiResponse::error('Kostan not found', 400);
            }

            $kostan->delete();

            return response()->json(ApiResponse::success('Kostan deleted successfully'), 200);
        } catch (QueryException $e) {
            // Handle any database errors
            return ApiResponse::error('Failed to delete Kostan', 500);
        }
    }
}
