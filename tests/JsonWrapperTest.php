<?php

namespace Units;

use PHPUnit\Framework\TestCase;
use Sergey144010\Data\CamelCustom;
use Sergey144010\Data\Data;
use Sergey144010\Data\Strategy\AddProperties;
use Sergey144010\Json\JsonWrapper;
use Sergey144010\Json\JsonWrapperInterface;
use Units\InterfaceModels\ModelClass;
use Units\InterfaceModels\ModelClassTwo;
use Units\InterfaceModels\ModelConstructor;
use Units\InterfaceModels\ModelConstructorTwo;
use Units\InterfaceModels\ModelInterface;
use Units\InterfaceModels\ModelInterfaceTwo;
use Units\InterfaceModels\ModelPropertyOne;
use Units\InterfaceModels\ModelPropertyTwo;
use Units\InterfaceModels\OneInterfaceTwoImplementations\EntryModelOne;
use Units\InterfaceModels\OneInterfaceTwoImplementations\EntryModelTwo;
use Units\InterfaceModels\OneInterfaceTwoImplementations\ModelInterfaceBase;
use Units\InterfaceModels\OneInterfaceTwoImplementations\ModelOne;
use Units\InterfaceModels\OneInterfaceTwoImplementations\ModelTwo;

class JsonWrapperTest extends TestCase
{
    private readonly JsonWrapperInterface $json;

    public function setUp(): void
    {
        parent::setUp();

        $this->json = new JsonWrapper(
            new Data(new CamelCustom())
        );
    }

    public function testEmptyModel(): void
    {
        $string = json_encode(
            [
                'property-one' => 'one',
                'property_two' => 'two',
            ]
        );

        $model = $this->json->toObject($string, EmptyModel::class);

        self::assertEquals('one', $model->propertyOne);
        self::assertEquals('two', $model->propertyTwo);
    }

    public function testBasicProperty(): void
    {
        $string = json_encode(
            [
                'property-one' => 'one',
                'property_two' => 'two',
            ]
        );

        $model = $this->json->toObject($string, BasicPropertyModel::class);

        self::assertEquals('one', $model->propertyOne);
        self::assertEquals('two', $model->propertyTwo);
    }

    public function testConstructorProperty(): void
    {
        $string = json_encode(
            [
                'property-one' => 'one',
                'property_two' => 'two',
                'property three' => 'three',
            ]
        );

        $model = $this->json->toObject($string, ConstructorPropertyModel::class);

        self::assertEquals('one', $model->propertyOne);
        self::assertEquals('two', $model->propertyTwo);
        self::assertObjectNotHasProperty('propertyThree', $model);
    }

    public function testDataConstructorPropertyBasic(): void
    {
        $data = json_encode(
            [
                'propertyOne' => 'one',
                'propertyTwo' => 'two',
            ]
        );

        $model = $this->json->toObject($data, ConstructorPropertyModel::class);

        self::assertEquals('one', $model->propertyOne);
        self::assertEquals('two', $model->propertyTwo);
    }

    public function testDataConstructorPropertyWithType(): void
    {
        $data = json_encode(
            [
                'one' => [
                    'propertyOne' => 'one1',
                    'propertyTwo' => 'two1',
                ],
                'two' => [
                    'propertyOne' => 'one2',
                    'propertyTwo' => 'two2',
                ],
            ]
        );

        $model = $this->json->toObject($data, ConstructorPropertyModelWithType::class);

        self::assertIsObject($model->one);
        self::assertInstanceOf(ConstructorPropertyModel::class, $model->one);
        self::assertEquals('one1', $model->one->propertyOne);
        self::assertEquals('two1', $model->one->propertyTwo);

        self::assertIsObject($model->two);
        self::assertInstanceOf(ConstructorPropertyModel::class, $model->two);
        self::assertEquals('one2', $model->two->propertyOne);
        self::assertEquals('two2', $model->two->propertyTwo);
    }
    public function testDataConstructorPropertyWithTypeCollection(): void
    {
        $data = json_encode(
            [
                'one' => [
                    'propertyOne' => 'one',
                    'propertyTwo' => 'two',
                ],
                'collection' => [
                    'c1' => [
                        'propertyOne' => 'one1',
                        'propertyTwo' => 'two1',
                    ],
                    'c2' => [
                        'propertyOne' => 'one2',
                        'propertyTwo' => 'two2',
                    ],
                ],
                'two' => '123',
            ]
        );

        $model = $this->json->toObject($data, ConstructorParameterAttributeModel::class);

        self::assertIsObject($model->one);
        self::assertInstanceOf(ConstructorPropertyModel::class, $model->one);
        self::assertEquals('one', $model->one->propertyOne);
        self::assertEquals('two', $model->one->propertyTwo);

        self::assertIsArray($model->collection);
        self::assertCount(2, $model->collection);

        self::assertInstanceOf(ConstructorPropertyModel::class, $model->collection[0]);
        self::assertEquals('one1', $model->collection[0]->propertyOne);
        self::assertEquals('two1', $model->collection[0]->propertyTwo);

        self::assertInstanceOf(ConstructorPropertyModel::class, $model->collection[1]);
        self::assertEquals('one2', $model->collection[1]->propertyOne);
        self::assertEquals('two2', $model->collection[1]->propertyTwo);

        self::assertEquals('123', $model->two);
    }

