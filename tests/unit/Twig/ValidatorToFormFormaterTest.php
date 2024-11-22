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

namespace PrestaShop\Module\AutoUpgrade\Tests\Twig;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Twig\ValidatorToFormFormater;

class ValidatorToFormFormaterTest extends TestCase
{
    public function testEmptyArray()
    {
        $input = [];
        $expected = [];

        $actual = ValidatorToFormFormater::format($input);

        $this->assertSame($expected, $actual);
    }

    public function testErrorsForSpecificFields()
    {
        $input = [
            [
                'message' => 'Oh no',
                'target' => 'oneDisgustingField',
            ],
        ];
        $expected = [
            'oneDisgustingField' => 'Oh no',
        ];

        $actual = ValidatorToFormFormater::format($input);

        $this->assertSame($expected, $actual);
    }

    public function testGlobalErrors()
    {
        $input = [
            [
                'message' => 'Eww',
            ],
        ];
        $expected = [
            'global' => 'Eww',
        ];

        $actual = ValidatorToFormFormater::format($input);

        $this->assertSame($expected, $actual);
    }

    public function testMixedErrors()
    {
        $input = [
            [
                'message' => 'This field cannot be blank',
                'target' => 'theMandatoryField',
            ],
            [
                'message' => 'Eww',
            ],
            [
                'message' => 'Oh no',
                'target' => 'oneDisgustingField',
            ],
        ];
        $expected = [
            'theMandatoryField' => 'This field cannot be blank',
            'global' => 'Eww',
            'oneDisgustingField' => 'Oh no',
        ];

        $actual = ValidatorToFormFormater::format($input);

        $this->assertSame($expected, $actual);
    }
}
