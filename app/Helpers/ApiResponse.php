<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($message = 'Success', $data = null)
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }

    public static function error($message, $status = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
