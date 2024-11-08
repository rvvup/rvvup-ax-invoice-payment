<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Test\Unit\Model\Environment;

use InvalidArgumentException;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Rvvup\AxInvoicePayment\Model\Environment\GetEnvironmentVersions;
use Rvvup\AxInvoicePayment\Model\Environment\GetEnvironmentVersionsInterface;

/**
 * @covers GetEnvironmentVersions
 */
class GetEnvironmentVersionsTest extends TestCase
{
    /**
     * @var false|string
     */
    private $phpVersion;

    /**
     * @var CacheInterface&MockObject
     */
    private $cache;

    /**
     * @var File&MockObject
     */
    private $fileIo;

    /**
     * @var SerializerInterface&MockObject
     */
    private $serializer;

    /**
     * @var ComposerInformation&MockObject
     */
    private $composerInformation;

    /**
     * @var GetEnvironmentVersions
     */
    private $getEnvironmentVersions;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->phpVersion = phpversion();
        $this->cache = $this->getMockBuilder(CacheInterface::class)->disableOriginalConstructor()->getMock();
        $this->composerInformation = $this->getMockBuilder(ComposerInformation::class)->disableOriginalConstructor()->getMock();
        $this->fileIo = $this->getMockBuilder(File::class)->disableOriginalConstructor()->getMock();
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)->disableOriginalConstructor()->getMock();
        $logger = $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()->getMock();

        $productMetadata = $this->getMockBuilder(ProductMetadataInterface::class)->disableOriginalConstructor()->getMock();
        $productMetadata->expects($this->once())->method('getName')->willReturn('Magento');
        $productMetadata->expects($this->once())->method('getEdition')->willReturn('Community');
        $productMetadata->expects($this->once())->method('getVersion')->willReturn('2.4.4');

        $this->getEnvironmentVersions = new GetEnvironmentVersions(
            $this->cache,
            $productMetadata,
            $this->composerInformation,
            $this->fileIo,
            $this->serializer,
            $logger
        );
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->phpVersion = null;
        $this->cache = null;
        $this->composerInformation = null;
        $this->fileIo = null;
        $this->serializer = null;
        $this->getEnvironmentVersions = null;
    }

    public function testInstalledViaComposer(): void
    {
        $this->composerInformation->expects($this->once())->method('getInstalledMagentoPackages')->willReturn([
            'rvvup/module-ax-invoice-payment' => [
                'version' => '0.1.0'
            ],
        ]);
        $this->assertEquals(
            $this->getExpectedEnvironmentVersions(),
            $this->getEnvironmentVersions->execute(),
            "Unexpected value when testing composer-based install"
        );
    }

    public function testInstalledInAppCode(): void
    {
        $this->composerInformation->expects($this->once())->method('getInstalledMagentoPackages')->willReturn([]);
        $path = $this->getPath();
        $this->fileIo->expects($this->once())->method('fileExists')->with($path)->willReturn(true);
        $this->fileIo->expects($this->once())->method('read')->with($path)->willReturn('{"version": "0.0.1"}');
        $this->serializer->expects($this->once())->method('unserialize')->willReturn(['version' => '0.1.0']);

        $this->assertEquals(
            $this->getExpectedEnvironmentVersions(),
            $this->getEnvironmentVersions->execute(),
            "Unexpected value when testing app/code-based install"
        );
    }

    public function testComposerJsonMissingVersionInAppCode(): void
    {
        $this->composerInformation->expects($this->once())->method('getInstalledMagentoPackages')->willReturn([]);
        $path = $this->getPath();
        $this->fileIo->expects($this->once())->method('fileExists')->with($path)->willReturn(true);
        $this->fileIo->expects($this->once())->method('read')->with($path)->willReturn('{}');
        $this->serializer->expects($this->once())->method('unserialize')->willReturn([]);

        $this->assertEquals(
            $this->getExpectedEnvironmentVersions(GetEnvironmentVersionsInterface::UNKNOWN_VERSION),
            $this->getEnvironmentVersions->execute(),
            "Unexpected value when testing missing composer file"
        );
    }

    public function testCorruptComposerJsonInAppCode(): void
    {
        $this->composerInformation->expects($this->once())->method('getInstalledMagentoPackages')->willReturn([]);
        $path = $this->getPath();
        $this->fileIo->expects($this->once())->method('fileExists')->with($path)->willReturn(true);
        $this->fileIo->expects($this->once())->method('read')->with($path)->willReturn('some corrupt data');
        $this->serializer->expects($this->once())->method('unserialize')->willThrowException(new InvalidArgumentException());

        $this->assertEquals(
            $this->getExpectedEnvironmentVersions(GetEnvironmentVersionsInterface::UNKNOWN_VERSION),
            $this->getEnvironmentVersions->execute(),
            "Unexpected value when testing corrupt composer file fallback"
        );
    }

    public function testEmptyComposerJsonInAppCode(): void
    {
        $this->composerInformation->expects($this->once())->method('getInstalledMagentoPackages')->willReturn([]);
        $path = $this->getPath();
        $this->fileIo->expects($this->once())->method('fileExists')->with($path)->willReturn(true);
        $this->fileIo->expects($this->once())->method('read')->with($path)->willReturn('');

        $this->assertEquals(
            $this->getExpectedEnvironmentVersions(GetEnvironmentVersionsInterface::UNKNOWN_VERSION),
            $this->getEnvironmentVersions->execute(),
            "Unexpected value when testing corrupt composer file fallback"
        );
    }

    public function testSuccessfulFallbackIfUnableToLocateVersion(): void
    {
        $this->composerInformation->expects($this->once())->method('getInstalledMagentoPackages')->willReturn([]);
        $this->fileIo->expects($this->once())->method('fileExists')->with($this->getPath())->willReturn(false);

        $this->assertEquals(
            $this->getExpectedEnvironmentVersions(GetEnvironmentVersionsInterface::UNKNOWN_VERSION),
            $this->getEnvironmentVersions->execute(),
            "Unexpected value when testing missing composer version key fallback"
        );
    }

    public function testGeneratedEnvironmentVersionIsRetrievedFromCache(): void
    {
        $encodedEnvironmentVersions = json_encode($this->getExpectedEnvironmentVersions());

        $this->cache
            ->expects($this->exactly(2))
            ->method('load')
            ->with(GetEnvironmentVersionsInterface::RVVUP_ENVIRONMENT_VERSIONS_AX)
            ->willReturnOnConsecutiveCalls(
                false,
                $encodedEnvironmentVersions
            );

        $this->composerInformation
            ->expects($this->once())
            ->method('getInstalledMagentoPackages')
            ->willReturn([
                'rvvup/module-ax-invoice-payment' => [
                    'version' => '0.1.0'
                ]
            ]);

        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->willReturn($encodedEnvironmentVersions);

        $this->serializer
            ->expects($this->once())
            ->method('unserialize')
            ->willReturn($this->getExpectedEnvironmentVersions());

        $this->cache->expects($this->once())->method('save')->willReturn(true);

        $this->assertEquals(
            $this->getExpectedEnvironmentVersions(),
            $this->getEnvironmentVersions->execute(),
            'Unexpected value when saving/loading from cache'
        );

        $this->assertEquals(
            $this->getExpectedEnvironmentVersions(),
            $this->getEnvironmentVersions->execute(),
            'Unexpected value when saving/loading from cache'
        );
    }

    /**
     * Expected values of the `execute` method return.
     *
     * @param string $rvvupModuleVersion
     * @return array
     */
    private function getExpectedEnvironmentVersions(string $rvvupModuleVersion = '0.1.0'): array
    {
        return [
            'rvvup_module_version' => $rvvupModuleVersion,
            'php_version' => $this->phpVersion,
            'magento_version' => [
                'name' => 'Magento',
                'edition' => 'Community',
                'version' => '2.4.4'
            ]
        ];
    }

    /**
     * Get the path to the composer.json file.
     *
     * @return string
     */
    private function getPath(): string
    {
         return dirname((new ReflectionClass(GetEnvironmentVersions::class))->getFileName())
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'composer.json';
    }
}
