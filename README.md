# json
Json to object

Get from json named object with properties.

For more details see https://github.com/sergey144010/data

## Examples

Initialization
```
use Sergey144010\Json\JsonWrapper;

$this->json = new JsonWrapper(
    new Data(new CamelCustom())
);
```

-
```
### Input json
{"property-one": "one", "property_two": "two"}

### Named Dto
class SomeDto
{
    public function __construct(
        readonly public string $propertyOne,
        readonly public string $propertyTwo,
    ) {
    }
}

### Output

$dto = $this->json->toObject($string, SomeDto::class);

self::assertEquals('one', $dto->propertyOne);
self::assertEquals('two', $dto->propertyTwo);
```

-
```
### Input json

$data = '
{
    "one": {
        "propertyOne": "one1",
        "propertyTwo": "two1"
    },
    "two": {
        "propertyOne": "one2",
        "propertyTwo": "two2"
    }
}
';

### Dto
class RootDto
{
    public function __construct(
        readonly public SomeDto $one,
        readonly public SomeDto $two,
    ) {
    }
}

### Output

$dto = $this->json->toObject($data, RootDto::class);
echo $dto->one->propertyOne; // one1
echo $dto->one->propertyTwo; // two1
echo $dto->two->propertyOne; // one2
echo $dto->two->propertyTwo; // two2
```

-
```
# Input data

$data = json_encode([
    'one' => [
        'propertyOne' => 'one',
        'propertyTwo' => 'two',
    ],
    'collection' => [
        [
            'propertyOne' => 'one1',
            'propertyTwo' => 'two1',
        ],
        [
            'propertyOne' => 'one2',
            'propertyTwo' => 'two2',
        ],
    ],
]);
        
# Dto
use Sergey144010\Data\Attribute\Collection;

class CollectionDto
{
    public function __construct(
        readonly public SomeDto $one,
        #[Collection(SomeDto::class)] readonly public array $collection,
    ) {
    }
}

# Output

$dto = $json->toObject($data, CollectionDto::class);
echo $dto->one->propertyOne; // one
echo $dto->one->propertyTwo; // two
echo $dto->collection[0]->propertyOne; // one1
echo $dto->collection[0]->propertyTwo; // two1
echo $dto->collection[1]->propertyOne; // one2
echo $dto->collection[1]->propertyTwo; // two2
```

-
```
# Input data

$data = json_encode([
    [
        'propertyOne' => 11,
        'propertyTwo' => 12,
    ],
    [
        'propertyOne' => 21,
        'propertyTwo' => 22,
    ],
]);
        
# Dto
use Sergey144010\Data\Attribute\CollectionRoot;

class CollectionDto
{
    public function __construct(
        readonly public SomeDto $one,
        #[CollectionRoot(SomeDto::class)] readonly public array $collection,
    ) {
    }
}

# Output

$dto = $json->toObject($data, CollectionDto::class);
echo $dto->collection[0]->propertyOne; // 11
echo $dto->collection[0]->propertyTwo; // 12
echo $dto->collection[1]->propertyOne; // 21
echo $dto->collection[1]->propertyTwo; // 22
```

-
```
# Input data

$data = json_encode([
    'propertyOne' => [359418786]
]);

# Dto

class ConstructorPropertyArrayModel
{
    public function __construct(
        readonly public array $propertyOne
    ) {
    }
}

# Output

$dto = $json->toObject($data, ConstructorPropertyArrayModel::class);
echo $dto->propertyOne[0]; // 359418786
```

## With Interfaces

-
```
        $data = json_encode([
            'one' => [
                'property-one' => 'one',
            ],
            'two' => [
                'property-two' => 'two',
            ],
        ]);

        $model = $json->toObject(
            jsonString: $data,
            class: ModelPropertyTwo::class,
            injectionMap: [
                ModelInterface::class => ModelClass::class,
                ModelInterfaceTwo::class => ModelClassTwo::class,
            ]
        );

        self::assertEquals('one', $model->one->propertyOne);
        self::assertEquals('two', $model->two->propertyTwo);
        
# Where
class ModelPropertyTwo
{
    public readonly ModelInterface $one;
    public readonly ModelInterfaceTwo $two;
}

class ModelClass implements ModelInterface
{
    public readonly string $propertyOne;
}

class ModelClassTwo implements ModelInterfaceTwo
{
    public readonly string $propertyTwo;
}
```

