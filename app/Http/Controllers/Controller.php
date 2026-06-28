<?php

namespace App\Http\Controllers;

class Controller extends \Illuminate\Routing\Controller
{
    public function sendResponse($data = null, $message = 'Success', $status = 200){
        $response = [
            'status' => 'success',
            'message' => $message
        ];
        if(!empty($data)){
            $response['data'] = $data;
        }
        return response()->json($response, $status);
    }
    public function sendError($message, $errors = null, $status = 400){
        $response = [
            'status'=> 'error',
            'message'=> $message
        ];
        if(!empty($errors)){
            $response['errors'] = $errors;
        }
        return response()->json($response, $status);
    }
}
