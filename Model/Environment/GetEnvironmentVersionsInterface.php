<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Model\Environment;

interface GetEnvironmentVersionsInterface
{
    public const RVVUP_ENVIRONMENT_VERSIONS_AX = 'rvvup_environment_versions_ax';
    public const UNKNOWN_VERSION = 'unknown';

    /**
     * Get a list of the environment versions including module, magento, and php versions
     *
     * @return array
     */
    public function execute(): array;
}
