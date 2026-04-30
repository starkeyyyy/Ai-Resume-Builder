<?php

namespace App\Utils;

class ApiResponse
{
    public $statusCode;
    public $data;
    public $message;
    public $success;

    public function __construct($statusCode, $data, $message = "Success")
    {
        $this->statusCode = $statusCode;
        $this->data = $data;
        $this->message = $message;
        $this->success = $statusCode < 400;
    }

    public function toArray()
    {
        return [
            "statusCode" => $this->statusCode,
            "data" => $this->data,
            "message" => $this->message,
            "success" => $this->success
        ];
    }
}
