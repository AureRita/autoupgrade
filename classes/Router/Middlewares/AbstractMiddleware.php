<?php

namespace PrestaShop\Module\AutoUpgrade\Router\Middlewares;

use PrestaShop\Module\AutoUpgrade\Router\Routes;
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

    /**
     * @return Routes::*|null
     *
     * @throws \Exception
     */
    abstract public function process(): ?string;
}
