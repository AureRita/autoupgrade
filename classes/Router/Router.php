<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\AutoUpgrade\Router;

use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Router
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
     * @param Request $request
     *
     * @return Response|string
     */
    public function handle(Request $request)
    {
        $routeName = $request->query->get('route') ?? Routes::HOME_PAGE;
        $route = array_key_exists($routeName, RoutesConfig::ROUTES) ? $routeName : Routes::ERROR_404;

        $route = (new Middleware($this->upgradeContainer))->process($route);

        if ($routeName !== $route) {
            $this->dirtyRedirectToRoute($request, $route);
        }

        $routeParams = RoutesConfig::ROUTES[$route];
        $method = $routeParams['method'];

        return (new $routeParams['controller']($this->upgradeContainer, $request))->$method();
    }

    /**
     * @param Request $request
     * @param Routes::* $route
     *
     * @return never
     */
    private function dirtyRedirectToRoute(Request $request, string $route): void
    {
        $newUrl = $this->upgradeContainer->getUrlGenerator()->getUrlToRoute($request, $route);

        header('Location: ' . $newUrl, true, 302);
        exit;
    }
}
