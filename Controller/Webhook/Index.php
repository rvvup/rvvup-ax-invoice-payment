<?php
declare(strict_types=1);

namespace Rvvup\AxInvoicePayment\Controller\Webhook;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Rvvup\AxInvoicePayment\Api\UpdateInvoice;
use Rvvup\AxInvoicePayment\Block\Info;
use Rvvup\Payments\Model\ConfigInterface;

class Index implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /** @var Info */
    private $infoBlock;

    /** @var RequestInterface */
    private $request;

    /** @var ResultFactory */
    private $resultFactory;

    /** @var ConfigInterface */
    private $config;

    /** @var SerializerInterface */
    private $json;

    /** @var UpdateInvoice */
    private $updateInvoice;

    /**
     * @param Info $infoBlock
     * @param RequestInterface $request
     * @param ResultFactory $resultFactory
     * @param SerializerInterface $json
     * @param ConfigInterface $config
     * @param UpdateInvoice $updateInvoice
     */
    public function __construct(
        Info $infoBlock,
        RequestInterface $request,
        ResultFactory $resultFactory,
        SerializerInterface $json,
        ConfigInterface $config,
        UpdateInvoice $updateInvoice
    ) {
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->infoBlock = $infoBlock;
        $this->config = $config;
        $this->json = $json;
        $this->updateInvoice = $updateInvoice;
    }

    public function execute(): ResultInterface
    {
        $merchantId = $this->request->getParam('merchant_id', false);
        $checkoutId = $this->request->getParam('checkout_id', false);
        $source = $this->request->getParam('application_source', false);
        $storeId = $this->request->getParam('store_id', null);

        if (!$source || $source !== 'MAGENTO_AX_INVOICE') {
            return $this->returnExceptionResponse();
        }
        if ($merchantId !== $this->config->getMerchantId()) {
            return $this->returnExceptionResponse();
        }
        if (!$checkoutId) {
            return $this->returnExceptionResponse();
        }
        try {
            $data = $this->infoBlock->getCheckoutData($checkoutId, $storeId);
            if (isset($data['status'])) {
                if ($data['status'] == 'COMPLETED') {
                    $invoices = $this->json->unserialize($data['metadata']['invoices']);
                    $payedInvoices = $this->json->unserialize($data['metadata']['selected_invoices']);
                    $ids = [];
                    foreach ($invoices as $invoice) {
                        $isPayed = $invoice['is_payed'];
                        $invoiceNumber = $invoice['invoice_number'];

                        if (!$isPayed) {
                            if (in_array($invoiceNumber, $payedInvoices)) {
                                $ids[] = $invoiceNumber;
                            }
                        } else {
                            $ids[] = $invoiceNumber;
                        }
                    }

                    foreach ($ids as $id) {
                        $this->updateInvoice->updateInvoiceById((int) $id);
                    }
                }
            }
        } catch (\Exception $exception) {
            return $this->returnExceptionResponse();
        }
        return $this->returnSuccessfulResponse();
    }

    /**
     * @return ResultInterface
     */
    private function returnSuccessfulResponse(): ResultInterface
    {
        $response = $this->resultFactory->create($this->resultFactory::TYPE_RAW);
        /**
         * https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/202
         * 202 Accepted: request has been accepted for processing, but the processing has not been completed
         */
        $response->setHttpResponseCode(202);

        return $response;
    }

    private function returnExceptionResponse(): ResultInterface
    {
        $response = $this->resultFactory->create($this->resultFactory::TYPE_JSON);
        $response->setHttpResponseCode(500);

        return $response;
    }

    /** @inheritDoc  */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /** @inheritDoc  */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }
}
