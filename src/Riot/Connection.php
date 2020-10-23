<?php

declare(strict_types=1);

namespace Riot;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Riot\Exception\BadGatewayException;
use Riot\Exception\BadRequestException;
use Riot\Exception\DataNotFoundException;
use Riot\Exception\ForbiddenException;
use Riot\Exception\GatewayTimeoutException;
use Riot\Exception\InternalServerErrorException;
use Riot\Exception\MethodNotAllowedException;
use Riot\Exception\RateLimitExceededException;
use Riot\Exception\ServiceUnavailableException;
use Riot\Exception\UnauthorizedException;
use Riot\Exception\UnsupportedMediaTypeException;

final class Connection implements ConnectionInterface
{
    private const API_URL = 'api.riotgames.com';

    private ClientInterface $client;

    private string $riotApiToken;

    private RequestFactoryInterface $requestFactory;

    public function __construct(ClientInterface $riotClient, string $riotApiToken, RequestFactoryInterface $requestFactory)
    {
        $this->client = $riotClient;
        $this->riotApiToken = $riotApiToken;
        $this->requestFactory = $requestFactory;
    }

    public function get(string $region, string $path): ResponseInterface
    {
        $request = $this->requestFactory->createRequest(
            'GET',
            sprintf('https://%s.%s/%s', $region, self::API_URL, $path),
        );
        $request = $request->withAddedHeader('X-Riot-Token', $this->riotApiToken);

        $response = $this->client->sendRequest($request);
        if (self::STATUS_CODE_OK !== $response->getStatusCode()) {
            $this->statusCodeToException($response);
        }

        return $response;
    }

    public function getAsDecodedArray(string $region, string $path): array
    {
        $response = $this->get($region, $path);

        $body = $response->getBody()->getContents();

        return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws BadGatewayException
     * @throws BadRequestException
     * @throws DataNotFoundException
     * @throws ForbiddenException
     * @throws GatewayTimeoutException
     * @throws InternalServerErrorException
     * @throws MethodNotAllowedException
     * @throws RateLimitExceededException
     * @throws ServiceUnavailableException
     * @throws UnauthorizedException
     * @throws UnsupportedMediaTypeException
     */
    private function statusCodeToException(ResponseInterface $response): void
    {
        switch ($response->getStatusCode()) {
            case self::STATUS_CODE_BAD_REQUEST:
                throw BadRequestException::createFromResponse('Bad request', $response);
            case self::STATUS_CODE_UNAUTHORIZED:
                throw UnauthorizedException::createFromResponse('Unauthorized', $response);
            case self::STATUS_CODE_FORBIDDEN:
                throw ForbiddenException::createFromResponse('Forbidden', $response);
            case self::STATUS_CODE_DATA_NOT_FOUND:
                throw DataNotFoundException::createFromResponse('Data not found', $response);
            case self::STATUS_CODE_METHOD_NOT_ALLOWED:
                throw MethodNotAllowedException::createFromResponse('Method not allowed', $response);
            case self::STATUS_CODE_UNSUPPORTED_MEDIA_TYPE:
                throw UnsupportedMediaTypeException::createFromResponse('Unsupported media type', $response);
            case self::STATUS_CODE_LIMIT_EXCEEDED:
                throw RateLimitExceededException::createFromResponse('Rate limit exceeded', $response);
            case self::STATUS_CODE_INTERNAL_SERVER_ERROR:
                throw InternalServerErrorException::createFromResponse('Internal server error', $response);
            case self::STATUS_CODE_BAD_GATEWAY:
                throw BadGatewayException::createFromResponse('Bad gateway', $response);
            case self::STATUS_CODE_SERVICE_UNAVAILABLE:
                throw ServiceUnavailableException::createFromResponse('Service unavailable', $response);
            case self::STATUS_CODE_GATEWAY_TIMEOUT:
                throw GatewayTimeoutException::createFromResponse('Gateway timeout', $response);
        }
    }
}
