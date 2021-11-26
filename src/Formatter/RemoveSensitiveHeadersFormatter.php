<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Formatter;

use Http\Message\Formatter as HttpFormatter;
use function preg_quote;
use Psl\Regex;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class RemoveSensitiveHeadersFormatter implements HttpFormatter
{
    private HttpFormatter $formatter;

    /**
     * @var non-empty-list<string>
     */
    private array $sensitiveHeaders;

    /**
     * @param non-empty-list<string> $sensitiveHeaders
     */
    public function __construct(HttpFormatter $formatter, array $sensitiveHeaders)
    {
        $this->formatter = $formatter;
        $this->sensitiveHeaders = $sensitiveHeaders;
    }

    public function formatRequest(RequestInterface $request): string
    {
        return $this->removeSensitiveData(
            $this->formatter->formatRequest($request)
        );
    }

    /** @psalm-suppress DeprecatedMethod */
    public function formatResponse(ResponseInterface $response): string
    {
        return $this->removeSensitiveData(
            $this->formatter->formatResponse($response)
        );
    }

    /** @psalm-suppress DeprecatedMethod MixedReturnStatement MixedInferredReturnType */
    public function formatResponseForRequest(ResponseInterface $response, RequestInterface $request): string
    {
        if (method_exists($this->formatter, 'formatResponseForRequest')) {
            return $this->removeSensitiveData(
                $this->formatter->formatResponseForRequest($response, $request)
            );
        }

        return $this->removeSensitiveData(
            $this->formatter->formatResponse($response)
        );
    }

    private function removeSensitiveData(string $info): string
    {
        return array_reduce(
            $this->sensitiveHeaders,
            /** @psalm-suppress InvalidReturnStatement, InvalidReturnType */
            fn (string $sensitiveData, string $header): string => Regex\replace(
                $sensitiveData,
                '{^('.preg_quote($header, '{').')\:(.*)}im',
                '$1: xxxx',
            ),
            $info
        );
    }
}
