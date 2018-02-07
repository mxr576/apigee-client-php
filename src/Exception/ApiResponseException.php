<?php

namespace Apigee\Edge\Exception;

use Http\Message\Formatter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ApiResponseException.
 *
 * General exception class for API response errors.
 */
class ApiResponseException extends ApiException
{
    /** @var \Psr\Http\Message\ResponseInterface */
    protected $response;
    /** @var null|string */
    protected $edgeErrorCode;

    public function __construct(
        ResponseInterface $response,
        RequestInterface $request,
        string $message = '',
        int $code = 0,
        \Throwable $previous = null,
        Formatter $formatter = null
    ) {
        $this->response = $response;
        $message = $response->getReasonPhrase();
        // Try to parse Edge error message and error code from the response body.
        $contentTypeHeader = $response->getHeaderLine('Content-Type');
        if ($contentTypeHeader && false !== strpos($contentTypeHeader, 'application/json')) {
            $array = json_decode((string) $response->getBody(), true);
            if (JSON_ERROR_NONE === json_last_error()) {
                if (array_key_exists('code', $array)) {
                    $this->edgeErrorCode = $array['code'];
                }
                if (array_key_exists('message', $array)) {
                    $message = $array['message'];
                }
            }
        }
        parent::__construct($request, $message, $code, $previous, $formatter);
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return sprintf(
            "Request:\n%s\nResponse:\n%s\n",
            $this->formatter->formatRequest($this->request),
            $this->formatter->formatResponse($this->response)
        );
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return null|string
     */
    public function getEdgeErrorCode(): ?string
    {
        return $this->edgeErrorCode;
    }
}