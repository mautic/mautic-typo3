<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Mautic;

/***
 *
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2020 Florian Wessels <f.wessels@Leuchtfeuer.com>, Leuchtfeuer Digital Marketing
 *
 ***/

use Bitmotion\Mautic\Domain\Model\AccessTokenData;
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

    public function __construct(AuthInterface $authorization, string $baseUrl)
    {
        $this->authorization = $authorization;
        $this->baseUrl = rtrim($baseUrl, '/');
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
        if ($this->authorization->validateAccessToken(false)) {
            if ($this->authorization->accessTokenUpdated()) {
                AccessTokenData::set($this->authorization->getAccessTokenData());
            }
        }
        return $this->authorization->makeRequest($url, $parameters, $method, $settings);
    }
}
