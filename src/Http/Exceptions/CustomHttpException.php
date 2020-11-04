<?php
namespace Levonliu\Packages\Http\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class CustomHttpException extends HttpException
{
    /**
     * @var int
     */
    protected $customCode;
    protected $errors;

    public function __construct($customCode, $message = null, $errorData = [], $statusCode = 400, \Exception $previous = null, array $headers = array(), $code = 0)
    {
        $this->customCode = $customCode;
        $this->errors = $errorData;
        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }

    /**
     * @return string
     */
    public function getCustomCode()
    {
        return $this->customCode;
    }

    /**
     * @return array
     */
    public function getErrorData()
    {
        return $this->errors;
    }
}
