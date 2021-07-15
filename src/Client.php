<?php

declare(strict_types=1);

namespace Keboola\NotificationClient;

use Closure;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use JsonException;
use Keboola\NotificationClient\Exception\ClientException as NotificationClientException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validation;

abstract class Client
{
    private const DEFAULT_USER_AGENT = 'Notification PHP Client';
    private const DEFAULT_BACKOFF_RETRIES = 3;
    private const JSON_DEPTH = 512;

    protected GuzzleClient $guzzle;

    public function __construct(
        string $baseUrl,
        ?string $token,
        array $options = []
    ) {
        $validator = Validation::createValidator();
        $errors = $validator->validate($baseUrl, [new Url()]);
        if (!empty($options['backoffMaxTries'])) {
            $errors->addAll($validator->validate($options['backoffMaxTries'], [new Range(['min' => 0, 'max' => 100])]));
            $options['backoffMaxTries'] = intval($options['backoffMaxTries']);
        } else {
            $options['backoffMaxTries'] = self::DEFAULT_BACKOFF_RETRIES;
        }
        if (empty($options['userAgent'])) {
            $options['userAgent'] = self::DEFAULT_USER_AGENT;
        }
        if ($errors->count() !== 0) {
            $messages = '';
            /** @var ConstraintViolationInterface $error */
            foreach ($errors as $error) {
                $messages .= 'Value "' . $error->getInvalidValue() . '" is invalid: ' . $error->getMessage() . "\n";
            }
            throw new NotificationClientException('Invalid parameters when creating client: ' . $messages);
        }
        $this->guzzle = $this->initClient($baseUrl, $token, $options);
    }

    private function createDefaultDecider(int $maxRetries): Closure
    {
        return function (
            $retries,
            RequestInterface $request,
            ?ResponseInterface $response = null,
            $error = null
        ) use ($maxRetries) {
            if ($retries >= $maxRetries) {
                return false;
            } elseif ($error && $error->getCode() >= 500) {
                return true;
            } else {
                return false;
            }
        };
    }

    abstract protected function getTokenHeaderName(): ?string;

    private function initClient(string $url, ?string $token, array $options = []): GuzzleClient
    {
        // Initialize handlers (start with those supplied in constructor)
        $handlerStack = HandlerStack::create($options['handler'] ?? null);
        // Set exponential backoff
        $handlerStack->push(Middleware::retry($this->createDefaultDecider($options['backoffMaxTries'])));
        // Set handler to set default headers
        $handlerStack->push(Middleware::mapRequest(
            function (RequestInterface $request) use ($token, $options) {
                $request = $request
                    ->withHeader('User-Agent', $options['userAgent'])
                    ->withHeader('Content-type', 'application/json');
                if ($this->getTokenHeaderName()) {
                    $request = $request->withHeader((string) $this->getTokenHeaderName(), (string) $token);
                }
                return $request;
            }
        ));
        // Set client logger
        if (isset($options['logger']) && $options['logger'] instanceof LoggerInterface) {
            $handlerStack->push(Middleware::log(
                $options['logger'],
                new MessageFormatter(
                    '{hostname} {req_header_User-Agent} - [{ts}] "{method} {resource} {protocol}/{version}"' .
                    ' {code} {res_header_Content-Length}'
                )
            ));
        }
        // finally create the instance
        return new GuzzleClient(['base_uri' => $url, 'handler' => $handlerStack]);
    }

    protected function sendRequest(Request $request): array
    {
        try {
            $response = $this->guzzle->send($request);
            $body = $response->getBody()->getContents();
            if ($body === '') {
                return [];
            }
            return json_decode($body, true, self::JSON_DEPTH, JSON_THROW_ON_ERROR);
        } catch (ClientException $e) {
            throw new NotificationClientException($e->getMessage(), $e->getCode(), $e);
        } catch (GuzzleException $e) {
            throw new NotificationClientException($e->getMessage(), $e->getCode(), $e);
        } catch (JsonException $e) {
            throw new NotificationClientException('Unable to parse response body into JSON: ' . $e->getMessage());
        }
    }
}
