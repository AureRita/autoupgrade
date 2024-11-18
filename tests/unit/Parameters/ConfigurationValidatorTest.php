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

namespace Parameters;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Parameters\ConfigurationValidator;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class ConfigurationValidatorTest extends TestCase
{
    /** @var ConfigurationValidator */
    private $validator;

    protected function setUp()
    {
        $translator = $this->createMock(Translator::class);

        $translator->method('trans')->willReturnCallback(function ($string, $params) {
            return sprintf($string, ...$params);
        });

        $this->validator = new ConfigurationValidator($translator);
    }

    public function testValidateChannelSuccess()
    {
        $result = $this->validator->validate([UpgradeConfiguration::CHANNEL => 'online']);
        $this->assertEmpty($result);

        $result = $this->validator->validate([UpgradeConfiguration::CHANNEL => 'local']);
        $this->assertEmpty($result);
    }

    public function testValidateChannelFail()
    {
        $channel = 'toto';

        $result = $this->validator->validate([UpgradeConfiguration::CHANNEL => $channel]);
        $this->assertEquals([
            [
                'message' => 'Unknown channel ' . $channel,
                'target' => UpgradeConfiguration::CHANNEL,
            ],
        ], $result);
    }

    public function testValidateZipSuccess()
    {
        $result = $this->validator->validate([UpgradeConfiguration::ARCHIVE_ZIP => 'prestashop.zip']);
        $this->assertEmpty($result);

        $result = $this->validator->validate([UpgradeConfiguration::CHANNEL => 'local', UpgradeConfiguration::ARCHIVE_ZIP => 'prestashop.zip']);
        $this->assertEmpty($result);

        $result = $this->validator->validate([UpgradeConfiguration::CHANNEL => 'online', UpgradeConfiguration::ARCHIVE_ZIP => '']);
        $this->assertEmpty($result);
    }

    public function testValidateZipFail()
    {
        $result = $this->validator->validate([UpgradeConfiguration::CHANNEL => 'local', UpgradeConfiguration::ARCHIVE_ZIP => '']);
        $this->assertEquals([
            [
                'message' => 'No zip archive provided',
                'target' => UpgradeConfiguration::ARCHIVE_ZIP,
            ],
        ], $result);
    }

    public function testValidateXmlSuccess()
    {
        $result = $this->validator->validate([UpgradeConfiguration::ARCHIVE_XML => 'prestashop.xml']);
        $this->assertEmpty($result);

        $result = $this->validator->validate([UpgradeConfiguration::CHANNEL => 'local', UpgradeConfiguration::ARCHIVE_XML => 'prestashop.xml']);
        $this->assertEmpty($result);

        $result = $this->validator->validate([UpgradeConfiguration::CHANNEL => 'online', UpgradeConfiguration::ARCHIVE_XML => '']);
        $this->assertEmpty($result);
    }

    public function testValidateXmlFail()
    {
        $result = $this->validator->validate([UpgradeConfiguration::CHANNEL => 'local', UpgradeConfiguration::ARCHIVE_XML => '']);
        $this->assertEquals([
            [
                'message' => 'No xml archive provided',
                'target' => UpgradeConfiguration::ARCHIVE_XML,
            ],
        ], $result);
    }

    public function testValidateBoolSuccess()
    {
        $validValues = ['1', '0', 'true', 'false', 'on', 'off', true, false];

        foreach ($validValues as $value) {
            $result = $this->validator->validate([UpgradeConfiguration::PS_AUTOUP_CUSTOM_MOD_DESACT => $value]);
            $this->assertEmpty($result);
        }
    }

    public function testValidateBoolFail()
    {
        $result = $this->validator->validate([UpgradeConfiguration::PS_AUTOUP_CUSTOM_MOD_DESACT => 'toto']);
        $this->assertEquals([
            [
                'message' => 'Value must be a boolean for ' . UpgradeConfiguration::PS_AUTOUP_CUSTOM_MOD_DESACT,
                'target' => UpgradeConfiguration::PS_AUTOUP_CUSTOM_MOD_DESACT,
            ],
        ], $result);

        $result = $this->validator->validate([UpgradeConfiguration::PS_AUTOUP_CUSTOM_MOD_DESACT => '']);
        $this->assertEquals([
            [
                'message' => 'Value must be a boolean for ' . UpgradeConfiguration::PS_AUTOUP_CUSTOM_MOD_DESACT,
                'target' => UpgradeConfiguration::PS_AUTOUP_CUSTOM_MOD_DESACT,
            ],
        ], $result);
    }

    public function testValidateMultipleInputFail()
    {
        $result = $this->validator->validate([
            UpgradeConfiguration::CHANNEL => 'local',
            UpgradeConfiguration::ARCHIVE_ZIP => '',
            UpgradeConfiguration::ARCHIVE_XML => '',
        ]);

        $this->assertEquals([
            [
                'message' => 'No zip archive provided',
                'target' => UpgradeConfiguration::ARCHIVE_ZIP,
            ],
            [
                'message' => 'No xml archive provided',
                'target' => UpgradeConfiguration::ARCHIVE_XML,
            ],
        ], $result);
    }
}
