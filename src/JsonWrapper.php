<?php

namespace Sergey144010\Json;

use Sergey144010\Data\Data;
use Sergey144010\Data\Strategy\StrategyInterface;

final class JsonWrapper implements JsonWrapperInterface
{
    public function __construct(
        readonly private Data $data
    ) {
    }

    public function encode(
        array $array
    ): string {
        return json_encode($array, JSON_THROW_ON_ERROR);
    }

    public function decode(
        string $jsonString,
        bool $associative = false,
        int $flags = JSON_THROW_ON_ERROR,
        callable $onDecodeFail = null
    ): mixed {
        try {
            return json_decode(
                json: $jsonString,
                associative: $associative,
                flags: $flags,
            );
        } catch (\Throwable $exception) {
            if (isset($onDecodeFail)) {
                return $onDecodeFail($exception);
            }
        }

        throw new JsonException('Json decode failed');
    }

    public function toArray(
        string $jsonString,
        callable $onDecodeFail = null
    ): mixed {
        return $this->decode(
            jsonString: $jsonString,
            associative: true,
            onDecodeFail: $onDecodeFail
        );
    }

    /**
     * @template T
     *
     * @param string $jsonString
     * @param class-string<T>|null $class
     * @param callable|null $onDecodeFail
     * @param StrategyInterface|null $strategy
     * @param array|callable $injectionMap
     * @return T
     */
    public function toObject(
        string $jsonString,
        string $class = null,
        callable $onDecodeFail = null,
        StrategyInterface $strategy = null,
        array|callable $injectionMap = null,
    ): mixed {
        if (! isset($class)) {
            return $this->decode(
                jsonString: $jsonString,
                associative: false,
                onDecodeFail: $onDecodeFail
            );
        }

        $data = $this->decode(
            jsonString: $jsonString,
            associative: true,
            onDecodeFail: $onDecodeFail
        );

        if (is_callable($injectionMap)) {
            $injectionMap = $injectionMap($data);
        }

        if (! isset($strategy)) {
            /** @var T $object */
            $object = $this->data->toObject(
                data: $data,
                class: $class,
                injectionMap: $injectionMap ?? [],
            );

            return $object;
        }

        /** @var T $object */
        $object = $this->data->toObject(
            data: $data,
            class: $class,
            strategy: $strategy,
            injectionMap: $injectionMap ?? [],
        );

        return $object;
    }
}
