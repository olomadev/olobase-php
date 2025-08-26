
# Mezzio Modularity Project v1.0 Details


## Directory Structure of Modules

```
- src
	- Authorization
		- src
			- Dto
				PermissionCreateDto.php
				PermissionDto.php
				PermissionFindByPagingDto.php
				PermissionFindAllDto.php
				PermissionUpdateDto.php
				PermissionFindByIdDto.php
			- Entity
				Permission.php
			- Handler
				- Permissions
					CreateHandler.php
					CreateHandlerFactory.php
					DeleteHandler.php
					DeleteHandlerFactory.php
					FindByPagingHandler.php
					FindByPagingHandlerFactory.php
					UpdateHandler.php
					UpdateHandlerFactory.PHP
			- i18n
				- en
					messages.php

			- Migrations
			- Repository
				PermissionRepository.php

			ConfigProvider.php
			composer.json



## ConfigProvider.php

```php
<?php

declare(strict_types=1);

namespace Authorization;

use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Modularity\DataTable\ColumnFiltersInterface;
use Modularity\Router\AttributeRouteProviderInterface;
use Psr\Container\ContainerInterface;

use function dirname;

/**
 * The configuration provider for the Authorization module
 *
 * @see https://docs.laminas.dev/laminas-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'translator'   => $this->getTranslations(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'invokables' => [],
            'aliases'    => [],
            'factories'  => [


                // handlers - permissions
                Handler\Permissions\CreateHandler::class       => Handler\Permissions\CreateHandlerFactory::class,
                Handler\Permissions\UpdateHandler::class       => Handler\Permissions\UpdateHandlerFactory::class,
                Handler\Permissions\DeleteHandler::class       => Handler\Permissions\DeleteHandlerFactory::class,
                Handler\Permissions\FindByPagingHandler::class => Handler\Permissions\FindByPagingHandlerFactory::class,

                Repository\PermissionRepository::class => function ($container) {
                    $dbAdapter     = $container->get(AdapterInterface::class);
                    $cacheStorage  = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    $permissions   = new TableGateway('permissions', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Repository\PermissionRepository($permissions, $cacheStorage, $columnFilters);
                },
            ],
        ];
    }

    public function getTranslations(): array
    {
        return [
            'translation_file_patterns' => [
                [
                    'type'     => 'PhpArray',
                    'base_dir' => __DIR__ . '/i18n',
                    'pattern'  => '%s/messages.php',
                ],
            ],
        ];
    }

    public static function registerRoutes(ContainerInterface $container): void
    {
        $provider = $container->get(AttributeRouteProviderInterface::class);
        $provider->registerRoutes(dirname(__DIR__));
    }
}
```


## Handler / Permissions / Handler /  CreateHandler.php


```php
<?php

declare(strict_types=1);

namespace Authorization\Handler\Permissions;

use Authentication\Middleware\JwtAuthenticationMiddleware;
use Authorization\Dto\PermissionCreateDto;
use Authorization\Entity\Permission;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Mezzio\Authorization\AuthorizationMiddleware;
use Modularity\Attribute\Entity;
use Modularity\Attribute\Route;
use Modularity\Authorization\PermissionRepositoryInterface;
use Modularity\Middleware\EntityMiddleware;
use Modularity\Validation\ValidationErrorFormatterInterface;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Route(
    path: '/api/authorization/permissions/create',
    methods: ['POST'],
    middlewares: [
        JwtAuthenticationMiddleware::class,
        AuthorizationMiddleware::class,
        EntityMiddleware::class,
    ]
)]
class CreateHandler implements RequestHandlerInterface
{
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository,
        private InputFilterPluginManager $filterManager,
        private ValidationErrorFormatterInterface $errorFormatter,
    ) {
    }

    #[Entity(dto: PermissionCreateDto::class, entity: Permission::class)]
    #[OA\Post(
        path: '/api/authorization/permissions/create',
        tags: ['Authorization'],
        summary: 'Create a new permission',
        operationId: 'authorizationPermissions_create',
        requestBody: new OA\RequestBody(
            description: 'Create a new permission',
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/PermissionCreateDto')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'id',
                                    type: 'string',
                                    example: 'b8d3a570-3c3d-11ee-be56-0242ac120002'
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request, returns validation errors'
            ),
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $entity = $request->getAttribute('entity');
        $permId = $this->permissionRepository->createEntity($entity);

        return new JsonResponse(['data' => ['id' => $permId]]);
    }
}
```


## Handler / Permissions / Dto / PermissionCreateDto.php


```php
<?php

declare(strict_types=1);

namespace Authorization\Dto;

use Laminas\Validator\InArray;
use Modularity\Attribute\Input;
use Modularity\Attribute\InputFilter;
use Modularity\Attribute\ObjectInput;
use OpenApi\Attributes as OA;

#[InputFilter]
#[OA\Schema(
    schema: "PermissionCreateDto",
    required: ["id", "action", "method"],
    type: "object",
    description: "Permission creation data transfer object"
)]
class PermissionCreateDto
{
    #[Input(name: 'name')]
    #[OA\Property(
        property: "name",
        type: "string",
        description: "Human-readable permission name"
    )]
    public string $name;

    #[ObjectInput(
        name: 'action',
        fields: [
            [
                'name'       => 'id',
                'required'   => true,
                'validators' => [
                    [
                        'name'    => InArray::class,
                        'options' => [
                            'haystack' => ['create', 'delete', 'edit', 'list', 'show'],
                        ],
                    ],
                ],
            ],
        ]
    )]
    #[OA\Property(
        property: "action",
        type: "object",
        required: ["id"],
        properties: [
            new OA\Property(
                property: "id",
                type: "string",
                enum: ["create", "delete", "edit", "list", "show"],
                description: "Action type"
            ),
        ]
    )]
    public array $action;

    #[ObjectInput(
        name: 'method',
        fields: [
            [
                'name'       => 'id',
                'required'   => true,
                'validators' => [
                    [
                        'name'    => InArray::class,
                        'options' => [
                            'haystack' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'],
                        ],
                    ],
                ],
            ],
        ]
    )]
    #[OA\Property(
        property: "method",
        type: "object",
        required: ["id"],
        properties: [
            new OA\Property(
                property: "id",
                type: "string",
                enum: ["GET", "POST", "PUT", "DELETE", "PATCH"],
                description: "HTTP method"
            ),
        ]
    )]
    public array $method;
}
```


```php
<?php

declare(strict_types=1);

namespace Authorization\Handler\Permissions;

use Authentication\Middleware\JwtAuthenticationMiddleware;
use Authorization\Dto\PermissionsFindByPagingDto;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Authorization\AuthorizationMiddleware;
use Olobase\Attribute\Route;
use Olobase\Authorization\PermissionRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Route(
    path: '/api/authorization/permissions/findByPaging',
    methods: ['GET'],
    middlewares: [
        JwtAuthenticationMiddleware::class,
        AuthorizationMiddleware::class,
    ]
)]
class FindByPagingHandler implements RequestHandlerInterface
{
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository
    ) {
    }

    #[OA\Get(
        path: '/api/authorization/permissions/findByPaging',
        tags: ['Authorization'],
        summary: 'Find all roles by pagination',
        operationId: 'authorizationRoles_findByPaging',
        parameters: [
            new OA\Parameter(
                name: 'q',
                in: 'query',
                required: false,
                description: 'Search string',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'Page number',
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                description: 'Per page',
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'sort',
                in: 'query',
                required: false,
                description: 'Order items',
                schema: new OA\Schema(
                    type: 'array',
                    items: new OA\Items()
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/PermissionsFindByPagingDto')
            ),
            new OA\Response(
                response: 404,
                description: 'No result found'
            ),
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $get     = $request->getQueryParams();
        $page    = empty($get['page']) ? 1 : (int) $get['page'];
        $perPage = empty($get['per_page']) ? 5 : (int) $get['per_page'];

        // https://docs.laminas.dev/tutorials/pagination/
        $paginator = $this->permissionRepository->findByPaging($get);

        $page = $page < 1 ? 1 : $page;
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);

        $dto = new PermissionsFindByPagingDto($paginator);
        return new JsonResponse($dto->toArray());
    }
}
```


## Handler / Permissions / Dto / PermissionsFindByPagingDto.php

```php
<?php

declare(strict_types=1);

namespace Authorization\Dto;

use Laminas\Paginator\Paginator;
use Olobase\Dto\AbstractDto;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'PermissionsFindByPagingDto', type: 'object')]
class PermissionsFindByPagingDto extends AbstractDto
{
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: '#/components/schemas/PermissionDto')
    )]
    public array $data = [];

    #[OA\Property(type: 'integer')]
    public int $page = 0;

    #[OA\Property(type: 'integer')]
    public int $perPage = 0;

    #[OA\Property(type: 'integer')]
    public int $totalPages = 0;

    #[OA\Property(type: 'integer')]
    public int $totalItems = 0;

    public function __construct(?Paginator $paginator = null)
    {
        if ($paginator === null) {
            return;
        }

        $items = [];
        foreach ($paginator->getCurrentItems() as $row) {
            $items[] = PermissionDto::hydrate((array) $row)->toArray();
        }

        $this->data       = $items;
        $this->page       = $paginator->getCurrentPageNumber();
        $this->perPage    = $paginator->getItemCountPerPage();
        $this->totalPages = $paginator->count();
        $this->totalItems = $paginator->getTotalItemCount();
    }
}
```

## Handler / Permissions / Dto / PermissionDto.php

```php
<?php

declare(strict_types=1);

namespace Authorization\Dto;

use Olobase\Dto\AbstractDto;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'PermissionDto', type: 'object')]
class PermissionDto extends AbstractDto
{
    #[OA\Property(property: 'id', type: 'string', format: 'uuid')]
    public string $id;

    #[OA\Property(property: 'module', type: 'string')]
    public string $module;

    #[OA\Property(property: 'name', type: 'string')]
    public string $name;

    #[OA\Property(
        type: 'object',
        property: 'action',
        properties: [
            new OA\Property(property: 'id', type: 'string'),
            new OA\Property(property: 'name', type: 'string')
        ]
    )]
    public array $action;

    #[OA\Property(
        type: 'object',
        property: 'method',
        properties: [
            new OA\Property(property: 'id', type: 'string'),
            new OA\Property(property: 'name', type: 'string')
        ]
    )]
    public array $method;

    #[OA\Property(property: 'route', type: 'string')]
    public string $route;
}
```

## Modularity / Dto / AbstractDto.php


```php
<?php

declare(strict_types=1);

namespace Modularity\Dto;

use OpenApi\Attributes as OA;
use ReflectionClass;
use ReflectionProperty;

use function array_map;
use function class_exists;
use function end;
use function explode;
use function in_array;
use function is_array;
use function is_object;
use function is_string;
use function json_decode;
use function json_last_error;
use function lcfirst;
use function method_exists;
use function preg_replace;
use function str_replace;
use function strtolower;
use function ucwords;

use const JSON_ERROR_NONE;
use const JSON_THROW_ON_ERROR;

abstract class AbstractDto
{
    public function toArray(): array
    {
        $result     = [];
        $reflection = new ReflectionClass($this);

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $name = $property->getName();

            if (! $property->isInitialized($this)) {
                $result[$name] = null;
                continue;
            }

            $value = $property->getValue($this);

            if ($value instanceof self) {
                // DTO ise kendi toArray() çağır
                $result[$name] = $value->toArray();
            } elseif (is_array($value)) {
                // Array ise map et
                $result[$name] = array_map(function ($v) {
                    if ($v instanceof self) {
                        return $v->toArray();
                    }
                    if (is_object($v) && method_exists($v, 'toCamelCaseArray')) {
                        return $v->toCamelCaseArray();
                    }
                    return $v;
                }, $value);
            } elseif (is_object($value) && method_exists($value, 'toCamelCaseArray')) {
                // Tek bir entity nesnesi
                $result[$name] = $value->toCamelCaseArray();
            } else {
                // Normal değer
                $result[$name] = $value;
            }
        }

        return $result;
    }

    public static function hydrate(array $row, $key = 'data'): static
    {
        // var_dump($row);

        $dtoClass   = static::class;
        $reflection = new ReflectionClass($dtoClass);

        /** @var static $dto */
        $dto = $reflection->newInstanceWithoutConstructor();

        $debug = [];

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $attributes = $property->getAttributes(OA\Property::class);

            if (empty($attributes)) {
                continue; // Attribute yoksa atla
            }

            foreach ($attributes as $attribute) {
                /** @var OA\Property $oaProperty */
                $oaProperty = $attribute->newInstance();
                $propName   = self::camelToSnake($oaProperty->property ?? $property->getName());

                $debug[] = $propName;

                // // --- ARRAY OBJECT TYPE ---
                // if ($oaProperty->type === 'array' && $oaProperty->items && $oaProperty->items->type === 'object') {
                //     foreach ($oaProperty->items as $itemData) {
                //         if (is_array($itemData) && isset($itemData[0]->property)) {
                //             $$key = []; // current value if present, empty array if not
                //             foreach ($itemData as $itemKey => $item) {
                //                 $column        = self::camelToSnake($item->property);
                //                 $itemRowValue  = $row[$column];
                //                 $$key[$column] = self::castValue($itemRowValue, $item->type);
                //             }
                //             $property->setValue($dto, $$key);
                //         }
                //     }
                // }

                // --- ARRAY OBJECT TYPE ---
                if ($oaProperty->type === 'array' && $oaProperty->items && $oaProperty->items->ref) {
                    if (! isset($row[$propName]) || ! is_array($row[$propName])) {
                        continue;
                    }

                    // Ref'ten hangi DTO kullanılacağını bul
                    $ref      = $oaProperty->items->ref; // "#/components/schemas/PermissionDto"
                    $refClass = null;

                    if ($ref) {
                        $parts      = explode('/', $ref);
                        $schemaName = end($parts); // "PermissionDto"
                        $refClass   = $reflection->getNamespaceName() . "\\" . $schemaName;
                    }

                    $items = [];
                    foreach ($row[$propName] as $itemValue) {
                        if ($refClass && class_exists($refClass)) {
                            if (is_object($itemValue) && method_exists($itemValue, 'toCamelCaseArray')) {
                                $items[] = $refClass::hydrate($itemValue->toCamelCaseArray());
                            } elseif (is_array($itemValue)) {
                                $items[] = $refClass::hydrate($itemValue);
                            }
                        } else {
                            $items[] = $itemValue;
                        }
                    }

                    $property->setValue($dto, $items);
                }
                // --- OBJECT TYPE ---
                elseif ($oaProperty->type === 'object') {
                    if (! isset($row[$propName])) {
                        continue;
                    }

                    $value = $row[$propName];

                    if (is_string($value)) {
                        $value = json_decode($value, true);
                    }

                    if (! is_array($value)) {
                        continue;
                    }

                    $objectData = [];
                    foreach ($oaProperty->properties as $subProperty) {
                        /** @var OA\Property $subProperty */
                        $subPropName  = self::camelToSnake($subProperty->property ?? '');
                        $subPropValue = $value[$subPropName] ?? null;

                        if ($subPropValue !== null) {
                            $objectData[$subPropName] = self::castValue($subPropValue, $subProperty->type);
                        }
                    }

                    $property->setValue($dto, $objectData);
                }
                // --- SCALAR TYPE ---
                elseif (! empty($row[$propName])) {
                    $property->setValue($dto, self::castValue($row[$propName], $oaProperty->type));
                }
            }
        } // end foreach

        // print_r($debug);
        // die;
        return $dto;
    }

    private static function castValue(mixed $value, ?string $type): mixed
    {
        if ($value === null) {
            return null;
        }
        if (is_string($value) && self::isJson($value)) {
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        }
        return match ($type) {
            'array'    => (array) $value,
            'object'   => (object) $value,
            'string' => (string) $value,
            'integer', 'int' => (int) $value,
            'number', 'float' => (float) $value,
            'boolean', 'bool' => is_string($value) ? in_array(strtolower($value), ['1', 'true', 'yes'], true) : (bool) $value,
            default  => $value,
        };
    }

    private static function isJson(string $string): bool
    {
        if ($string === '' || ! is_string($string)) {
            return false;
        }

        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    private static function snakeToCamel(string $string): string
    {
        return lcfirst(str_replace('_', '', ucwords($string, '_')));
    }

    private static function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($input)));
    }
}
```

## Handler / Permissions / Entity / Permission.php


```php
<?php

