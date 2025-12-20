<?php

namespace Tigusigalpa\Dropbox\Exceptions;

use Exception;

class DropboxException extends Exception
{
    protected ?array $response;

    public function __construct(string $message = "", int $code = 0, ?array $response = null)
    {
        parent::__construct($message, $code);
        $this->response = $response;
    }

    public function getResponse(): ?array
    {
        return $this->response;
    }

    public function getErrorSummary(): ?string
    {
        return $this->response['error_summary'] ?? null;
    }

    public function getErrorTag(): ?string
    {
        return $this->response['error']['.tag'] ?? null;
    }
}
