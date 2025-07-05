<?php
declare(strict_types=1);

namespace Authentication\Model;

use Exception;
use Common\Helper\RequestHelper;
use Authentication\Helper\TokenEncryptHelper;
use Laminas\Cache\Storage\StorageInterface;
use Mezzio\Authentication\UserInterface;
use Olobase\Mezzio\Authentication\JwtEncoderInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;

class TokenModel implements TokenModelInterface
{
    private $conn;
    private $config;

    public function __construct(
        array $config,
        private StorageInterface $cache,
        private TokenEncryptHelper $tokenEncrypt,
        private JwtEncoderInterface $encoder,
        private TableGatewayInterface $users
    )
    {
        $this->users = $users;
        $this->cache = $cache;
        $this->config = $config;
        $this->encoder = $encoder;
        $this->tokenEncrypt = $tokenEncrypt;
        $this->conn = $users->getAdapter()->getDriver()->getConnection();
        
        $sessionTTL = $this->config['token']['session_ttl'] * 60;
        if ($sessionTTL < 10) {
            throw new Exception("Configuration error: Session ttl value cannot be less than 10 minutes.");
        }
    }
    
    /**
     * Decode token
     * 
     * @param  string $token token
     * @return mixed
     */
    public function decode(string $token)
    {
        return $this->encoder->decode($token);
    }
    
    /**
     * Generate token header variables
     * 
     * @param  ServerRequestInterface $request psr7 http request object
     * @return array
     */
    protected function generateHeader(ServerRequestInterface $request, $user = null)
    {
        $server = $request->getServerParams();
        $mtRand     = mt_rand();
        $tokenId    = md5(uniqid((string)$mtRand, true));
        $issuedAt   = time();
        $notBefore  = $issuedAt;
        $expire     = $notBefore + (60 * $this->config['token']['token_validity']);

        if ($user) {
            // create not expired token for REST API
            // 
            if ($user->getDetails()['id'] == 'e6b13fce-c91a-4fbd-93b8-1105f7a59466' || $user->getDetails()['fullname'] == "restapiuser@example.com") {
                $expire = strtotime('+100 years');
            }
        }
        $http       = empty($server['HTTPS']) ? 'http://' : 'https://';
        $issuer     = $http.$server['HTTP_HOST'];
        $userAgent  = empty($server['HTTP_USER_AGENT']) ? 'unknown' : $server['HTTP_USER_AGENT'];
        $deviceKey  = md5($userAgent);
        return [
            $tokenId,
            $issuedAt,
            $notBefore,
            $expire,
            $issuer,
            $deviceKey
        ];
    }

    /**
     * Returns to encoded token with expire date
     *
     * @param  ServerRequestInterface $request request
     * @return array|boolean
     */
    public function create(ServerRequestInterface $request)
    {
        $user = $request->getAttribute(UserInterface::class);
        $userId = $user->getDetails()['id'];
        //
        // JWT header
        //
        list(
            $tokenId,
            $issuedAt,
            $notBefore,
            $expire,
            $issuer
        ) = $this->generateHeader($request, $user);
        //
        // JWT token data
        //
        $jwt = [
            'iat'  => $issuedAt,         // Issued at: time when the token was generated
            'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
            'iss'  => $issuer,           // Issuer
            'nbf'  => $notBefore,        // Not before
            'exp'  => $expire,           // Expire
            'data' => [                  // Data related to the signer user
                'roles' => $user->getRoles(),
                'details' => [
                    'id' => $userId,
                    'email' => $user->getDetail('email') ? $user->getDetail('email') : $user->getIdentity(), // User email
                    'fullname' => $user->getDetail('fullname'),
                    'ip' => $user->getDetail('ip'),
                    'deviceKey' => $user->getDetail('deviceKey'),
                    'tokenId' => $tokenId,
                ],
            ]
        ];
        $token = $this->encoder->encode($jwt);
        //
        // create token session
        //
        $configSessionTTL = (int)$this->config['token']['session_ttl'] * 60;
        $this->cache->getOptions()->setTtl($configSessionTTL);
        $this->cache->setItem(SESSION_KEY.$userId.":".$tokenId, $configSessionTTL);

        return [
            'token' => $this->tokenEncrypt->encrypt($token),
            'tokenId' => $tokenId,
            'expiresAt' => date('Y-m-d H:i:s', $expire),
        ];
    }

    /**
     * Refresh token
     * 
     * @param  ServerRequestInterface $request request
     * @param  array                  $decoded payload
     * @return array|boolean
     */
    public function refresh(ServerRequestInterface $request, array $decoded)
    {
        $server = $request->getServerParams();
        $userAgent = empty($server['HTTP_USER_AGENT']) ? 'unknown' : $server['HTTP_USER_AGENT'];
        $userId = $decoded['data']['details']['id'];
        //
        // validate token session
        //
        $oldTokenId = $decoded['data']['details']['tokenId'];
        $sessionTTL = $this->cache->getItem(SESSION_KEY.$userId.":".$oldTokenId);
        if (! $sessionTTL) {
            return false; // ttl expired
        }
        $expiredAt = $decoded['exp'];
        $now = time();
        if ($expiredAt + (int)$sessionTTL + 10 < $now) {
            return false; // ttl expired
        }  
        //
        // JWT header - renew token
        // 
        list(
            $tokenId,
            $issuedAt,
            $notBefore,
            $expire,
            $issuer,
            $deviceKey
        ) = $this->generateHeader($request);
        //
        // Renew JWT token data
        //
        $decoded['data']['details']['tokenId'] = $tokenId; // renew token id
        $decoded['data']['details']['ip'] = RequestHelper::getRealUserIp(); 
        $decoded['data']['details']['deviceKey'] = $deviceKey; 
        $jwt = [
            'iat'  => $decoded['iat'],  // Issued at: time when the token was generated
            'jti'  => $tokenId,         // Json Token Id: an unique identifier for the token
            'iss'  => $decoded['iss'],  // Issuer
            'nbf'  => $notBefore,       // Not before
            'exp'  => $expire,          // Expire
            'data' => (array)$decoded['data']
        ];
        $newToken = $this->encoder->encode($jwt);
        //
        // refresh the user session
        //
        $configSessionTTL = (int)$this->config['token']['session_ttl'] * 60;
        $this->cache->getOptions()->setTtl($configSessionTTL);
        $this->cache->setItem(SESSION_KEY.$userId.":".$tokenId, $configSessionTTL);
        $this->cache->removeItem(SESSION_KEY.$userId.":".$oldTokenId);

        return [
            'token' => $this->tokenEncrypt->encrypt($newToken),
            'tokenId' => $tokenId,
            'expiresAt' => date("Y-m-d H:i:s", $expire),
            'data' => (array)$decoded['data']
        ];
    }

    /**
     * Kill current token for logout operation
     * 
     * @param  string $userId  user id
     * @param  string $tokenId token id
     * @return void
     */
    public function kill(string $userId, string $tokenId)
    {
        $this->cache->removeItem(SESSION_KEY.$userId.":".$tokenId);
    }
    
    /**
     * Returns to token encryption object
     * 
     * @return object
     */
    public function getTokenEncrypt() : TokenEncryptHelper
    {
        return $this->tokenEncrypt;
    }
}