declare(strict_types=1);

namespace Authorization\Entity;

use Modularity\Entity\AbstractEntity;

final class Permission extends AbstractEntity
{
    public function __construct(
        ?string $id = null,
        private ?string $module = null,
        private ?string $name = null,
        private ?string $action = null,
        private ?string $route = null,
        private ?string $method = null,
    ) {
        parent::__construct($id);
    }

    public function getModule(): ?string
    {
        return (string) $this->module;
    }

    public function getName(): ?string
    {
        return (string) $this->name;
    }

    public function getAction(): string
    {
        return (string) $this->action;
    }

    public function getRoute(): ?string
    {
        return (string) $this->route;
    }

    public function getMethod(): string
    {
        return (string) $this->method;
    }
}
```


## Modularity \ Entity \ AbstractEntity.php


```php
<?php

declare(strict_types=1);

namespace Modularity\Entity;

use Modularity\Util\RandomStringHelper;
use ReflectionClass;

use function get_class_methods;
use function in_array;
use function is_string;
use function json_decode;
use function json_last_error;
use function lcfirst;
use function preg_replace;
use function str_replace;
use function str_starts_with;
use function strtolower;
use function substr;
use function ucfirst;
use function ucwords;

use const JSON_ERROR_NONE;
use const JSON_THROW_ON_ERROR;

