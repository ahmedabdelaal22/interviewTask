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
    /**
     * The function returns an API response with data, status, message, errors, and pagination.
     * 
     * @param data The data that needs to be returned in the API response. It can be an array, object
     * or any other data type.
     * @param message A string message that describes the response. It can be used to provide
     * additional information about the response data or to indicate any errors or issues that occurred
     * during the request.
     * @param code HTTP status code to be returned in the response.
     * @param errors An array of errors that occurred during the API request. This can include
     * validation errors, authentication errors, or any other errors that may have occurred.
     * 
     * @return The function `apiResponse` returns a response object with an array containing the
     * following keys: `data`, `status`, `message`, `errors`, and `pagination`. The `data` key contains
     * the data to be returned, the `status` key indicates whether the response is successful or not,
     * the `message` key contains a message to be returned, the `errors` key contains any errors
     */
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
    /**
     * This function returns an API response with a 422 status code and optional error messages.
     * 
     * @param messages The  parameter is an optional parameter that can be passed to the
     * apiValidation() function. It is used to provide additional error messages or details about why
     * the API request failed. If provided, these messages will be included in the response returned by
     * the function.
     * 
     * @return an API response with an empty data array, an empty message string, a status code of 422
     * (which typically indicates a validation error), and an optional messages parameter that can be
     * used to provide additional error messages.
     */
    public function apiValidation($messages = "")
    {
        return $this->apiResponse([], "", 422, $messages);
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

    /**
     * This is a PHP function that returns an API response with a default error message if no message
     * is provided.
     * 
     * @param textError The parameter `` is a string that represents the error message to be
     * returned in case of an unknown error. If the parameter is not provided, the default value
     * `'problem'` will be used. The function returns an API response with an empty data array, the
     * error message, and a
     * 
     * @return This function is returning an API response with an empty data array, a default error
     * message of "problem" (if no error message is provided as an argument), and a status code of 400.
     */
    public function unKnowError($textError = null)
    {
        return $this->apiResponse([], $textError ?? 'problem', 400);
    }

    public function getErrorCode($input)
    {
        if ($input == "first_name" || $input == "last_name")
            return 'E0011';

        else if ($input == "password")
            return 'E002';

        else if ($input == "email")
            return 'E003';
        else
            return "";
    }
}
