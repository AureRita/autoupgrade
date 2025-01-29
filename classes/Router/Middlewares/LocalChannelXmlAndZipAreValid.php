<?php

namespace PrestaShop\Module\AutoUpgrade\Router\Middlewares;

use PrestaShop\Module\AutoUpgrade\Router\Routes;

class LocalChannelXmlAndZipAreValid extends AbstractMiddleware
{
    public function process(): ?string
    {
        $updateConfiguration = $this->upgradeContainer->getUpdateConfiguration();

        if ($updateConfiguration->isChannelLocal()) {
            $errors = $this->upgradeContainer->getLocalChannelConfigurationValidator()->validate($updateConfiguration->toArray());

            if (!empty($errors)) {
                return Routes::UPDATE_PAGE_VERSION_CHOICE;
            }
        }

        return null;
    }
}
