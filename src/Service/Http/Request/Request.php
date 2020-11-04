<?php

namespace Levonliu\Packages\Service\Http\Request;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;
use Levonliu\Packages\Service\Http\Traits\RequestTraits;
use Levonliu\Packages\Service\Http\Traits\ResponseTraits;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class Request
{

    use RequestTraits, ResponseTraits;

    /**
     * @var GuzzleHttpClient
     */
    protected $httpClient;

    public function __construct()
    {
        $this->httpClient = $this->buildTestClient();
    }

    protected function buildTestClient()
    {
        $handler = \GuzzleHttp\choose_handler();
        $stack   = HandlerStack::create($handler);

        $stack->push(Middleware::mapRequest(function (RequestInterface $request) use ($stack) {
            if ($request->getMethod() == 'POST') {
                $request->withHeader('Content-type', 'application/x-www-form-urlencoded');
            }
            return $request->withHeader('Accept', 'application/json');
        }));

        return new GuzzleHttpClient([
            'base_uri' => config('request.base_uri'),
            'timeout'  => config('request.curl_timeout'),
            'handler'  => $stack,
            'verify'   => config('levon.request.verify'),
        ]);
    }

    /**
     * @return GuzzleHttpClient
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    public function sendGetRequest($path, $headers = [])
    {
        return $this->sendRequest(HttpRequest::METHOD_GET, $path, $headers);
    }

    public function sendPostRequest($path, $headers = [])
    {
        return $this->sendRequest(HttpRequest::METHOD_POST, $path, $headers);
    }

    public function sendPatchRequest($path, $headers = [])
    {
        return $this->sendRequest(HttpRequest::METHOD_PATCH, $path, $headers);
    }

    public function sendDeleteRequest($path, $headers = [])
    {
        return $this->sendRequest(HttpRequest::METHOD_DELETE, $path, $headers);
    }

    public function sendPutRequest($path, $headers = [])
    {
        return $this->sendRequest(HttpRequest::METHOD_PUT, $path, $headers);
    }


    public function sendRequest($method, $path, $headers)
    {
        try {
            $request = $this->buildRequest($method, $path, $headers);
            /** @var Response $response */
            $response = $this->httpClient->send($request, $this->options($method));
            if ($this->responseOk($response->getStatusCode())) {
                return $this->parseBody($response);
            }
            return $response->getReasonPhrase();
        } catch (HttpException $httpException) {
            $this->logRequestException($path, $httpException);
            throw $httpException;
        } catch (ServerException $exception) {
            $this->logRequestException($path, $exception);
            return $this->parseException($exception);
        } catch (RequestException $exception) {
            $this->logRequestException($path, $exception);
            return $this->parseException($exception);
        }
    }

    protected function buildRequest($method, $path, $headers)
    {
        return new \GuzzleHttp\Psr7\Request(
            $method,
            $this->buildUrl($method, $path),
            $this->headers($method, $headers)
        );
    }

    protected function buildUrl($method, $path)
    {
        $url = $path;
        if (HttpRequest::METHOD_GET === $method && count($this->parameters) > 0) {
            $url = $path . '?' . http_build_query($this->parameters);
        }
        return $url;
    }

    protected function headers($method, $headers = [])
    {
        return $headers;
    }

    protected function options($method)
    {
        $options = [];

        if (HttpRequest::METHOD_GET !== $method) {
            $options[RequestOptions::BODY] = json_encode($this->parameters, JSON_UNESCAPED_UNICODE);
        }

        return $options;
    }

    /**
     * @param ResponseInterface $response
     * @return mixed
     */
    protected function parseBody(ResponseInterface $response)
    {
        $contents = (string)$response->getBody();

        return json_decode($this->replaceUfeff($contents), TRUE);
    }

    public static function replaceUfeff($text)
    {
        if (blank($text)) {
            return $text;
        }

        $text = json_encode($text, JSON_UNESCAPED_UNICODE);
        $text = str_replace("\\ufeff", " ", $text);

        return json_decode($text, TRUE);
    }

    protected function batch($requests, \Closure $fulfilled = NULL, \Closure $rejected = NULL)
    {
        $results = [];

        $p = new \GuzzleHttp\Pool($this->httpClient, $requests, [
            'fulfilled' => function (ResponseInterface $response, $index) use (&$results, $fulfilled) {
                if ($fulfilled) {
                    $fulfilled($results, $response, $index);
                } else {
                    $results[$index] = $this->parseBody($response);
                }
            },
            'rejected'  => $rejected ?: function ($e) {
                throw  $e;
            }
        ]);

        $p->promise()->wait();

        return $results;
    }

    private function logRequestException($path, \Throwable $throwable)
    {
        Log::error("Request: {$path} error[" . $throwable->getMessage() . ']');
    }

}
