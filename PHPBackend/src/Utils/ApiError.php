<?php

namespace App\Utils;

class ApiError
{
    public $statusCode;
    public $message;
    public $errors;
    public $stack;
    public $success;

    public function __construct($statusCode, $message = "Something went wrong", $errors = [], $stack = "")
    {
        $this->statusCode = $statusCode;
        $this->message = $message;
        $this->errors = $errors;
        $this->stack = $stack;
        $this->success = false;
    }

    public function toArray()
    {
        return [
            "statusCode" => $this->statusCode,
            "message" => $this->message,
            "errors" => $this->errors,
            "stack" => $this->stack,
            "success" => $this->success
        ];
    }
}
