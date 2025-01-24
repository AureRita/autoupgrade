<?php

namespace PrestaShop\Module\AutoUpgrade\Router\Middlewares;

use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

abstract class AbstractMiddleware
{
    /**
     * @var UpgradeContainer
     */
    protected $upgradeContainer;

    public function __construct(UpgradeContainer $upgradeContainer)
    {
        $this->upgradeContainer = $upgradeContainer;
    }

    abstract public function process(): ?string;
}
