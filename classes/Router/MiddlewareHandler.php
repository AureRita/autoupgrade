<?php

namespace PrestaShop\Module\AutoUpgrade\Router;

use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class MiddlewareHandler
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
     * @param Routes::* $routeName
     *
     * @return Routes::*
     */
    public function process(string $routeName): string
    {
        $route = RoutesConfig::ROUTES[$routeName];
        $routeMiddlewares = $route['middleware'] ?? [];

        $nextRoute = $routeName;

        foreach ($routeMiddlewares as $middleware) {
            $processedRoute = (new $middleware($this->upgradeContainer))->process();

            if ($processedRoute !== null) {
                $nextRoute = $processedRoute;
                break;
            }
        }

        return $nextRoute;
    }
}
