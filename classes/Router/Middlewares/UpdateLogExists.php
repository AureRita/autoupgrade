<?php

namespace PrestaShop\Module\AutoUpgrade\Router\Middlewares;

use PrestaShop\Module\AutoUpgrade\Router\Routes;

class UpdateLogExists extends AbstractMiddleware
{
    public function process(): ?string
    {
        $activeUpdateLogFile = $this->upgradeContainer->getLogsState()->getActiveRestoreLogFile();
        $activeUpdateLogPath = $this->upgradeContainer->getProperty($this->upgradeContainer::LOGS_PATH) . DIRECTORY_SEPARATOR . $activeUpdateLogFile;

        if ($activeUpdateLogFile === null
            || !$this->upgradeContainer->getFileStorage()->exists($activeUpdateLogPath)) {
            return Routes::HOME_PAGE;
        }

        return null;
    }
}
