<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 8/29/19
 * Time: 8:49 AM
 */

namespace App\Exceptions;

class ConnectionException extends \Exception
{
    public $errors;

    public $type;

    public $operation;

    public $status = 500;

    public function __construct(string $type, string $operation)
    {
        $this->type      = $type;
        $this->operation = $operation;

        $message = "Connection to [$this->type] failed during [$this->operation]";

        parent::__construct($message);
    }

    public function status()
    {
        return $this->status;
    }

}