-
```
        $data = json_encode([
            'one' => [
                'property-one' => 'one',
            ],
            'two' => [
                'property-two' => 'two',
            ],
        ]);

        $model = $json->toObject(
            jsonString: $data,
            class: EntryModelTwo::class,
            injectionMap: [
                EntryModelTwo::class => [
                    'one' => ModelOne::class,
                    'two' => ModelTwo::class,
                ]
            ]
        );

        self::assertEquals('one', $model->one->propertyOne);
        self::assertEquals('two', $model->two->propertyTwo);
        
# Where
class EntryModelTwo
{
    public function __construct(
        readonly public ModelInterfaceBase $one,
        readonly public ModelInterfaceBase $two,
    ) {
    }
}

class ModelOne implements ModelInterfaceBase
{
    public function __construct(
        readonly public string $propertyOne,
    ) {
    }
}

class ModelTwo implements ModelInterfaceBase
{
    public function __construct(
        readonly public string $propertyTwo,
    ) {
    }
}
```

-
```
        $string = json_encode(
            [
                'one' => [
                    'property-two' => 'two',
                ],
            ]
        );

        $model = $this->json->toObject(
            jsonString: $string,
            class: EntryModelOne::class,
            injectionMap: function (array $request) {
                if (isset($request['one']['property-one'])) {
                    return [ModelInterfaceBase::class => ModelOne::class];
                }
                if (isset($request['one']['property-two'])) {
                    return [ModelInterfaceBase::class => ModelTwo::class];
                }

                return [];
            }
        );

        self::assertEquals('two', $model->one->propertyTwo);
```

# About Strategy

Есть несколько стратегий заполнения свойств объекта:
- Через конструктор. Только свойства описанные в конструкторе попадут в объект. Strategy\Constructor.
- Через публичные свойства объекта. Только свойства описанные как публичные свойства объекта попадут в объект. Strategy\Properties.
- Сначала проверка есть ли что-то в конструкторе, а потом проверка публичных свойств. Strategy\StepByStep - по умолчанию.
- Добавление всех входящих данных как публичные свойства объекта. Strategy\AddProperties.

There are several strategies for filling object properties:
- Via a constructor. Only properties described in the constructor will be included in the object. Strategy\Constructor.
- Via public properties of the object. Only properties described as public properties of the object will be included in the object. Strategy\Properties.
- First, check if there is something in the constructor, and then check the public properties. Strategy\StepByStep - by default.
- Add all incoming data as public properties of the object. Strategy\AddProperties.

# Examples strategy

- Constructor
```
class SomeDto
{
    public function __construct(
        readonly public string $propertyOne,
    ) {
    }
}

$json->toObject(
    jsonString:$data,
    class: SomeDto::class,
    strategy: new Strategy\Constructor()
    );
```

- Public properties
```
class SomeDto
{
    public string $propertyOne;
}

$json->toObject(
    jsonString:$data,
    class: SomeDto::class,
    strategy: new Strategy\Properties()
    );
```

- Combine Constructor and public properties
```
$json->toObject(
    jsonString:$data,
    class: SomeDto::class,
    strategy: new Strategy\StepByStep()
);
```

- Simple add properties
```
class SomeDto
{
}

$data = [
    'one' => 1,
    'two' => 2,
]

$json->toObject(
    jsonString:$data,
    class: SomeDto::class,
    strategy: new Strategy\AddProperties()
);

echo $data->one; // 1
echo $data->two; // 2
```

For more details see https://github.com/sergey144010/data

# Code style, Static analyze, Tests
Need checkout tests branch

## Build
```
$ docker build -t test-json-image-php8.1 -f ./docker/Dockerfile-php8.1 ./docker
```

## Install
```
$ docker run -it --rm --name json-tests -v "$PWD":/usr/src/app -w /usr/src/app test-json-image-php8.1 composer install
```

## Code style
```
$ docker run -it --rm --name json-tests -v "$PWD":/usr/src/app -w /usr/src/app test-json-image-php8.1 php ./vendor/bin/phpcs --standard=PSR12 -p ./src ./tests
$ docker run -it --rm --name json-tests -v "$PWD":/usr/src/app -w /usr/src/app test-json-image-php8.1 php ./vendor/bin/phpcbf --standard=PSR12 -p ./src ./tests
```

## Static analyze
```
$ docker run -it --rm --name json-tests -v "$PWD":/usr/src/app -w /usr/src/app test-json-image-php8.1 php ./vendor/bin/phpstan analyze --level=5 ./src
```

## Tests
```
$ docker run -it --rm --name json-tests -v "$PWD":/usr/src/app -w /usr/src/app test-json-image-php8.1 php ./vendor/bin/phpunit --bootstrap ./vendor/autoload.php ./tests
```