<?php

namespace PrestaShop\Module\AutoUpgrade\Tests;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Router\Middleware;
use PrestaShop\Module\AutoUpgrade\Router\Routes;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class MiddlewareTest extends TestCase
{
    /**
     * @var Middleware
     */
    private $middleware;

    protected function setUp(): void
    {
        $upgradeContainer = new UpgradeContainer('/html', '/html/admin');
        $this->middleware = new Middleware($upgradeContainer);
    }

    public function testProcessReturnString(): void
    {
        $routeName = Routes::HOME_PAGE;
        $result = $this->middleware->process($routeName);
        assert(is_string($result));
    }

    public function testRouteWithMiddlewareReturnRedirectRoute(): void
    {
        $routeName = Routes::UPDATE_PAGE_UPDATE_OPTIONS;
        $result = $this->middleware->process($routeName);
        assert(is_string($result));
        $this->assertSame(Routes::UPDATE_PAGE_VERSION_CHOICE, $result);
    }

    public function testRouteWithoutMiddlewareReturnItself(): void
    {
        $routeName = Routes::HOME_PAGE;
        $result = $this->middleware->process($routeName);
        $this->assertSame(Routes::HOME_PAGE, $result);
    }
}
