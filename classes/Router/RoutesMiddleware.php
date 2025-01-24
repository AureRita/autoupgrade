<?php

namespace PrestaShop\Module\AutoUpgrade\Router;

use PrestaShop\Module\AutoUpgrade\Router\Middlewares\UpdateIsConfigured;
use PrestaShop\Module\AutoUpgrade\Router\Middlewares\LocalChannelXmlAndZipExist;

class RoutesMiddleware
{
    const UPDATE_IS_CONFIGURED = UpdateIsConfigured::class;
    const LOCAL_CHANNEL_XML_AND_ZIP_EXIST = LocalChannelXmlAndZipExist::class;
}