abstract class AbstractEntity
{
    /** @var string|int|null */
    protected $id;

    public function __construct(string|int|null $id = null)
    {
        $this->id = $id;

        if ($id === null) {
            $this->id = RandomStringHelper::generateUuid();
        }
    }

    public function getId(): string|int
    {
        return $this->id;
    }

    public function toCamelCaseArray(array $exclude = []): array
    {
        $data = [];
        foreach (get_class_methods($this) as $method) {
            if (str_starts_with($method, 'get')) {
                $propName = lcfirst(substr($method, 3)); // getName -> name
                if (in_array($propName, $exclude, true)) {
                    continue;
                }
                $data[self::snakeToCamel($propName)] = $this->{$method}();
            }
        }
        return $data;
    }

    public function toSnakeCaseArray(array $exclude = []): array
    {
        $data = [];
        foreach (get_class_methods($this) as $method) {
            if (str_starts_with($method, 'get')) {
                $propName = lcfirst(substr($method, 3)); // getName -> name
                if (in_array($propName, $exclude, true)) {
                    continue;
                }
                $data[self::camelToSnake($propName)] = $this->{$method}();
            }
        }
        return $data;
    }

    public static function hydrate(array $row, ?string $entityClass = null): static
    {
        $entityClass ??= static::class;
        $reflection    = new ReflectionClass($entityClass);

        // 1. Create the entity with minimal constructor arguments
        $constructorParams = [];
        foreach ($reflection->getConstructor()->getParameters() as $param) {
            $constructorParams[] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
        }
        /** @var static $entity */
        $entity = $reflection->newInstanceArgs($constructorParams);

        // 2. Set properties using setter or directly via property
        foreach ($reflection->getConstructor()->getParameters() as $param) {
            $name  = $param->getName();
            $value = $row[$name] ?? ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null);

            $type  = $param->getType()?->getName();
            $value = self::castValue($value, $type);

            $setter = 'set' . ucfirst($name);
            if ($reflection->hasMethod($setter)) {
                $entity->{$setter}($value);
            } elseif ($reflection->hasProperty($name)) {
                $prop = $reflection->getProperty($name);
                $prop->setAccessible(true);
                $prop->setValue($entity, $value);
            }
        }

