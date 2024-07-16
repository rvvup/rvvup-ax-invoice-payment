<?php

declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Model\Config\Jwt;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;

class Validator extends ArraySerialized
{
    /** @var EncryptorInterface */
    private $encryptor;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param EncryptorInterface $encryptor
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param Json|null $serializer
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        EncryptorInterface $encryptor,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        Json $serializer = null
    ) {
        $this->encryptor = $encryptor;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data,
            $serializer)
        ;}

    public function beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            unset($value['__empty']);
        }
        foreach ($value as $key => $element) {
            $validate = $this->validate($element);
            if (!$validate['is_encrypted']) {
                $element['jwt_key'] = $this->encryptor->encrypt($element['jwt_key']);
            }
            $value[$key] = $element;
        }
        $this->setValue($value);
        return parent::beforeSave();
    }

    /**
     * @param string $jwt
     * @return void
     * @throws ValidatorException
     */
    private function validate(array $element): array
    {
        $encrypted = false;
        $parts = explode('.', $element['jwt_key']);
        $message = __('API key is invalid for company ' . $element['company']);
        if (count($parts) !== 3) {
            $value = $this->encryptor->decrypt($element['jwt_key']);
            $parts = explode('.', $value);
            $encrypted = true;
            if (count($parts) !== 3) {
                throw new ValidatorException($message);
            }
        }

        $payloadString = base64_decode($parts[1], true);
        if (false === $payloadString) {
            throw new ValidatorException($message);
        }
        return ['is_encrypted' => $encrypted];
    }
}
