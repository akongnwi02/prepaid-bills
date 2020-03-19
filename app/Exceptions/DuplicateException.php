<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 3/18/20
 * Time: 11:39 PM
 */

namespace App\Exceptions;


class DuplicateException extends \Exception
{
    public $status = 409;
    
    public function __construct($message = 'This may be a duplicate request')
    {
        parent::__construct($message);
    }
    
    public function status()
    {
        return $this->status;
    }
}