<?php

namespace MageSuite\AutoOrderCompletion\Observer;

class AutoCompleteAfterOrderSave implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \MageSuite\AutoOrderCompletion\Service\InvoiceCreator
     */
    private $invoiceCreator;

    /**
     * @var \MageSuite\AutoOrderCompletion\Service\ShipmentCreator
     */
    private $shipmentCreator;

    public function __construct(
        \MageSuite\AutoOrderCompletion\Service\InvoiceCreator $invoiceCreator,
        \MageSuite\AutoOrderCompletion\Service\ShipmentCreator $shipmentCreator
    )
    {
        $this->invoiceCreator = $invoiceCreator;
        $this->shipmentCreator = $shipmentCreator;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getData('order');

        if (!$order) {
            return;
        }

        try {
            $this->invoiceCreator->createInvoice($order);
            $this->shipmentCreator->createShipment($order);
        } catch (\Exception $exception) {
            $order->addStatusHistoryComment(
                sprintf('Automatic completion of order was not possible, exception message: %s', $exception->getMessage()),
                false
            );
        }
    }
}