    public function testOnNull(): void
    {
        $data = json_encode(
            [
                'Property One' => 'string123',
                'Property_Two' => null,
            ]
        );

        $model = $this->json->toObject($data, BasicPropertyWithNullModel::class);
        self::assertEquals('string123', $model->propertyOne);
        self::assertEquals(null, $model->propertyTwo);
    }

    public function testTwoNull(): void
    {
        $data = json_encode(
            [
                'Property One' => 'string123',
            ]
        );

        $model = $this->json->toObject($data, BasicPropertyWithNullModel::class);
        self::assertEquals('string123', $model->propertyOne);
        self::assertEquals(null, $model->propertyTwo);
    }

    public function testOneNothingFromResponseException(): void
    {
        $this->expectException(\Throwable::class);

        $data = json_encode(
            [
                'Property_Two' => 'asd',
            ]
        );

        $this->json->toObject($data, BasicPropertyWithNullModel::class);
    }

    public function testBasicPropertyByUniversal(): void
    {
        $string = json_encode(
            [
                'property-one' => 'one',
                'property_two' => 'two',
            ]
        );

        $model = $this->json->toObject($string, BasicPropertyModel::class);

        self::assertEquals('one', $model->propertyOne);
        self::assertEquals('two', $model->propertyTwo);
    }

    public function testMixedBasicPropertyByUniversal(): void
    {
        #$this->expectException(\ErrorException::class);
        #self::assertEquals('three', $model->propertyThree);

        $string = json_encode(
            [
                'property-one' => 'one',
                'property_two' => 'two',
                'property three' => 'three',
            ]
        );

        $model = $this->json->toObject($string, BasicPropertyModel::class);

        self::assertEquals('one', $model->propertyOne);
        self::assertEquals('two', $model->propertyTwo);
        self::assertObjectNotHasProperty('propertyThree', $model);
    }

    public function testEmptyModelByUniversal(): void
    {
        $string = json_encode(
            [
                'property-one' => 'one',
                'property_two' => 'two',
            ]
        );

        $model = $this->json->toObject($string, EmptyModel::class);

        self::assertEquals('one', $model->propertyOne);
        self::assertEquals('two', $model->propertyTwo);
    }

    public function testConstructorDefaultValue(): void
    {
        $string = json_encode([]);

        $model = $this->json->toObject($string, ConstructorPropertyDefaultModel::class);

        self::assertEquals('111', $model->propertyOne);
        self::assertEquals('222', $model->propertyTwo);
    }

    public function testPropertyDefaultValue(): void
    {
        $string = json_encode([]);

        $model = $this->json->toObject($string, PropertyDefaultModel::class);

        self::assertEquals('one123', $model->propertyOne);
        self::assertEquals('two321', $model->propertyTwo);
    }

    public function testPropertyDefaultNull()
    {
        $string = json_encode([]);

        $model = $this->json->toObject($string, ConstructorPropertyDefaultNullModel::class);

        self::assertEquals(null, $model->propertyOne);
        self::assertEquals(null, $model->propertyTwo);
    }

    public function testInterfaceOne()
    {
        $string = json_encode(
            [
                'one' => [
                    'property-one' => 'one',
                ],
                'two' => [
                    'property-one' => 'one',
                ],
                'three' => [
                    'property-one' => 'one',
                ],
            ]
        );

        $model = $this->json->toObject(
            jsonString: $string,
            class: ModelConstructor::class,
            injectionMap: [ModelInterface::class => ModelClass::class]
        );

        self::assertEquals('one', $model->one->propertyOne);
        self::assertEquals('one', $model->two->propertyOne);
        self::assertEquals('one', $model->three->propertyOne);
    }

