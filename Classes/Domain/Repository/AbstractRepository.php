<?php

declare(strict_types=1);
namespace Bitmotion\Mautic\Domain\Repository;

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

use Bitmotion\Mautic\Mautic\AuthorizationFactory;
use Mautic\Api\Api;
use Mautic\Exception\ContextNotFoundException;
use Mautic\MauticApi;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\SingletonInterface;

abstract class AbstractRepository implements LoggerAwareInterface, SingletonInterface
{
    use LoggerAwareTrait;

    protected $authorization;

    protected $mauticApi;

    public function __construct(AuthorizationFactory $authorizationFactory)
    {
        $this->authorization = $this->authorization ?? $authorizationFactory::createAuthorizationFromExtensionConfiguration();
        $this->mauticApi = new MauticApi();
        $this->injectApis();
    }

    abstract protected function injectApis(): void;

    /**
     * @throws ContextNotFoundException
     */
    protected function getApi(string $apiContext): Api
    {
        return $this->mauticApi->newApi($apiContext, $this->authorization, $this->authorization->getBaseUrl());
    }
}
