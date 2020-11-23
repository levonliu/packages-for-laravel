<?php

namespace Levonliu\Packages\Service\Request\Traits;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Arr;
use Levonliu\Packages\Service\Request\Exceptions\CustomHttpException;
use Symfony\Component\HttpFoundation\Response;

trait ResponseTraits
{

    protected $errorCodeMapping = [
        'HOTEL.GENERAL_EXCEPTION' => Response::HTTP_UNPROCESSABLE_ENTITY,
    ];

    /**
     * @param $errorBody
     * @return array
     */
    protected function parseStatusCodeAndMessage($errorBody)
    {
        $statusCode = 0;
        $message    = $errorBody;
        if ($parsed = json_decode($errorBody, TRUE)) {
            $code       = Arr::get($parsed, 'code');
            $statusCode = Arr::get($this->errorCodeMapping, $code, Response::HTTP_INTERNAL_SERVER_ERROR);
            $message    = Arr::get($parsed, 'message');

        }
        return [
            'status_code' => intval($statusCode),
            'message'     => strval($message),
            'data'        => [
                'json'     => $parsed,
                'original' => $errorBody,
            ]
        ];
    }


    /**
     * @param RequestException $exception
     */
    protected function parseException($exception)
    {
        $response = $exception->getResponse();
        if (blank($response)) {
            throw new CustomHttpException(500, $exception->getMessage());
        }

        $errorBody = (string)$response->getBody();

        $errorBody = $this->parseStatusCodeAndMessage($errorBody);

        $statusCode = $errorBody['status_code'] ?: $response->getStatusCode();
        $message    = $errorBody['message'];
        $data       = $errorBody['data'];

        $validStatuses = array_keys(Response::$statusTexts);
        $code          = in_array($statusCode, $validStatuses) ? $statusCode : 500;
        throw new CustomHttpException($code, $message ?: Response::$statusTexts[$code], $data, $code);
    }

    protected function responseOk($statusCode)
    {
        return $statusCode >= Response::HTTP_OK && $statusCode < Response::HTTP_MULTIPLE_CHOICES;
    }
}
