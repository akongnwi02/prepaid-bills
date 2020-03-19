<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 3/18/20
 * Time: 11:13 PM
 */

namespace App\Exceptions;


class ForbiddenException extends \Exception
{
    public $status = 403;
    
    public function __construct($message = 'The meter code may have been deactivated')
    {
        parent::__construct($message);
    }
    
    public function status()
    {
        return $this->status;
    }
}