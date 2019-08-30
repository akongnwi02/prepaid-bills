<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 8/29/19
 * Time: 8:49 AM
 */

namespace App\Exceptions;


use Throwable;

class ResourceNotFoundException extends \Exception
{
    public $errors;

    public $value;

    public $resource;

    public $status = 404;

    public function __construct(string $resource, string $value)
    {
        $this->value = $value;
        $this->resource = class_basename($resource);

        $message = "Resource [$this->value] of type [$this->resource] is not found";

        parent::__construct($message);
    }

    public function errors()
    {
        return [$this->value => [
           'not_found',
        ]];
    }

    public function status()
    {
        return $this->status;
    }

}