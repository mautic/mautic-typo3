<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Domain\Repository;

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

    public function __construct(AuthorizationFactory $authorizationFactory, MauticApi $mauticApi)
    {
        $this->authorization = $this->authorization ?? $authorizationFactory::createAuthorizationFromExtensionConfiguration();
        $this->mauticApi = $mauticApi;
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
