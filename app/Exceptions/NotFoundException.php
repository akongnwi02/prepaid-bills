<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 8/29/19
 * Time: 8:49 AM
 */

namespace App\Exceptions;

class NotFoundException extends \Exception
{
    public $errors;
    
    public $status = 404;
    
    public $resource;

    public function __construct($resource = null, $value = null)
    {
        $this->resource = $resource;
        parent::__construct("The $resource is not found $value");
    }
    
    public function status()
    {
        return $this->status;
    }
    
    public function error()
    {
        return [
            $this->resource => [
                'Not found'
            ]
         ];
    }
}