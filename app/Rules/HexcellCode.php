<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Class UnusedPassword.
 */
class HexcellCode implements Rule
{
    /**
     * @var
     */
    protected $uuid;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match(config('app.meter_code_regex'), $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'validation.invalid';
    }
}