        return $entity;
    }

    private static function castValue(mixed $value, ?string $type): mixed
    {
        if ($value === null) {
            return null;
        }
        if (is_string($value) && self::isJson($value)) {
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        }
        return match ($type) {
            'string' => (string) $value,
            'integer', 'int' => (int) $value,
            'number', 'float' => (float) $value,
            'boolean', 'bool' => is_string($value) ? in_array(strtolower($value), ['1', 'true', 'yes'], true) : (bool) $value,
            default  => $value,
        };
    }

    private static function snakeToCamel(string $string): string
    {
        return lcfirst(str_replace('_', '', ucwords($string, '_')));
    }

    private static function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($input)));
    }

    private static function isJson(string $string): bool
    {
        if ($string === '' || ! is_string($string)) {
            return false;
        }

        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
```

## Repository \ PermissionRepository

```php
<?php

declare(strict_types=1);

namespace Authorization\Repository;

use Authorization\Entity\Permission;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Sql;
use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Paginator\Paginator;
use Olobase\Authorization\PermissionRepositoryInterface;
use Olobase\DataTable\ColumnFiltersInterface;
use Olobase\Db\JsonExpressionHelper;
use Olobase\Repository\AbstractRepository;

use function array_map;
use function iterator_to_array;
use function json_encode;
use function strtolower;
use function strtoupper;
use function ucfirst;

class PermissionRepository extends AbstractRepository implements PermissionRepositoryInterface
{
    public function __construct(
        private TableGatewayInterface $permissions,
        private StorageInterface $cache,
        private ColumnFiltersInterface $columnFilters
    ) {
        parent::__construct($permissions, $cache);
    }

    public function findAll(): array
    {
        $key = APP_CACHE_PREFIX . self::class . ':' . __FUNCTION__;
        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key);
        }
        $sql    = new Sql($this->permissions->getAdapter());
        $select = $sql->select();
        $select->columns([
            'id',
            'module',
            'name',
            'action',
            'route',
            'method',
        ]);
        $select->from(['p' => 'permissions']);
        $select->order(['module ASC', 'name ASC']);

        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results   = iterator_to_array($resultSet);
        $statement->getResource()->closeCursor();

        if (! empty($results)) {
            $this->cache->setItem($key, $results);
        }
        return $results;
    }

    public function findByRoleId(string $roleId): array
    {
        $platform   = strtolower($this->adapter->getPlatform()->getName());
        $jsonHelper = new JsonExpressionHelper($platform);
        
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id',
            'module',
            'name',
            'route',
            'action' => $jsonHelper->jsonObject([
                'id' => 'p.action',
                'name' => $jsonHelper->ucfirst('p.action')
            ]),
            'method' => $jsonHelper->jsonObject([
                'id' => 'p.method',
                'name' => $jsonHelper->upper('p.method')
            ]),
            'method',
        ]);
        $select->from(['p' => 'permissions'])
            ->join(
                ['rp' => 'role_permissions'],
                'p.id = rp.perm_id',
                [],
                $select::JOIN_INNER
            )
            ->where(['rp.role_id' => $roleId]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();
        $rows      = iterator_to_array($result);
        $statement->getResource()->closeCursor();

        return array_map(
            fn($r) => new Permission(
                $r['id'],
                $r['module'],
                $r['name'],
                $r['action'],
                $r['route'],
                $r['method'],
            ),
            $rows
        );
    }

    public function findGroupedByRole(): array
    {
        $key = APP_CACHE_PREFIX . self::class . ':' . __FUNCTION__;
        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key);
        }
        $select = $this->permissions->getSql()->select();
        $select->columns(['id', 'route', 'method', 'action']);
        $select->join(
            ['rp' => 'role_permissions'],
            'permissions.id = rp.perm_id',
            [],
            $select::JOIN_INNER
        );
        $select->join(
            ['r' => 'roles'],
            'r.id = rp.role_id',
            ['key', 'level'],
            $select::JOIN_LEFT
        );
        $resultSet = $this->permissions->selectWith($select);
        $results   = [];
        foreach ($resultSet as $row) {
            $results[$row['key']][] = $row['route'] . '^' . $row['method'];
        }
        // echo $select->getSqlString($this->permissions->getAdapter()->getPlatform());
        // die;
        if (! empty($results)) {
            $this->cache->setItem($key, $results);
        }
        return $results;
    }

    public function findByPaging(array $get): Paginator
    {
        $platform   = strtolower($this->adapter->getPlatform()->getName());
        $jsonHelper = new JsonExpressionHelper($platform);

        $sql    = new Sql($this->permissions->getAdapter());
        $select = $sql->select();
        $select->columns([
            'id',
            'action' => $jsonHelper->jsonObject([
                'id'   => 'p.action',
                'name' => $jsonHelper->ucfirst('p.action'),
            ]),
            'method' => $jsonHelper->jsonObject([
                'id'   => 'p.method',
                'name' => $jsonHelper->upper('p.method'),
            ]),
            'module',
            'name',
            'route',
        ])->from(['p' => 'permissions']);

        $this->columnFilters->clear();
        $this->columnFilters->setColumns([
            'module',
            'name',
            'action',
            'route',
            'method',
        ]);
        $this->columnFilters->setSelect($select);
        $this->columnFilters->setData($get);

        if ($this->columnFilters->searchDataIsNotEmpty()) {
            $nest = $select->where->nest();
            foreach ($this->columnFilters->getSearchData() as $col => $words) {
                $nest = $nest->or->nest();
                foreach ($words as $str) {
                    $nest->or->like(new Expression($col), '%' . $str . '%');
                }
                $nest = $nest->unnest();
            }
            $nest->unnest();
        }
        if ($this->columnFilters->orderDataIsNotEmpty()) {
            foreach ($this->columnFilters->getOrderData() as $order) {
                $select->order($order);
            }
        } else {
            $select->order(['module ASC', 'name ASC']);
        }

        // echo $select->getSqlString($this->permissions->getAdapter()->getPlatform());
        // die;
        $paginatorAdapter = new DbSelect($select, $this->adapter);
        return new Paginator($paginatorAdapter);
    }

    protected function doCreate(object $entity)
    {
        $this->permissions->insert($entity->toSnakeCaseArray());
        return $entity->getId();
    }

    protected function doUpdate(object $entity)
    {
        return $this->permissions->update($entity->toSnakeCaseArray(['id']), ['id' => $entity->getId()]);
    }

    protected function doDelete(object $entity)
    {
        return $this->permissions->delete(['id' => $entity->getId()]);
    }

    protected function deleteCache(): void
    {
        $this->cache->removeItem(APP_CACHE_PREFIX . self::class . ':findGroupedByRole');
    }
}
```


## Modularity / Repository / AbstractRepository.php


```php
<?php

