<?php
namespace App\Http\Handlers;

use Illuminate\Support\Facades\Log;

class BaseHandler
{
    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @param $errors
     */
    public function setErrors($errors): void
    {
        $this->errors = is_array($errors) ? $errors : (array)$errors;
        Log::debug(sprintf('File - %s Line - %s | Error: %s', __FILE__, __LINE__, print_r($this->errors, true)));
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
