<?php

namespace Aerys\ReverseProxy;

use Aerys\Request;
use Aerys\Response;
use Amp\Artax;
use Amp\Artax\Client;
use Amp\Artax\DefaultClient;
use Amp\ByteStream;

class ReverseProxy {
    private $target;
    private $headers;
    private $client;

    public function __construct(string $uri, array $headers = [], Client $client = null) {
        $this->target = rtrim($uri, '/');

        if (is_callable($headers)) {
            $this->headers = $headers;
        } elseif (is_array($headers)) {
            foreach ($headers as $header => $values) {
                if (!is_array($values)) {
                    throw new \Error('Headers must be either callable or an array of arrays');
                }

                foreach ($values as $value) {
                    if (!is_scalar($value)) {
                        throw new \Error('Header values must be scalars');
                    }
                }
            }

            $this->headers = array_change_key_case($headers, CASE_LOWER);
        } else {
            throw new \Error('Headers must be either callable or an array of arrays');
        }

        $this->client = $client ?? new DefaultClient;
    }

    public function __invoke(Request $req, Response $res) {
        $headers = $req->getAllHeaders();

        unset($headers['accept-encoding'], $headers['connection']);

        if ($this->headers) {
            if (is_callable($this->headers)) {
                $headers = ($this->headers)($headers);
            } else {
                $headers = $this->headers + $headers;
            }
        }

        $artaxRequest = (new Artax\Request($this->target . $req->getUri(), $req->getMethod()))
            ->withHeaders($headers)
            ->withBody(yield $req->getBody());

        /** @var Artax\Response $artaxResponse */
        $artaxResponse = yield $this->client->request($artaxRequest);

        $res->setStatus($status = $artaxResponse->getStatus());
        $res->setReason($artaxResponse->getReason());

        foreach ($artaxResponse->getHeaders() as $header => $values) {
            foreach ($values as $value) {
                $res->addHeader($header, $value);
            }
        }

        yield ByteStream\pipe($artaxResponse->getBody(), $res);
    }
}