declare(strict_types=1);

namespace Modularity\Repository;

use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\EventManager\EventManagerInterface;
use Throwable;

abstract class AbstractRepository implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    protected $conn;
    protected $adapter;
    protected bool $eventsEnabled = false;

    public function __construct(
        TableGatewayInterface $table,
        StorageInterface $cache,
        ?EventManagerInterface $eventManager = null
    ) {
        $this->conn    = $table->getAdapter()->getDriver()->getConnection();
        $this->adapter = $table->getAdapter();

        if ($eventManager) {
            $this->setEventManager($eventManager);
            $this->eventsEnabled = true;
        }
    }

    protected function transactional(callable $callback): mixed
    {
        $this->conn->beginTransaction();
        try {
            $result = $callback();
            $this->deleteCache();
            $this->conn->commit();
            return $result;
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function createEntity(object $entity)
    {
        return $this->transactional(function () use ($entity) {
            $this->beforeCreate($entity);
            $id = $this->doCreate($entity);
            $this->afterCreate($entity);
            return $id;
        });
    }

    public function updateEntity(object $entity)
    {
        return $this->transactional(function () use ($entity) {
            $this->beforeUpdate($entity);
            $affectedRows = $this->doUpdate($entity);
            $this->afterUpdate($entity);
            return $affectedRows;
        });
    }

    public function deleteEntity(object $entity)
    {
        return $this->transactional(function () use ($entity) {
            $this->beforeDelete($entity);
            $affectedRows = $this->doDelete($entity);
            $this->afterDelete($entity);
            return $affectedRows;
        });
    }

    abstract protected function doCreate(object $entity);

    abstract protected function doUpdate(object $entity);

    abstract protected function doDelete(object $entity);

    protected function beforeCreate(object $entity): void
    {
        if ($this->eventsEnabled) {
            $this->getEventManager()->trigger(__FUNCTION__, $this, ['entity' => $entity]);
        }
    }

    protected function afterCreate(object $entity): void
    {
        if ($this->eventsEnabled) {
            $this->getEventManager()->trigger(__FUNCTION__, $this, ['entity' => $entity]);
        }
    }

    protected function beforeUpdate(object $entity): void
    {
        if ($this->eventsEnabled) {
            $this->getEventManager()->trigger(__FUNCTION__, $this, ['entity' => $entity]);
        }
    }

    protected function afterUpdate(object $entity): void
    {
        if ($this->eventsEnabled) {
            $this->getEventManager()->trigger(__FUNCTION__, $this, ['entity' => $entity]);
        }
    }

    protected function beforeDelete(object $entity): void
    {
        if ($this->eventsEnabled) {
            $this->getEventManager()->trigger(__FUNCTION__, $this, ['entity' => $entity]);
        }
    }

    protected function afterDelete(object $entity): void
    {
        if ($this->eventsEnabled) {
            $this->getEventManager()->trigger(__FUNCTION__, $this, ['entity' => $entity]);
        }
    }

    protected function deleteCache(): void
    {
    }

    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }
}
```


## Modularity / Mapper / InputSchemaMapper.php

```php
<?php

