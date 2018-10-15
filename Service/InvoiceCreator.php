<?php

namespace MageSuite\AutoOrderCompletion\Service;

class InvoiceCreator
{

    /**
     * @var \Magento\Sales\Api\InvoiceManagementInterface
     */
    private $invoiceManagement;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepositoryInterface;

    /**
     * @var \MageSuite\AutoOrderCompletion\Helper\Config
     */
    private $configHelper;

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepositoryInterface,
        \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \MageSuite\AutoOrderCompletion\Helper\Config $configHelper
    )
    {
        $this->invoiceManagement = $invoiceManagement;
        $this->transactionFactory = $transactionFactory;
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->configHelper = $configHelper;
    }

    public function createInvoice(\Magento\Sales\Model\Order $order)
    {
        if (!$this->configHelper->isAutoInvoicingEnabled()) {
            return;
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepositoryInterface->get($order->getId());

        if (!$order->getPayment()) {
            return;
        }

        if (!$order->canInvoice()) {
            return;
        }

        $invoice = $this->invoiceManagement->prepareInvoice($order);
        $invoice->register();

        $order->setIsInProcess(true);

        $transaction = $this->transactionFactory->create();

        $transaction
            ->addObject($order)
            ->addObject($invoice)
            ->save();

        $order->addStatusHistoryComment('Order was automatically invoiced', false);
    }
}