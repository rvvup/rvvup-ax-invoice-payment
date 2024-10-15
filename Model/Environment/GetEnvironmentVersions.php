<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Model\Environment;

use Exception;
use InvalidArgumentException;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\Io\IoInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class GetEnvironmentVersions implements GetEnvironmentVersionsInterface
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var ComposerInformation
     */
    private $composerInformation;

    /**
     * Set via di.xml
     *
     * @var IoInterface|File
     */
    private $fileIo;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Set via di.xml
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string|null
     */
    private $cachedEnvironmentVersions;

    /**
     * @param CacheInterface $cache
     * @param ProductMetadataInterface $productMetadata
     * @param ComposerInformation $composerInformation
     * @param IoInterface $fileIo
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @return void
     */
    public function __construct(
        CacheInterface $cache,
        ProductMetadataInterface $productMetadata,
        ComposerInformation $composerInformation,
        IoInterface $fileIo,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->cache = $cache;
        $this->productMetadata = $productMetadata;
        $this->composerInformation = $composerInformation;
        $this->fileIo = $fileIo;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * Get a list of the environment versions including module, magento, and php versions
     *
     * @return array
     */
    public function execute(): array
    {
        $cachedEnvironmentVersions = $this->getCachedEnvironmentVersions();

        // If not null data in cache, return them unserialized.
        if ($cachedEnvironmentVersions !== null) {
            try {
                return $this->serializer->unserialize($cachedEnvironmentVersions);
            } catch (InvalidArgumentException $ex) {
                // Fail silently and allow the rest of the code to get the data;
                $this->logger->error('Failed to decode `cachedEnvironmentVersions` with message: ' . $ex->getMessage());
            }
        }

        $environmentVersions = [
            'rvvup_module_version' => $this->getRvvupModuleVersion(),
            'php_version' => phpversion(),
            'magento_version' => [
                'name' => $this->productMetadata->getName(),
                'edition' => $this->productMetadata->getEdition(),
                'version' => $this->productMetadata->getVersion()
            ]
        ];

        try {
            $this->cache->save($this->serializer->serialize($environmentVersions), self::RVVUP_ENVIRONMENT_VERSIONS);
        } catch (InvalidArgumentException $ex) {
            $this->logger->error(
                'Failed to serialize & save environment version data to cache with message: ' . $ex->getMessage(),
                $environmentVersions
            );
        }

        return $environmentVersions;
    }

    /**
     * Get & set property environment versions from cache.
     * Null it if not string or no value.
     *
     * @return string|null
     */
    private function getCachedEnvironmentVersions(): ?string
    {
        if ($this->cachedEnvironmentVersions === null) {
            $this->cachedEnvironmentVersions = $this->cache->load(self::RVVUP_ENVIRONMENT_VERSIONS);
        }

        if (!is_string($this->cachedEnvironmentVersions) || empty($this->cachedEnvironmentVersions)) {
            $this->cachedEnvironmentVersions = null;
        }

        return $this->cachedEnvironmentVersions;
    }

    /**
     * Get the Rvvup Module version installed either from project's `composer.lock` or `app/code` folder installation.
     *
     * Fallback to unknown.
     *
     * @return string
     */
    public function getRvvupModuleVersion(): string
    {
        // Attempt to figure out what plugin version we have depending on installation method
        $packages = $this->composerInformation->getInstalledMagentoPackages();

        // Get the value from the composer.lock file if set.
        if (isset($packages['rvvup/module-magento-payments']['version'])
            && is_string($packages['rvvup/module-magento-payments']['version'])
        ) {
            return (string) $packages['rvvup/module-magento-payments']['version'];
        }

        // Otherwise, check for `app/code` installation
        $appCodeComposerJsonVersion = $this->getAppCodeComposerJsonVersion();

        // If set use it, otherwise unknown.
        return $appCodeComposerJsonVersion ?? self::UNKNOWN_VERSION;
    }

    /**
     * Get the version from the `composer.json` of the module if module is installed `in app/code`.
     *
     * @return string|null
     */
    private function getAppCodeComposerJsonVersion(): ?string
    {
        // We need to get 2 folders up to the root of the module to find the composer.json
        $fileName = __DIR__
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'composer.json';

        if (!$this->fileIo->fileExists($fileName)) {
            return null;
        }

        try {
            $composerFile = $this->fileIo->read($fileName);

            if (!is_string($composerFile)) {
                $this->logger->debug('Failed to read composer file');

                return null;
            }

            try {
                $composerData = $this->serializer->unserialize($composerFile);
            } catch (InvalidArgumentException $ex) {
                $this->logger->debug('Failed to unserialize content of composer file');

                return null;
            }

            return is_array($composerData) && isset($composerData['version']) && is_string($composerData['version'])
                ? $composerData['version']
                : null;
        } catch (Exception $e) {
            return null;
        }
    }
}
