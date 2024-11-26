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

namespace Services;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Exceptions\ZipActionException;
use PrestaShop\Module\AutoUpgrade\Services\LocalVersionFilesService;
use PrestaShop\Module\AutoUpgrade\Services\PrestashopVersionService;

class LocalVersionFilesServiceTest extends TestCase
{
    /** @var PrestashopVersionService */
    private $prestashopVersionService;

    public function setUp()
    {
        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('An issue with this version of PHPUnit and PHP 8+ prevents this test to run.');
        }

        $this->prestashopVersionService = $this->getMockBuilder(PrestashopVersionService::class)
            ->disableOriginalConstructor()
            ->setMethods(['extractPrestashopVersionFromZip', 'extractPrestashopVersionFromXml'])
            ->getMock();
    }

    /**
     * @throws ZipActionException
     */
    public function testGetLocalVersionsFiles()
    {
        $this->prestashopVersionService->method('extractPrestashopVersionFromZip')
            ->withConsecutive(
                ['8.1.7.zip'],
                ['1.7.8.10.zip']
            )
            ->willReturnOnConsecutiveCalls(
                '8.1.7',
                '1.7.8.10'
            );
        $this->prestashopVersionService->method('extractPrestashopVersionFromXml')
            ->withConsecutive(
                ['8.1.7.xml'],
                ['1.7.8.10.xml']
            )
            ->willReturnOnConsecutiveCalls(
                '8.1.7',
                '1.7.8.10'
            );

        $localVersionFilesService = $this->getMockBuilder(LocalVersionFilesService::class)
            ->setConstructorArgs([$this->prestashopVersionService, 'fakeDownloadPath', '1.7.8.11'])
            ->setMethods(['getAllFilesFromFolder'])
            ->getMock();

        $localVersionFilesService->method('getAllFilesFromFolder')
            ->withConsecutive(
                ['fakeDownloadPath', LocalVersionFilesService::TYPE_ZIP],
                ['fakeDownloadPath', LocalVersionFilesService::TYPE_XML]
            )
            ->willReturnOnConsecutiveCalls(
                ['8.1.7.zip', '1.7.8.10.zip'],
                ['8.1.7.xml', '1.7.8.10.xml']
            );

        $expected = [
            '8.1.7' => [
                'xml' => ['8.1.7.xml'],
                'zip' => ['8.1.7.zip'],
            ],
        ];

        $this->assertEquals($expected, $localVersionFilesService->getLocalVersionsFiles());
    }

    /**
     * @throws ZipActionException
     */
    public function testGetLocalVersionsFilesWithMissingFile()
    {
        $this->prestashopVersionService->method('extractPrestashopVersionFromZip')
            ->withConsecutive(
                ['8.1.7.zip'],
                ['9.0.0.zip']
            )
            ->willReturnOnConsecutiveCalls(
                '8.1.7',
                '9.0.0'
            );
        $this->prestashopVersionService->method('extractPrestashopVersionFromXml')
            ->withConsecutive(
                ['8.1.7.xml']
            )
            ->willReturnOnConsecutiveCalls(
                '8.1.7'
            );

        $localVersionFilesService = $this->getMockBuilder(LocalVersionFilesService::class)
            ->setConstructorArgs([$this->prestashopVersionService, 'fakeDownloadPath', '1.7.8.11'])
            ->setMethods(['getAllFilesFromFolder'])
            ->getMock();

        $localVersionFilesService->method('getAllFilesFromFolder')
            ->withConsecutive(
                ['fakeDownloadPath', LocalVersionFilesService::TYPE_ZIP],
                ['fakeDownloadPath', LocalVersionFilesService::TYPE_XML]
            )
            ->willReturnOnConsecutiveCalls(
                ['8.1.7.zip', '9.0.0.zip'],
                ['8.1.7.xml']
            );

        $expected = [
            '8.1.7' => [
                'xml' => ['8.1.7.xml'],
                'zip' => ['8.1.7.zip'],
            ],
        ];

        $this->assertEquals($expected, $localVersionFilesService->getLocalVersionsFiles());
    }
}
