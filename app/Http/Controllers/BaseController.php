<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class BaseController extends Controller
{
    protected function defaultResponse($code, $message, $result = null, $httpCode = 200, $errorType = null, $errorMessage = null)
    {
        if($httpCode == 200) {
            return response()->json([
                'code' => $code,
                'message' => $message,
                'result' => $result
            ], $httpCode);
        } else {
            return response()->json([
                'code' => $code,
                'message' => $message,
                'errorType' => $errorType,
                'errorMessage' => $errorMessage
            ], $httpCode);
        }
    }

    protected function executeFunction(callable $function)
    {
        try {
            DB::beginTransaction();
            $data = call_user_func($function);
            DB::commit();

            return $this->defaultResponse(200, 'Good', $data, 200, 'Good', null);
        } catch (\Throwable $th) {
            return $this->defaultResponse(500, 'Failed', null, 500, 'Error', $th->getMessage());
        }
    }
}
