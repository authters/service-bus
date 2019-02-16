<?php

namespace Authters\ServiceBus\Exception;

use Illuminate\Contracts\Validation\Validator;

class ValidationException extends RuntimeException
{
    /**
     * @var Validator
     */
    private static $validator;

    public static function withValidator(Validator $validator): self
    {
        self::$validator = $validator;

        $message = 'Validation rules fails:';
        $message .= $validator->errors();

        return new self($message);
    }

    public function getValidator(): Validator
    {
        return self::$validator;
    }
}