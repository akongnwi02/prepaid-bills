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

    public function __construct($message = 'Not found')
    {
        parent::__construct($message);
    }
    
    public function status()
    {
        return $this->status;
    }

}