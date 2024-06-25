<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\ListPaymentResource;
use App\ListPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListPaymentController extends Controller
{
    public function getListPayments(Request $request): JsonResponse
    {
        try {
            $userId = Auth::user()->id;
            $listPayments = ListPayment::with(['unit.perumahan', 'unit.kontrakan', 'unit.kostan'])->where('user_id', $userId)->orderBy('payment_date', 'desc')->get();

            return response()->json(ApiResponse::success('Data fetched successfully', ListPaymentResource::collection($listPayments)));
        } catch (\Exception $e) {
            // Handle the exception, log it, and return an appropriate response
            return ApiResponse::error('Internal Server Error' . $e->getMessage(), 500);
        }
    }

    public function getListPaymentsByKeyword(Request $request): JsonResponse
    {
        try {
            $userId = Auth::user()->id;

            $query = ListPayment::with(['unit.perumahan', 'unit.kontrakan', 'unit.kostan'])
                ->where('user_id', $userId);

            if ($request->filled('keyword')) {
                $keyword = (string) $request->keyword;

                $query->Where('payment_date', 'like', '%' . $keyword . '%')->orWhere(function ($query) use ($keyword) {
                    $query->whereHas('unit', function ($query) use ($keyword) {
                        $query->where('name', 'like', '%' . $keyword . '%');
                    })->orWhereHas('unit.perumahan', function ($query) use ($keyword) {
                        $query->where('type', 'perumahan')
                            ->where(function ($query) use ($keyword) {
                                $query->where('kode_unit', 'like', '%' . $keyword . '%')
                                    ->orWhere('name', 'like', '%' . $keyword . '%');
                            });
                    })->orWhereHas('unit.kontrakan', function ($query) use ($keyword) {
                        $query->where('type', 'kontrakan')
                            ->where(function ($query) use ($keyword) {
                                $query->where('kode_unit', 'like', '%' . $keyword . '%')
                                    ->orWhere('name', 'like', '%' . $keyword . '%');
                            });
                    })->orWhereHas('unit.kostan', function ($query) use ($keyword) {
                        $query->where('type', 'kostan')
                            ->where(function ($query) use ($keyword) {
                                $query->where('kode_unit', 'like', '%' . $keyword . '%')
                                    ->orWhere('name', 'like', '%' . $keyword . '%');
                            });
                    });
                });
            }

            $listPayments = $query->orderBy('payment_date', 'desc')->get();

            return response()->json(ApiResponse::success('Data fetched successfully', ListPaymentResource::collection($listPayments)));
        } catch (\Exception $e) {
            // Handle the exception, log it, and return an appropriate response
            return ApiResponse::error('Internal Server Error' . $e->getMessage(), 500);
        }
    }
}
