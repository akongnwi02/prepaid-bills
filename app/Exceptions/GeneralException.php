<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 8/29/19
 * Time: 8:49 AM
 */

namespace App\Exceptions;

class GeneralException extends \Exception
{
    public $status = 500;

    public function __construct($message = 'An unexpected error occurred')
    {
        parent::__construct($message);
    }

    public function status()
    {
        return $this->status;
    }
}