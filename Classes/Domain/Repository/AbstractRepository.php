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

namespace Leuchtfeuer\Mautic\Domain\Repository;

use Leuchtfeuer\Mautic\Mautic\AuthorizationFactory;
use Leuchtfeuer\Mautic\Mautic\OAuth;
use Mautic\Api\Api;
use Mautic\Exception\ContextNotFoundException;
use Mautic\MauticApi;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\SingletonInterface;

abstract class AbstractRepository implements LoggerAwareInterface, SingletonInterface
{
    use LoggerAwareTrait;

    protected OAuth $authorization;

    protected MauticApi $mauticApi;

    public function __construct(AuthorizationFactory $authorizationFactory)
    {
        $this->authorization ??= $authorizationFactory::createAuthorizationFromExtensionConfiguration();
        $this->mauticApi = new MauticApi();
        $this->injectApis();
    }

    abstract protected function injectApis(): void;

    /**
     * @throws ContextNotFoundException
     */
    protected function getApi(string $apiContext): Api
    {
        // @extensionScannerIgnoreLine
        return $this->mauticApi->newApi($apiContext, $this->authorization, $this->authorization->getBaseUrl());
    }
}
