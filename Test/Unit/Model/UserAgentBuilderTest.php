<?php declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Test\Unit\Model;

use Magento\Framework\App\CacheInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Rvvup\AxInvoicePayment\Model\Environment\GetEnvironmentVersionsInterface;
use Rvvup\AxInvoicePayment\Model\UserAgentBuilder;

/**
 * @covers \Rvvup\AxInvoicePayment\Model\UserAgentBuilder
 */
class UserAgentBuilderTest extends TestCase
{
    /** @var string */
    private $phpVersion;
    /** @var CacheInterface|MockObject */
    private $cache;
    /** @var UserAgentBuilder */
    private $userAgentBuilder;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->phpVersion = phpversion();
        $this->cache = $this->getMockBuilder(CacheInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $getEnvironmentVersionsMock = $this->getMockBuilder(GetEnvironmentVersionsInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $getEnvironmentVersionsMock->expects($this->once())
            ->method('execute')
            ->willReturn([
                'rvvup_module_version' => '0.1.0',
                'php_version' => $this->phpVersion,
                'magento_version' => [
                    'name' => 'Magento',
                    'edition' => 'Community',
                    'version' => '2.4.4'
                ]
            ]);

        $this->userAgentBuilder = new UserAgentBuilder($this->cache, $getEnvironmentVersionsMock);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->phpVersion = null;
        $this->cache = null;
        $this->userAgentBuilder = null;
    }

    public function testUserAgentGeneration(): void
    {
        $this->assertEquals(
            'RvvupMagentoAxInvoicePayment/0.1.0; Magento-Community/2.4.4; PHP/' . $this->phpVersion,
            $this->userAgentBuilder->get(),
            "Unexpected value when testing user agent generation"
        );
    }

    public function testGeneratedUserAgentIsRetrievedFromCache(): void
    {
        $expectedUserAgent = 'RvvupMagentoAxInvoicePayment/0.1.0; Magento-Community/2.4.4; PHP/' . $this->phpVersion;
        $this->cache
            ->expects($this->exactly(2))
            ->method('load')
            ->with(UserAgentBuilder::RVVUP_USER_AGENT_STRING)
            ->willReturnOnConsecutiveCalls(
                false,
                $expectedUserAgent
            );

        $this->cache->expects($this->once())->method('save')->with(
            $expectedUserAgent,
            UserAgentBuilder::RVVUP_USER_AGENT_STRING
        );

        $this->assertEquals(
            $expectedUserAgent,
            $this->userAgentBuilder->get(),
            'Unexpected value when saving/loading from cache'
        );
        $this->assertEquals(
            $expectedUserAgent,
            $this->userAgentBuilder->get(),
            'Unexpected value when saving/loading from cache'
        );
    }
}
