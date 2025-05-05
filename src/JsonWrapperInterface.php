<?php

namespace Sergey144010\Json;

use Sergey144010\Data\Strategy\StrategyInterface;

interface JsonWrapperInterface
{
    public function encode(
        array $array
    ): string;

    public function decode(
        string $jsonString,
        bool $associative = false,
        int $flags = JSON_THROW_ON_ERROR,
        callable $onDecodeFail = null
    ): mixed;

    public function toArray(
        string $jsonString,
        callable $onDecodeFail = null
    ): mixed;

    public function toObject(
        string $jsonString,
        string $class = null,
        callable $onDecodeFail = null,
        StrategyInterface $strategy = null,
        array|callable $injectionMap = null,
    ): mixed;
}
