<?php

namespace Denimsoft\FsNotify\Exception;

use RuntimeException;
use Throwable;

class ShutdownException extends RuntimeException implements FsNotifyException
{
    /**
     * @var Throwable[]
     */
    private $exceptions;

    public function __construct(string $message = '', int $code = 0, array $exceptions)
    {
        parent::__construct($message, $code, reset($exceptions));

        $this->exceptions = $exceptions;
    }

    /**
     * @param Throwable[] $exceptions
     *
     * @return ShutdownException
     */
    public static function create(array $exceptions): ShutdownException
    {
        return new static('', 0, $exceptions);
    }

    /**
     * @return Throwable[]
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }
}
