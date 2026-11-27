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

namespace Leuchtfeuer\Mautic\Controller;

use Leuchtfeuer\Mautic\Domain\Model\Dto\YamlConfiguration;
use Leuchtfeuer\Mautic\Service\MauticAuthorizeService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class BackendController extends ActionController
{
    public const FLASH_MESSAGE_QUEUE = 'marketingautomation.mautic.flashMessages';

    private const LANGUAGE_FILE = 'LLL:EXT:mautic/Resources/Private/Language/locallang_mod.xlf:';

    public function __construct(
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly Context $context,
    ) {}

    public function showAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $emConfiguration = new YamlConfiguration();
        /** @var MauticAuthorizeService $authorizeService */
        $authorizeService = GeneralUtility::makeInstance(MauticAuthorizeService::class);

        if ($authorizeService->validateCredentials() === true) {
            if (!$authorizeService->validateAccessToken()) {
                if ($authorizeService->accessTokenToBeRefreshed()) {
                    $emConfiguration->reloadConfigurations();
                } else {
                    $moduleTemplate->assign('authorizeButton', $authorizeService->getAuthorizeButton());
                }
            } else {
                $authorizeService->checkConnection();
            }
        }

        $moduleTemplate->assign('configuration', $emConfiguration);
        $moduleTemplate->assign('expiresInfo', $this->buildExpiresInfo($emConfiguration->getExpires()));
        return $moduleTemplate->renderResponse('Backend/Show');
    }

    /**
     * Renders the access token expiry timestamp in a human readable way, e.g.
     * "2026-08-12 15:42:07 (expires in 58 min)".
     */
    protected function buildExpiresInfo(int $expires): string
    {
        if ($expires <= 0) {
            return $this->translate('authorization.expires.none');
        }

        $seconds = $expires - (int)$this->context->getPropertyFromAspect('date', 'timestamp');
        $interval = $this->formatInterval(abs($seconds));

        return sprintf(
            '%s (%s)',
            date('Y-m-d H:i:s', $expires),
            $seconds >= 0
                ? $this->translate('authorization.expires.in', [$interval])
                : $this->translate('authorization.expires.ago', [$interval])
        );
    }

    /**
     * Formats a number of seconds using language neutral units, e.g. "3 d 4 h".
     */
    protected function formatInterval(int $seconds): string
    {
        $units = ['d' => 86400, 'h' => 3600, 'min' => 60];
        $parts = [];

        foreach ($units as $unit => $length) {
            if ($seconds >= $length) {
                $parts[] = intdiv($seconds, $length) . ' ' . $unit;
                $seconds %= $length;
            }
            if (count($parts) === 2) {
                return implode(' ', $parts);
            }
        }

        return $parts === [] ? $seconds . ' s' : implode(' ', $parts);
    }

    /**
     * @param list<string> $arguments Replacements for the sprintf placeholders of the label
     */
    protected function translate(string $key, array $arguments = []): string
    {
        return LocalizationUtility::translate(self::LANGUAGE_FILE . $key, null, $arguments) ?? $key;
    }

    public function resetAuthorizationAction(): ResponseInterface
    {
        /** @var MauticAuthorizeService $authorizeService */
        $authorizeService = GeneralUtility::makeInstance(MauticAuthorizeService::class);
        $authorizeService->resetTokens();

        $this->addFlashMessage(
            $this->translate('authorization.reset.message'),
            $this->translate('authorization.reset.title'),
            ContextualFeedbackSeverity::OK,
        );

        return $this->redirect('show');
    }

    public function saveAction(array $configuration): ResponseInterface
    {
        $emConfiguration = new YamlConfiguration();

        if (str_ends_with((string)$configuration['baseUrl'], '/')) {
            $configuration['baseUrl'] = rtrim((string)$configuration['baseUrl'], '/');
        }

        if (!in_array($emConfiguration->getAccessToken(), ['', '0'], true) && !$emConfiguration->isSameCredentials($configuration)) {
            $configuration['accessToken'] = '';
        }

        $configuration['timeout'] = YamlConfiguration::normalizeTimeout($configuration['timeout'] ?? null);

        $emConfiguration->save($configuration);
        return $this->redirect('show');
    }
}
