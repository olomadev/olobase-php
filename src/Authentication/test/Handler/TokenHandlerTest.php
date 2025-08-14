<?php

declare(strict_types=1);

namespace Authentication\Tests\Handler;

use Authentication\Handler\TokenHandler;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterPluginManager;
use Mezzio\Authentication\UserInterface;
use Olobase\Authentication\JwtAuth\JwtAuthenticationInterface;
use Olobase\Authentication\JwtAuth\TokenInterface;
use Olobase\Authentication\Util\TokenEncryptHelper;
use Olobase\Filter\AttributeInputFilterCollector;
use Olobase\Validation\ValidationErrorFormatterInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;

use function json_decode;

class TokenHandlerTest extends TestCase
{
    public function testSuccessfulTokenGeneration(): void
    {
        $requestBody = [
            'username' => 'test@example.com',
            'password' => 'secret',
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
        $collector = $this->getMockBuilder(AttributeInputFilterCollector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collector->method('fromObject')->willReturn($inputFilter);

        // Mock User
        $user = $this->createMock(UserInterface::class);
        $user->method('getDetails')->willReturn([
            'id'         => 1,
            'firstName'  => 'Test Name',
            'avatar_url' => 'https://example.com/avatar.png',
        ]);
        $user->method('getIdentity')->willReturn('test@example.com');
        $user->method('getRoles')->willReturn(['user']);

        // Mock tokenClass (anon class)
        $tokenClass = new class () implements TokenInterface {
            public function decodeToken(string $token)
            {
            }

            public function generateTOken(ServerRequestInterface $request, $expiration = null)
            {
                return [
                    'token' => 'abc.def.ghi',
                    'data'  => [
                        'roles'   => ['user'],
                        'details' => [
                            'firstName'  => 'Test Name',
                            'email'      => 'test@example.com',
                            'avatar_url' => 'https://example.com/avatar.png',
                        ],
                        'meta'    => [
                            'expiresAt' => '2099-12-31T23:59:59Z',
                        ],
                    ],
                ];
            }

            public function refreshToken(ServerRequestInterface $request, array $decoded, $expiration = null)
            {
            }

            public function getTokenEncrypt(): TokenEncryptHelper
            {
                return new TokenEncryptHelper();
            }
        };

        // Mock Authentication
        $auth = $this->createMock(JwtAuthenticationInterface::class);
        $auth->method('authenticateWithCredentials')->willReturn($user);
        $auth->method('getToken')->willReturn($tokenClass);

        // Error formatter mock
        $errorFormatter = $this->createMock(ValidationErrorFormatterInterface::class);

        // Handler instance
        $handler = new TokenHandler([], $auth, $this->createMock(InputFilterPluginManager::class), $errorFormatter);

        // Inject mock collector (via Reflection since it's not normally injectable)
        $reflection = new ReflectionClass($handler);
        $property   = $reflection->getProperty('pluginManager');
        $property->setAccessible(true);
        $property->setValue($handler, new class ($collector) extends InputFilterPluginManager {
            private $collector;
            public function __construct($collector)
            {
                $this->collector = $collector;
            }

            public function get($name, ?array $options = null)
            {
                return $this->collector;
            }
        });

        // test
        $response = $handler->handle($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $payload = json_decode((string) $response->getBody(), true);

        // var_dump($payload);
        // die;

        $this->assertEquals('abc.def.ghi', $payload['token']);
        $this->assertEquals('test@example.com', $payload['data']['details']['email']);
        $this->assertEquals('Test Name', $payload['data']['details']['firstName']);
        $this->assertEquals(['user'], $payload['data']['roles']);
        $this->assertEquals('https://example.com/avatar.png', $payload['data']['details']['avatar_url']);
        $this->assertEquals('2099-12-31T23:59:59Z', $payload['data']['meta']['expiresAt']);
    }
}
