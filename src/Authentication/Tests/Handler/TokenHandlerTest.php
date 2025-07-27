<?php

declare(strict_types=1);

namespace Authentication\Tests\Handler;

use Authentication\Handler\TokenHandler;
use Authentication\Dto\TokenRequestDto;
use Olobase\Util\ValidationErrorFormatterInterface;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterPluginManager;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;
use DateTime;
use DateTimeZone;

class TokenHandlerTest extends TestCase
{
    public function testSuccessfulTokenGeneration(): void
    {
        $requestBody = [
            'username' => 'test@example.com',
            'password' => 'secret'
        ];

        // Mock request
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn($requestBody);
        $request->method('withAttribute')->willReturnSelf();

        // Real InputFilter 
        $inputFilter = new InputFilter();
        $inputFilter->add(['name' => 'username', 'required' => true]);
        $inputFilter->add(['name' => 'password', 'required' => true]);
        $inputFilter->setData($requestBody);
        $this->assertTrue($inputFilter->isValid());

        // Mock AttributeInputFilterCollector
        $collector = $this->getMockBuilder(\Olobase\Filter\AttributeInputFilterCollector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collector->method('fromObject')->willReturn($inputFilter);

        // Mock User
        $user = $this->createMock(UserInterface::class);
        $user->method('getDetails')->willReturn([
            'id' => 1,
            'fullname' => 'Test User',
            'avatar' => ['url' => 'https://example.com/avatar.png']
        ]);
        $user->method('getIdentity')->willReturn('test@example.com');
        $user->method('getRoles')->willReturn(['user']);

        // Mock TokenService (anon class)
        $tokenService = new class {
            public function create($request): array
            {
                return [
                    'token' => 'abc.def.ghi',
                    'expiresAt' => '2099-12-31T23:59:59Z'
                ];
            }
        };

        // Mock Authentication
        $auth = $this->createMock(AuthenticationInterface::class);
        $auth->method('createUser')->willReturn($user);
        $auth->method('getTokenService')->willReturn($tokenService);

        // Error formatter mock
        $errorFormatter = $this->createMock(ValidationErrorFormatterInterface::class);

        // Handler instance
        $handler = new TokenHandler([], $auth, $this->createMock(InputFilterPluginManager::class), $errorFormatter);

        // Inject mock collector (via Reflection since it's not normally injectable)
        $reflection = new \ReflectionClass($handler);
        $property = $reflection->getProperty('filterPluginManager');
        $property->setAccessible(true);
        $property->setValue($handler, new class($collector) extends InputFilterPluginManager {
            private $collector;
            public function __construct($collector) { $this->collector = $collector; }
            public function get($name) { return $this->collector; }
        });

        // test
        $response = $handler->handle($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $payload = json_decode((string)$response->getBody(), true);

        $this->assertEquals('abc.def.ghi', $payload['data']['token']);
        $this->assertEquals('test@example.com', $payload['data']['user']['email']);
        $this->assertEquals('Test User', $payload['data']['user']['fullname']);
        $this->assertEquals(['user'], $payload['data']['user']['permissions']);
        $this->assertEquals(['url' => 'https://example.com/avatar.png'], $payload['data']['avatar']);
        $this->assertEquals('2099-12-31T23:59:59.000Z', $payload['data']['expiresAt']);
    }
}
