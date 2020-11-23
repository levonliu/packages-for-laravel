<?php
namespace Levonliu\Packages\Service\Request\Client;

use GuzzleHttp\Exception\ServerException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class AsyncRequest extends Request
{
    public function sendGetRequestAsync($path, $headers = [])
    {
        return $this->sendRequestAsync(HttpRequest::METHOD_GET, $path, $headers);
    }

    public function sendPostRequestAsync($path, $headers = [])
    {
        return $this->sendRequestAsync(HttpRequest::METHOD_POST, $path, $headers);
    }

    public function sendPatchRequestAsync($path, $headers = [])
    {
        return $this->sendRequestAsync(HttpRequest::METHOD_PATCH, $path, $headers);
    }

    public function sendDeleteRequestAsync($path, $headers = [])
    {
        return $this->sendRequestAsync(HttpRequest::METHOD_DELETE, $path, $headers);
    }

    public function sendPutRequestAsync($path, $headers = [])
    {
        return $this->sendRequestAsync(HttpRequest::METHOD_PUT, $path, $headers);
    }


    public function sendRequestAsync($method, $path, $headers = [])
    {
        $request = $this->buildRequest($method, $path, $headers);

        return $this->httpClient->sendAsync($request, $this->options($method))
            ->then(function (ResponseInterface $response) use ($request) {
                return $response;
            }, function (ServerException $exception) {
                return $exception->getResponse();
            });
    }

}