declare(strict_types=1);

namespace Modularity\Mapper;

use Laminas\InputFilter\InputFilterInterface;
use Modularity\Attribute\ObjectInput;
use Modularity\Entity\AbstractEntity;
use ReflectionClass;
use RuntimeException;

use function array_key_exists;
use function is_array;
use function is_string;
use function is_subclass_of;

class InputSchemaMapper
{
    public function mapToEntity(InputFilterInterface $inputFilter, object|string $dtoOrEntity, ?string $entityClass = null): object
    {
        if (is_string($dtoOrEntity)) {
            $entityClass = $dtoOrEntity;
            $dto         = null;
        } else {
            $dto = $dtoOrEntity;
        }

        if (! $entityClass || ! is_subclass_of($entityClass, AbstractEntity::class)) {
            throw new RuntimeException("Entity class {$entityClass} must extend AbstractEntity");
        }

        $data = $inputFilter->getValues();

        //  ObjectInput "id" support begin ...
        if ($dto) {
            $dtoReflection = new ReflectionClass($dto);
            foreach ($dtoReflection->getProperties() as $prop) {
                foreach ($prop->getAttributes(ObjectInput::class) as $attr) {
                    $propName = $prop->getName();
                    if (isset($data[$propName]) && is_array($data[$propName]) && array_key_exists('id', $data[$propName])) {
                        $data[$propName] = $data[$propName]['id'];
                    }
                }
            }
        }
        //  ObjectInput "id" support end ...
        $entityReflection = new ReflectionClass($entityClass);
        $params           = [];

        foreach ($entityReflection->getConstructor()->getParameters() as $param) {
            $name = $param->getName();

            if (array_key_exists($name, $data)) {
                $params[] = $data[$name];
            } elseif ($param->isDefaultValueAvailable()) {
                $params[] = $param->getDefaultValue();
            } else {
                $params[] = null;
            }
        }

        return $entityReflection->newInstanceArgs($params);
    }
}
```



# Migrations


# 🛠️ Doctrine Migrations Guide (v2)

This document explains how to run and manage **module-based Doctrine Migrations** using the custom `bin/console.php` CLI tool, fully compatible with **Doctrine Migrations 3.9+**.

---

## 🚀 Migrate

Runs migration files located at `src/ModuleName/src/Migrations/`.

```bash
php bin/console.php migrations:migrate --module="ModuleName" --env=local
````