    public function testInterfaceTwo()
    {
        $string = json_encode(
            [
                'one' => [
                    'property-one' => 'one',
                ],
                'two' => [
                    'property-two' => 'two',
                ],
            ]
        );

        $model = $this->json->toObject(
            jsonString: $string,
            class: ModelConstructorTwo::class,
            injectionMap: [
                ModelInterface::class => ModelClass::class,
                ModelInterfaceTwo::class => ModelClassTwo::class,
            ]
        );

        self::assertEquals('one', $model->one->propertyOne);
        self::assertEquals('two', $model->two->propertyTwo);
    }

    public function testInterfacePropertyOne()
    {
        $string = json_encode(
            [
                'one' => [
                    'property-one' => 'one',
                ],
                'two' => [
                    'property-one' => 'one',
                ],
                'three' => [
                    'property-one' => 'one',
                ],
            ]
        );

        $model = $this->json->toObject(
            jsonString: $string,
            class: ModelPropertyOne::class,
            injectionMap: [ModelInterface::class => ModelClass::class]
        );

        self::assertEquals('one', $model->one->propertyOne);
        self::assertEquals('one', $model->two->propertyOne);
        self::assertEquals('one', $model->three->propertyOne);
    }

    public function testInterfacePropertyTwo()
    {
        $string = json_encode(
            [
                'one' => [
                    'property-one' => 'one',
                ],
                'two' => [
                    'property-two' => 'two',
                ],
            ]
        );

        $model = $this->json->toObject(
            jsonString: $string,
            class: ModelPropertyTwo::class,
            injectionMap: [
                ModelInterface::class => ModelClass::class,
                ModelInterfaceTwo::class => ModelClassTwo::class,
            ]
        );

        self::assertEquals('one', $model->one->propertyOne);
        self::assertEquals('two', $model->two->propertyTwo);
    }

    public function testInterfaceTwoOther()
    {
        $string = json_encode(
            [
                'one' => [
                    'property-one' => 'one',
                ],
                'two' => [
                    'property-two' => 'two',
                ],
            ]
        );

        $model = $this->json->toObject(
            jsonString: $string,
            class: EntryModelTwo::class,
            injectionMap: [
                EntryModelTwo::class => [
                    'one' => [
                        ModelInterfaceBase::class => ModelOne::class,
                    ],
                    'two' => [
                        ModelInterfaceBase::class => ModelTwo::class,
                    ],
                ]
            ]
        );

        self::assertEquals('one', $model->one->propertyOne);
        self::assertEquals('two', $model->two->propertyTwo);
    }

    public function testInterfaceTwoOtherString()
    {
        $string = json_encode(
            [
                'one' => [
                    'property-one' => 'one',
                ],
                'two' => [
                    'property-two' => 'two',
                ],
            ]
        );

        $model = $this->json->toObject(
            jsonString: $string,
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
    }

    public function testInterfaceCallableOne()
    {
        $string = json_encode(
            [
                'one' => [
                    'property-one' => 'one',
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

        self::assertEquals('one', $model->one->propertyOne);
    }

    public function testInterfaceCallableTwo()
    {
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
    }

    public function testArrayInModel(): void
    {
        $string = json_encode(
            [
                'propertyOne' => [359418786]
            ]
        );

        $model = $this->json->toObject($string, ConstructorPropertyArrayModel::class);
        self::assertEquals('359418786', $model->propertyOne[0]);
    }

    public function testRootCollection(): void
    {
        $string = json_encode(
            [
                [
                    'propertyOne' => 11,
                    'propertyTwo' => 12,
                ],
                [
                    'propertyOne' => 21,
                    'propertyTwo' => 22,
                ],
            ]
        );

        $model = $this->json->toObject($string, CollectionRootModel::class);

        self::assertEquals(11, $model->collection[0]->propertyOne);
        self::assertEquals(12, $model->collection[0]->propertyTwo);
        self::assertEquals(21, $model->collection[1]->propertyOne);
        self::assertEquals(22, $model->collection[1]->propertyTwo);
    }

    public function testSimpleAddProperties(): void
    {
        $data = json_encode([
            'one' => 1,
            'two' => 2,
        ]);

        $model = $this->json->toObject(
            jsonString: $data,
            class: EmptyModel::class,
            strategy: new AddProperties(),
        );

        self::assertEquals(1, $model->one);
        self::assertEquals(2, $model->two);
    }
}
