<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Mautic;

use Bitmotion\Mautic\Domain\Model\Dto\EmConfiguration;
use Mautic\Auth\AuthInterface;

class OAuth implements AuthInterface
{
    /**
     * @var AuthInterface
     */
    protected $authorization;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $accesToken;

    /**
     * @var string
     */
    protected $authorizationMode;

    public function __construct(AuthInterface $authorization, string $baseUrl, string $accesToken, string $authorizationMode)
    {
        $this->authorization = $authorization;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->accesToken = $accesToken;
        $this->authorizationMode = $authorizationMode;
    }

    public function __call($method, $arguments)
    {
        if (!is_callable([$this->authorization, $method])) {
            throw new \BadMethodCallException(sprintf('Method "%s" does not exist!', $method), 1530044605);
        }

        return call_user_func_array([$this->authorization, $method], $arguments);
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Check if current authorization is still valid
     *
     * @return bool
     */
    public function isAuthorized()
    {
        return $this->authorization->isAuthorized();
    }

    /**
     * Make a request to server using the supported auth method
     *
     * @param string $url
     * @param string $method
     *
     * @return array
     */
    public function makeRequest($url, array $parameters = [], $method = 'GET', array $settings = [])
    {
        if ($this->authorizationMode !== EmConfiguration::OAUTH1_AUTHORIZATION_MODE && $method !== 'GET') {
            $settings['headers']['Authorization'] = sprintf('Authorization: Bearer %s', $this->accesToken);
        }

        return $this->authorization->makeRequest($url, $parameters, $method, $settings);
    }
}