💡 **Note:**
If this is the first migration run, Doctrine will automatically create the `migration_versions` table to track executed versions.

---

## 📊 Status

Shows current migration status for the specified module.

```bash
php bin/console.php migrations:list --module="ModuleName" --env=local
```

💡 **Note:**
Displays which migrations have been **executed** and which are **pending**.

---

## 🔙 Rollback (1 Step)

Rolls back the last executed migration (`down()` method is executed).

```bash
php bin/console.php migrations:migrate --module="ModuleName" --prev=true --env=local
```

💡 **Note:**
Only the most recent migration will be rolled back.
You can add `--no-interaction` to skip confirmation and `--ansi` for colored output.

---

## 🎯 Rollback to Specific Version

Rolls back to the specified version.
⚠️ **The specified version remains applied** (i.e., its `down()` is **not** called).

```bash
php bin/console.php migrations:migrate --module="ModuleName" --to=Version20250707151000 --env=local
```

💡 **Note:**
This rolls back migrations **up to** but **not including** the given version.
This is useful for partial rollbacks or keeping that version active.

---

## 🔒 Strict Rollback (Version Included)

Rolls back **to the specified version and includes it** — i.e., the `down()` of the target version is also executed.

```bash
php bin/console.php migrations:migrate --module="ModuleName" --to=Version20250707151000 --env=local --strict=true
```

💡 **Note:**
Use `--strict=true` to remove the target version as well.
Without this flag, the target version remains **applied**.

---

## 🧪 Examples

### Migrate a specific module

```bash
php bin/console.php migrations:migrate  --module="Users" migrations:migrate --env=local
```

### Rollback to Previous Version

```bash
php bin/console.php migrations:migrate --module="Users" --prev --env=local
```

### Rollback to Specific Version

```bash
php bin/console.php migrations:migrate  --module="Users" --to=Version20250707151000 --env=local
```

### Rollback to Specific Version "0" (Strict)

Rollback to head of the specified version (0).

```bash
php bin/console.php migrations:migrate --module="Users" --to=Version20250707151000 --env=local --strict
```

💡 **Note:**

Each module must have a `src/Migrations/` folder. If missing, that module will be skipped with a warning.


