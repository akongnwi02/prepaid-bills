<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 3/14/20
 * Time: 6:42 PM
 */

namespace App\Exceptions;

class BadRequestException extends \Exception
{
    public $status = 400;
    
    public function __construct($message = 'Invalid inputs')
    {
        parent::__construct($message);
    }
    
    public function status()
    {
        return $this->status;
    }
    
    public function errors()
    {
    
    }
}