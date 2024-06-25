<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\UnitCreateRequest;
use App\Http\Requests\UnitRequest;
use App\Http\Requests\UnitUpdateRequest;
use App\Http\Resources\UnitResource;
use App\ListIdleProperty;
use App\ListPayment;
use App\Perumahan;
use App\Kontrakan;
use App\Kostan;
use App\Unit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class UnitController extends Controller
{
    public function getUnits(UnitRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $units = Unit::where('type', $data['type'])->where('id_parent', $data['id_parent'])->get();

            if ($units->isEmpty()) {
                return ApiResponse::error('Unit not found', 404);
            }

            // return UnitResource::collection($units);
            return response()->json(ApiResponse::success('Units fetched successfully', UnitResource::collection($units)));
        } catch (\Exception $e) {
            // Handle the exception, log it, and return an appropriate response
            return ApiResponse::error('Internal Server Error' . $e->getMessage(), 500);
        }
    }

    public function getUnitById(Request $request)
    {
        // dd($request->id);   
        try {
            $unit = Unit::findOrFail($request->id);
            if (!$unit) {
                return ApiResponse::error('Unit not found', 404);
            }
            // return new UnitResource($unit);
            return response()->json(ApiResponse::success('Units fetched successfully', new UnitResource($unit)));
        } catch (\Exception $e) {
            return ApiResponse::error('Unit not found', 404);
        }
    }

    public function createUnit(UnitCreateRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $properti = null;

            if($data['type'] == 'perumahan'){
                $properti = Perumahan::where('id', $data['id_parent'])->firstOrFail();
            }
            if($data['type'] == 'kontrakan'){
                $properti = Kontrakan::where('id', $data['id_parent'])->firstOrFail();
            }
            if($data['type'] == 'kostan'){
                $properti = Kostan::where('id', $data['id_parent'])->firstOrFail();
            }
            $units = Unit::where('id_parent', $data['id_parent'])->get();
            $unit_count = $units->count();
            $data['kode_unit'] = $properti->kode_unit;

            if ($units->isNotEmpty()) {
                $lastUnit = $units->last();

                preg_match('/(\d+)$/', $lastUnit['name'], $matches);

                if (isset($matches[1])) {
                    $lastNumber = $matches[1];
                    // dd($lastNumber + 1);
                    for ($i = 1; $i <= $data['jumlah_unit']; $i++) {
                        $data['name'] = $lastUnit->kode_unit . '-' . ($lastNumber + $i);
                        $data['user_id'] = $lastUnit->user_id;
                        $data['tenor'] = 0;
                        $unit = new Unit($data);
                        $unit->save();
                        $unit_count++;
                    }
                } else {
                    for ($i = 1; $i <= $data['jumlah_unit']; $i++) {
                        $data['name'] = $lastUnit->kode_unit . '-' . ($i);
                        $data['user_id'] = $lastUnit->user_id;
                        $data['tenor'] = 0;
                        $unit = new Unit($data);
                        $unit->save();
                        $unit_count++;
                    }
                }
            } else {
                for ($i = 1; $i <= $data['jumlah_unit']; $i++) {
                    $data['name'] = $data['kode_unit'] . '-' . $i;
                    $data['user_id'] = Auth::user()->id;
                    $data['tenor'] = 0;
                    $unit = new Unit($data);
                    $unit->save();
                    $unit_count++;
                }
            }

            $properti->update(['jml_unit' => $unit_count]);

            return response()->json(ApiResponse::success('Success create unit', null), 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500); // Internal Server Error
        }
    }

    function calculateDueDate($start_date, $period)
    {
        // Convert start date to Carbon instance
        $start_date = Carbon::createFromFormat('Y-m-d', $start_date);

        // Add period based on payment frequency
        switch ($period) {
            case 'month':
                $start_date->addMonth();
                break;
            case 'year':
                $start_date->addYear();
                break;
                // You can add more cases for other period types if needed
            default:
                // Handle unsupported period types
                break;
        }

        // Return the calculated due date
        return $start_date->format('Y-m-d');
    }

    public function updateUnit(UnitUpdateRequest $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $data = $request->validated();
            $unit = Unit::findOrFail($id);

            if (!$unit) {
                return ApiResponse::error('Unit not found', 404);
            }



            if (!empty($data['tanggal_mulai'])) {
                if ($unit->tanggal_mulai) {
                    $date1 = Carbon::createFromFormat('Y-m-d', $unit->tanggal_mulai);
                } else {
                    $date1 = Carbon::now();
                }
                // $date2 = Carbon::createFromFormat('Y-m-d', $data['tanggal_mulai']);
                if ($unit->tanggal_jatuh_tempo == null) {
                    $data['tanggal_jatuh_tempo'] = $this->calculateDueDate($data['tanggal_mulai'], $data['periode_pembayaran']);
                    // dd($data['tanggal_jatuh_tempo']);

                    // ListPayment::create([
                    //     'unit_id' => $unit->id,
                    //     'user_id' => $user->id,
                    //     'payment_date' => $data['tanggal_mulai'],
                    //     'due_date' => $data['tanggal_jatuh_tempo'],
                    // ]);
                }
            }
            if (!empty($data['status']) && $data['status'] === 'empty') {
                $data['tanggal_jatuh_tempo'] = null;
                $data['tanggal_mulai_kontrakan'] = null;
            }


            if ($unit->status == 'filled') {
                if (isset($data['tanggal_mulai_kontrakan'])) {
                    unset($data['tanggal_mulai_kontrakan']); // Remove the field from data
                }
                if (!empty($data['status']) && $data['status'] == 'empty') {
                    ListIdleProperty::create([
                        'unit_id' => $unit->id,
                        'user_id' => $user->id,
                    ]);
                }
            }


            $unit->update($data);

            return response()->json(ApiResponse::success('Success update unit', new UnitResource($unit)), 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function bayarUnit($id): JsonResponse
    {
        try {
            $user = Auth::user();
            $unit = Unit::findOrFail($id);

            if (!$unit) {
                return ApiResponse::error('Unit not found', 404);
            }

            $dueDate = Carbon::createFromFormat('Y-m-d', $unit->tanggal_jatuh_tempo);
            $currentDate = Carbon::now();
            $monthsDifference = $currentDate->diffInMonths($dueDate);
            $isLate = $currentDate->gte($dueDate) ? 1 : 0;
            $payment_no = $unit->payment_no + 1;
            if($unit->status == 'paid_off'){
                return ApiResponse::error('Payment failed, this unit already paid off', 404);
            }
                // if ($currentDate->gte($dueDate)) {
                    ListPayment::create([
                        'unit_id' => $unit->id,
                        'user_id' => $user->id,
                        'payment_date' => $currentDate,
                        'due_date' => $unit->tanggal_jatuh_tempo,
                        'isLate' => $isLate
                    ]);
                    $unit->payment_no = $payment_no;
                    if($payment_no == $unit->tenor){
                        if($unit->purchase_type == 'angsuran'){
                            $unit->status = 'paid_off';
                        }else{
                            $unit->status = 'empty';
                            $unit->nama_penghuni = null;
                            $unit->no_identitas = null;
                            $unit->alamat = null;
                            $unit->provinsi = null;
                            $unit->kota = null;
                            $unit->kode_pos = null;
                            $unit->tanggal_mulai = null;
                            $unit->kota = null;
                            $unit->purchase_type = 'sewa';
                            $unit->payment_no = 0;
                            $unit->tenor = 0;
                            ListIdleProperty::create([
                                'unit_id' => $unit->id,
                                'user_id' => $user->id,
                            ]);
                        }
                        $unit->tanggal_jatuh_tempo = null;
                        $unit->tanggal_lunas = $currentDate;
                    }else{
                        $unit->tanggal_jatuh_tempo = $this->calculateDueDate($unit->tanggal_jatuh_tempo, $unit->periode_pembayaran);
                    }
                    $unit->save();
                // } else {
                //     return ApiResponse::error('Payment failed', 404);
                // }

            return response()->json(ApiResponse::success('Payment Successfully !', new UnitResource($unit)), 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }


    public function deleteUnit(Request $request): JsonResponse
    {
        try {
            $unit = Unit::findOrFail($request->id);

            if (!$unit) {
                return ApiResponse::error('Unit not found', 400);
            }

            $unit->delete();

            return response()->json(ApiResponse::success('Unit deleted successfully'), 200);
        } catch (QueryException $e) {
            // Handle any database errors
            return ApiResponse::error('Failed to delete Perumahan', 500);
        }
    }
}