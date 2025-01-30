<?php

namespace PrestaShop\Module\AutoUpgrade\Router\Middlewares;

use PrestaShop\Module\AutoUpgrade\Router\Routes;

class UpdateLogExists extends AbstractMiddleware
{
    public function process(): ?string
    {
        if ($this->upgradeContainer->getLogsState()->getActiveUpdateLogFile() === null) {
            return Routes::HOME_PAGE;
        }

        return null;
    }
}
