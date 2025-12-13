<?php

function generate_response($data = [], $status = 1, $error = false, $message = false)
{
    return [
        "status" => $status,
        "error" => $error != false ? $error : "",
        "message" => $message != false ? $message : "",
        "data" => $data
    ];
}