<?php

namespace App\Traits;

trait GeneralTrait
{

    public function getCurrentLang()
    {
        return app()->getLocale();
    }

    public function returnError($errNum, $msg)
    {
        return response()->json([
            'status' => false,
            'errNum' => $errNum,
            'msg' => $msg
        ]);
    }

    public function apiResponse($data = [], $message = "", $code = 200, $errors = [])
    {
        $array = [
            'data' => $data,
            'status' => in_array($code, $this->successCode()) ? 1 : 0,
            'message' => $message,
            'errors' => $errors,
            'pagination' => $this->paginationResponse($data)
        ];
        return response($array, $code);
    }

    public function successCode()
    {
        return [
            200, 201, 202
        ];
    }


    public function paginationResponse($data = [])
    {

            $pagination = [];
    
        return $pagination;
    }

    public function unauthorizedResponse($textError = "")
    {
        return $this->apiResponse([], $textError, 401);
    }

    public function returnSuccessMessage($msg = "", $errNum = "S000")
    {
        return [
            'status' => true,
            'errNum' => $errNum,
            'msg' => $msg
        ];
    }

    public function returnData($key, $value, $msg = "")
    {
        return response()->json([
            'status' => true,
            'errNum' => "000",
            'msg' => $msg,
            $key => $value
        ]);
    }

    public function returnValidationError($code = "E001", $validator)
    {
        return $this->returnError($code, $validator->errors()->first());
    }


    public function returnCodeAccordingToInput($validator)
    {
        $inputs = array_keys($validator->errors()->toArray());
        $code = $this->getErrorCode($inputs[0]);
        return $code;
    }

    public function getErrorCode($input)
    {
        if ($input == "first_name" || $input == "last_name")
            return 'E0011';

        else if ($input == "password")
            return 'E002';

        else if ($input == "mobile")
            return 'E003';
        else
            return "";
    }


}
