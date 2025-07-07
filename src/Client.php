<?php

declare(strict_types=1);

namespace Keboola\NotificationClient;

use Closure;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use JsonException;
use Keboola\NotificationClient\Exception\ClientException as NotificationClientException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validation;
use Throwable;

abstract class Client
{
    private const JSON_DEPTH = 512;
    protected string $tokenHeaderName = '';
    protected GuzzleClient $guzzle;

    /**
     * @param array{
     *     handler?: HandlerStack,
     *     backoffMaxTries: int<0, 100>,
     *     userAgent: string,
     *     logger?: LoggerInterface
     * } $options
     */
    public function __construct(
        string $baseUrl,
        ?string $token,
        array $options,
    ) {
        $validator = Validation::createValidator();
        $errors = $validator->validate($baseUrl, [new Url()]);
        $errors->addAll($validator->validate(
            // @phpstan-ignore-next-line
            $options['backoffMaxTries'] ?? null,
            [new NotBlank(null, '"backoffMaxTries" option must be provided')],
        ));
        $errors->addAll($validator->validate(
            // @phpstan-ignore-next-line
            $options['backoffMaxTries'] ?? null,
            [new Range(['min' => 0, 'max' => 100])],
        ));
        $errors->addAll($validator->validate(
            // @phpstan-ignore-next-line
            $options['userAgent'] ?? null,
            [new NotBlank(null, '"userAgent" option must be provided')],
        ));
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

    private function createDefaultDecider(int $maxRetries, LoggerInterface $logger): Closure
    {
        return function (
            $retries,
            RequestInterface $request,
            ?ResponseInterface $response = null,
            ?Throwable $error = null,
        ) use (
            $maxRetries,
            $logger,
        ) {
            if ($retries >= $maxRetries) {
                $logger->notice(sprintf('We have tried this %d times. Giving up.', $maxRetries));
                return false;
            } elseif ($response && $response->getStatusCode() >= 500) {
                $logger->notice(sprintf(
                    'Got a %s response for this reason: %s, retrying.',
                    $response->getStatusCode(),
                    $response->getReasonPhrase(),
                ));
                return true;
            } elseif ($error && $error->getCode() >= 500) {
                $logger->notice(sprintf(
                    'Got a %s error with this message: %s, retrying.',
                    $error->getCode(),
                    $error->getMessage(),
                ));
                return true;
            } else {
                return false;
            }
        };
    }

    /**
     * @param array{
     *     handler?: HandlerStack,
     *     backoffMaxTries: int<0, 100>,
     *     userAgent: string,
     *     logger?: LoggerInterface
     * } $options
     */
    private function initClient(string $url, ?string $token, array $options): GuzzleClient
    {
        // Initialize handlers (start with those supplied in constructor)
        $handlerStack = HandlerStack::create($options['handler'] ?? null);

        // Set client logger
        if (isset($options['logger'])) {
            $handlerStack->push(Middleware::log(
                $options['logger'],
                new MessageFormatter(
                    '{hostname} {req_header_User-Agent} - [{ts}] "{method} {resource} {protocol}/{version}"' .
                    ' {code} {res_header_Content-Length}',
                ),
                'debug',
            ));
            $logger = $options['logger'];
        } else {
            $logger = new NullLogger();
        }

        // Set exponential backoff
        $handlerStack->push(Middleware::retry($this->createDefaultDecider($options['backoffMaxTries'], $logger)));
        $headers['User-Agent'] = $options['userAgent'];
        if ($this->tokenHeaderName) {
            $headers[$this->tokenHeaderName] = (string) $token;
        }

        // finally create the instance
        return new GuzzleClient([
            'base_uri' => $url,
            'handler' => $handlerStack,
            RequestOptions::CONNECT_TIMEOUT => 10,
            RequestOptions::TIMEOUT => 120,
            'headers' => $headers,
        ]);
    }

    protected function sendRequest(Request $request): array
    {
        try {
            $response = $this->guzzle->send($request);
            $body = $response->getBody()->getContents();
            if ($body === '') {
                return [];
            }
            return (array) json_decode($body, true, self::JSON_DEPTH, JSON_THROW_ON_ERROR);
        } catch (GuzzleException $e) {
            throw new NotificationClientException($e->getMessage(), $e->getCode(), $e);
        } catch (JsonException $e) {
            throw new NotificationClientException('Unable to parse response body into JSON: ' . $e->getMessage());
        }
    }
}
