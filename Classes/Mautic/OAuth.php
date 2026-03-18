<?php

declare(strict_types=1);

/*
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) Leuchtfeuer Digital Marketing <dev@leuchtfeuer.com>
 */

namespace Leuchtfeuer\Mautic\Mautic;

use Mautic\Auth\AuthInterface;

/**
 * @method bool validateAccessToken()
 * @method bool accessTokenUpdated()
 * @method array getAccessTokenData()
 */
class OAuth implements AuthInterface
{
    protected string $baseUrl;

    public function __construct(protected AuthInterface $authorization, string $baseUrl, protected string $accesToken = '', protected string $authorizationMode = '')
    {
        // @extensionScannerIgnoreLine
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function __call(mixed $method, array $arguments): mixed
    {
        if (!is_callable([$this->authorization, $method])) {
            throw new \BadMethodCallException(sprintf('Method "%s" does not exist!', $method), 1530044605);
        }

        return call_user_func_array([$this->authorization, $method], $arguments);
    }

    public function getBaseUrl(): string
    {
        // @extensionScannerIgnoreLine
        return $this->baseUrl;
    }

    /**
     * Check if current authorization is still valid
     *
     * @return bool
     */
    #[\Override]
    public function isAuthorized(): bool
    {
        return $this->authorization->isAuthorized();
    }

    /**
     * Make a request to server
     *
     * @param string $url
     * @param string $method
     *
     * @return array
     */
    #[\Override]
    public function makeRequest($url, array $parameters = [], $method = 'GET', array $settings = [])
    {
        return $this->authorization->makeRequest($url, $parameters, $method, $settings);
    }
}
