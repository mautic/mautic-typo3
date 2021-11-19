<?php

namespace Bitmotion\Mautic\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class MauticOauthToken extends AbstractEntity
{
    /** @var string */
    protected $accessToken = '';

    /** @var string */
    protected $refreshToken = '';

    /** @var int */
    protected $expires;

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getExpires(): int
    {
        return $this->expires;
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function setExpires(int $expires): self
    {
        $this->expires = $expires;

        return $this;
    }
}