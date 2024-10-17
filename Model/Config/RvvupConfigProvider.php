<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Serialize\Serializer\Json;

class RvvupConfigProvider
{
    public const PAYMENT_RVVUP_AX_INTEGRATION = 'payment/rvvup_ax_integration';

    /** @var ScopeConfigInterface */
    private $config;

    /** @var Json */
    private $serializer;

    /** @var EncryptorInterface */
    private $encryptor;

    /**
     * @param ScopeConfigInterface $config
     * @param Json $serializer
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ScopeConfigInterface $config,
        Json $serializer,
        EncryptorInterface $encryptor
    ) {
        $this->config = $config;
        $this->serializer = $serializer;
        $this->encryptor = $encryptor;
    }

    /**
     * @param string $companyId
     * @return array
     * @throws InputException
     */
    public function getConfig(string $companyId): array
    {
        $jwt = $this->getDecryptedJwt($companyId);
        $jwtParts = explode('.', $jwt);
        list($head, $body, $crypto) = $jwtParts;
        $unwrappedJwtBody = $this->serializer->unserialize(base64_decode($body));
        return [
            'auth_token' => $jwt,
            'merchant_id' => (string) $unwrappedJwtBody['merchantId'],
            'endpoint' => (string) $unwrappedJwtBody['aud'],
        ];
    }

    /**
     * @param string $companyId
     * @return string
     * @throws InputException
     */
    private function getDecryptedJwt(string $companyId): string
    {
        $value = $this->config->getValue(self::PAYMENT_RVVUP_AX_INTEGRATION . '/company_jwt_mapping');
        $value = $this->serializer->unserialize($value);
        foreach ($value as $entry) {
            if ($entry['company'] === $companyId) {
                return $this->encryptor->decrypt($entry['api_key']);
            }
        }
        throw new InputException(__('There is no saved company named ' . $companyId));
    }
}