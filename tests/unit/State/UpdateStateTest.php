<?php

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Parameters\FileConfigurationStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\State\UpdateState;

class UpdateStateTest extends TestCase
{
    private $fileConfigurationStorageMock;
    /** @var UpdateState */
    private $state;

    protected function setUp(): void
    {
        $this->fileConfigurationStorageMock = $this->createMock(FileConfigurationStorage::class);
        $this->state = new UpdateState($this->fileConfigurationStorageMock);
    }

    public function testExportOfData(): void
    {
        $this->state->setCurrentVersion('1.7.8.6');
        $this->state->setDestinationVersion('9.0.0');
        $this->state->setProgressPercentage(20);

        $expected = [
            'currentVersion' => '1.7.8.6',
            'destinationVersion' => '9.0.0',
            'installedLanguagesIso' => [],
            'backupCompleted' => false,
            'warning_exists' => false,

            'progressPercentage' => '20',
        ];

        $this->assertEquals($expected, $this->state->export());
    }

    public function testClassReceivesProperty(): void
    {
        $this->state->importFromArray(['currentVersion' => '8.2.0']);
        $exported = $this->state->export();

        $this->assertSame('8.2.0', $this->state->getCurrentVersion());
        $this->assertSame('8.2.0', $exported['currentVersion']);
    }

    public function testClassIgnoresRandomData(): void
    {
        $this->state->importFromArray([
            'wow' => 'epic',
            'currentVersion' => '8.2.0',
        ]);
        $exported = $this->state->export();

        $this->assertFalse(isset($exported['wow']));
        $this->assertSame('8.2.0', $this->state->getCurrentVersion());
        $this->assertSame('8.2.0', $exported['currentVersion']);
    }

    // Tests with encoded data

    public function testClassReceivesPropertyFromEncodedData(): void
    {
        $data = [
            'nextParams' => [
                'currentVersion' => '8.2.0',
                'backupCompleted' => false,
            ],
        ];
        $encodedData = base64_encode(json_encode($data));
        $this->state->importFromEncodedData($encodedData);
        $exported = $this->state->export();

        $this->assertSame('8.2.0', $this->state->getCurrentVersion());
        $this->assertSame('8.2.0', $exported['currentVersion']);
    }

    public function testLoadState(): void
    {
        $savedState = [
            'currentVersion' => '1.6.1.24',
            'destinationVersion' => '9.2.3',
            'backupCompleted' => true,
            'installedLanguagesIso' => ['fr', 'de'],
        ];

        $this->fileConfigurationStorageMock
            ->expects($this->once())
            ->method('load')
            ->with(UpgradeFileNames::STATE_UPDATE_FILENAME)
            ->willReturn($savedState);

        $this->state->load();

        $this->assertEquals('1.6.1.24', $this->state->getCurrentVersion());
        $this->assertEquals('9.2.3', $this->state->getDestinationVersion());
        $this->assertTrue($this->state->isBackupCompleted());
        $this->assertEquals(['fr', 'de'], $this->state->getInstalledLanguagesIso());
    }

    public function testProgressionValue(): void
    {
        $this->assertSame(null, $this->state->getProgressPercentage());

        $this->state->setProgressPercentage(0);
        $this->assertSame(0, $this->state->getProgressPercentage());

        $this->state->setProgressPercentage(55);
        $this->assertSame(55, $this->state->getProgressPercentage());

        // Percentage cannot go down, an exception will be thrown
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Updated progress percentage cannot be lower than the currently set one.');

        $this->state->setProgressPercentage(10);
    }
}
