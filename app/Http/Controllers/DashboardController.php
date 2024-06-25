<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\DashboardResource;
use App\ListIdleProperty;
use App\ListPayment;
use App\Unit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function countOverdueItems($data)
    {
        $countOverdue = 0;

        foreach ($data as $item) {
            if ($item->isLate) {
                $countOverdue++;
            }
        }

        return $countOverdue;
    }

    public function getCalculation(Request $request): JsonResponse
    {
        try {
            $userId = Auth::user()->id;
            $units = Unit::where('user_id', $userId)->get();
            $listPayments = ListPayment::where('user_id', $userId)->get();
            $listIdleProperties = ListIdleProperty::where('user_id', $userId)->get();

            if ($units->isEmpty()) {
                return ApiResponse::error('Unit not found', 404);
            }

            $data['total_properties'] = $units->groupBy('kode_unit')->count();
            $data['total_units'] = $units->count();
            $data['available_units'] = $units->where('status', 'empty')->count();
            $data['filled_units'] = $units->where('status', 'filled')->count();
            $data['late_payment_count'] = $this->countOverdueItems($listPayments);

            // dd($listIdleProperties->toArray());

            // Get the current year
            $currentYear = date('Y');
            // Initialize an array to store the counts for the current year and 5 years before it
            $years = [];
            for ($i = 0; $i < 6; $i++) {
                $years[] = $currentYear - $i;
            }

            // Reverse the array to get the years in descending order
            $years = array_reverse($years);
            // Initialize an array to store the counts for the current year and 5 years before it
            $paymentLateCounts = [];
            $idlePropertyCountsPerYear = [];

            if (!empty($listPayments)) {
                // Initialize an array to store the counts per year
                $paymentCounts = [];
                // Loop over the payments and count payments per year
                foreach ($listPayments as $payment) {
                    // check if pembayaran is late or not
                    if ($payment->isLate) {
                        $paymentDate = Carbon::parse($payment->payment_date);
                        $paymentYear = $paymentDate->year;
                        if (!isset($paymentCounts[$paymentYear])) {
                            $paymentCounts[$paymentYear] = 0;
                        }
                        $paymentCounts[$paymentYear]++;
                    }
                }

                // dd($years);

                foreach ($years as $year) {
                    $paymentLateCounts[$year] = $paymentCounts[$year] ?? 0;
                }
            }

            if (!empty($listIdleProperties)) {
                $idlePropertyCounts = [];

                // Loop over the listIdleProperty collection and count properties per year
                foreach ($listIdleProperties as $property) {
                    $propertyYear = Carbon::parse($property->created_at)->year;
                    if (!isset($idlePropertyCounts[$propertyYear])) {
                        $idlePropertyCounts[$propertyYear] = 0;
                    }
                    $idlePropertyCounts[$propertyYear]++;
                }

                foreach ($years as $year) {
                    $idlePropertyCountsPerYear[$year] = $idlePropertyCounts[$year] ?? 0;
                }
            }
            $years = array_map(function ($year) {
                return (string)$year;
            }, $years);

            $data['labels'] = $years;
            $data['list_payment_late'] = $paymentLateCounts;
            $data['list_empty_properties'] = $idlePropertyCountsPerYear;

            $resource = new DashboardResource($data);

            return response()->json(ApiResponse::success('Data fetched successfully', $resource));
        } catch (\Exception $e) {
            // Handle the exception, log it, and return an appropriate response
            return ApiResponse::error('Internal Server Error' . $e->getMessage(), 500);
        }
    }
}
