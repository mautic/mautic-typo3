<?php

namespace Mautic\Mautic\Controller;

use Mautic\Auth\ApiAuth;
use Mautic\Auth\OAuth;
use Mautic\Mautic\Service\MauticService;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Created by PhpStorm.
 * User: woeler
 * Date: 26.01.18
 * Time: 16:12.
 */
class AuthorisationController extends ActionController
{
    /**
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    public function listAction()
    {
        if (!$this->checkSessionActive()) {
            session_start();
        }

        $service = new MauticService();

        $registry = GeneralUtility::makeInstance('TYPO3\CMS\Core\Registry');

        $settings = [];

        if (empty($service->getConfigurationData('mauticUrl'))
            || empty($service->getConfigurationData('mauticPublicKey'))
            || empty($service->getConfigurationData('mauticSecretKey'))) {
            if (!empty($service->getConfigurationData('mauticUsername'))
                && !empty($service->getConfigurationData('mauticPassword'))) {
                $this->view->assign('message', 'Your Mautic Basic Auth credentials have been validated. You are all set.');
            } else {
                $this->view->assign('message', 'Your Mautic extension configuration is incomplete and cannot use OAuth at this point. If you are using basic auth, you can ignore this message.');
            }

            $registry->set('tx_mautic', 'accessToken', '');
            $registry->set('tx_mautic', 'accessTokenSecret', '');
        } else {
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

            $accessTokenData = [
                'accessToken'        => $registry->get('tx_mautic', 'accessToken', ''),
                'accessTokenSecret'  => $registry->get('tx_mautic', 'accessTokenSecret', ''),
                'accessTokenExpires' => '',
            ];

            $settings = [
                'baseUrl'      => $service->getConfigurationData('mauticUrl'),
                'clientKey'    => $service->getConfigurationData('mauticPublicKey'),
                'clientSecret' => $service->getConfigurationData('mauticSecretKey'),
                'callback'     => 'http://sitetemplate.woeler.beech.it'.$uriBuilder->buildUriFromRoute('MauticOAuth'),
                'version'      => 'OAuth1a',
            ];

            if (!empty($accessTokenData['accessToken']) && !empty($accessTokenData['accessTokenSecret'])) {
                $settings['accessToken']        = $accessTokenData['accessToken'];
                $settings['accessTokenSecret']  = $accessTokenData['accessTokenSecret'];
                $settings['accessTokenExpires'] = $accessTokenData['accessTokenExpires'];
            }

            $api  = new ApiAuth();
            $auth = $api->newAuth($settings);

            if ($auth instanceof OAuth && $auth->validateAccessToken()) {
                $this->view->assign('message', 'Your Mautic OAuth credentials have been validated. You are all set.');
                $this->view->assign('authorisation', 'true');
                if ($auth->accessTokenUpdated()) {
                    $accessTokenData = $auth->getAccessTokenData();

                    $registry->set('tx_mautic', 'accessToken', $accessTokenData['access_token']);
                    $registry->set('tx_mautic', 'accessTokenSecret', $accessTokenData['access_token_secret']);
                }
            }
        }

        if (null === $service->testAuth()) {
            $this->view->assign('message', 'Could not connect to Mautic. Your credentials are incorrect.');

            return;
        }

        $this->view->assign('version', $service->testAuth()['version']);
        $this->view->assign('auth_version', $service->testAuth()['auth_version']);
        $this->view->assign('mauticUrl', $service->getConfigurationData('mauticUrl'));
    }

    /**
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    public function saveTokensAction()
    {
        if (!$this->checkSessionActive()) {
            session_start();
        }

        $service = new MauticService();

        $registry = GeneralUtility::makeInstance('TYPO3\CMS\Core\Registry');

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        $accessTokenData = [
            'accessToken'        => $registry->get('tx_mautic', 'accessToken', ''),
            'accessTokenSecret'  => $registry->get('tx_mautic', 'accessTokenSecret', ''),
            'accessTokenExpires' => '',
        ];

        $settings = [
            'baseUrl'      => $service->getConfigurationData('mauticUrl'),
            'clientKey'    => $service->getConfigurationData('mauticPublicKey'),
            'clientSecret' => $service->getConfigurationData('mauticSecretKey'),
            'callback'     => 'http://sitetemplate.woeler.beech.it'.$uriBuilder->buildUriFromRoute('MauticOAuth'),
            'version'      => 'OAuth1a',
        ];

        if (!empty($accessTokenData['accessToken']) && !empty($accessTokenData['accessTokenSecret'])) {
            $settings['accessToken']        = $accessTokenData['accessToken'];
            $settings['accessTokenSecret']  = $accessTokenData['accessTokenSecret'];
            $settings['accessTokenExpires'] = $accessTokenData['accessTokenExpires'];
        }

        $api  = new ApiAuth();
        $auth = $api->newAuth($settings);

        if ($auth->validateAccessToken()) {
            //            $this->view->assign('message', 'Your Mautic OAuth credentials have been validated. You are all set.');
//            $this->view->assign('authorisation', 'true');
            if ($auth->accessTokenUpdated()) {
                $accessTokenData = $auth->getAccessTokenData();

                $registry->set('tx_mautic', 'accessToken', $accessTokenData['access_token']);
                $registry->set('tx_mautic', 'accessTokenSecret', $accessTokenData['access_token_secret']);
            }

//            $this->view->assign('version', $service->testAuth()['version']);
//            $this->view->assign('auth_version', $service->testAuth()['auth_version']);
//            $this->view->assign('mauticUrl', $service->getConfigurationData('mauticUrl'));
        }
    }

    protected function checkSessionActive()
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }
}
