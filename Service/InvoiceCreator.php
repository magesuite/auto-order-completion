<?php

namespace MageSuite\AutoOrderCompletion\Service;

class InvoiceCreator
{
    /**
     * @var \Magento\Sales\Api\InvoiceManagementInterface
     */
    protected $invoiceManagement;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \MageSuite\AutoOrderCompletion\Helper\Configuration
     */
    protected $configuration;

    /**
     * @param \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param \MageSuite\AutoOrderCompletion\Helper\Configuration $configuration
     */
    public function __construct(
        \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \MageSuite\AutoOrderCompletion\Helper\Configuration $configuration
    ) {
        $this->invoiceManagement = $invoiceManagement;
        $this->transactionFactory = $transactionFactory;
        $this->configuration = $configuration;
    }

    public function execute(\Magento\Sales\Model\Order $order)
    {
        if (!$this->configuration->isAutoInvoicingEnabled($order->getStoreId())) {
            return;
        }

        if (!$order->getPayment() || !$order->canInvoice()) {
            return;
        }

        $invoice = $this->invoiceManagement->prepareInvoice($order);
        $invoice->register();

        $order->setIsInProcess(true);
        $order->addCommentToStatusHistory('Order was automatically invoiced');

        $transaction = $this->transactionFactory->create();
        $transaction->addObject($order)
            ->addObject($invoice)
            ->save();
    }
}
