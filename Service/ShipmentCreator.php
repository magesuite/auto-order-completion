<?php

namespace MageSuite\AutoOrderCompletion\Service;

class ShipmentCreator
{
    /**
     * @var \Magento\Sales\Model\Order\ShipmentFactory
     */
    protected $shipmentFactory;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \MageSuite\AutoOrderCompletion\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \MageSuite\AutoOrderCompletion\Helper\Configuration $configuration
    ) {
        $this->shipmentFactory = $shipmentFactory;
        $this->transactionFactory = $transactionFactory;
        $this->configuration = $configuration;
    }

    public function execute(\Magento\Sales\Model\Order $order)
    {
        if (!$this->configuration->isAutoShippingEnabled($order->getStoreId())) {
            return;
        }

        if (!$order->canShip() || $order->hasShipments()) {
            return;
        }

        $items = [];

        foreach ($order->getItems() as $item) {
            $items[$item->getId()] = $item->getQtyOrdered();
        }

        $shipment = $this->shipmentFactory->create($order, $items);
        $shipment->register();

        $transaction = $this->transactionFactory->create();
        $transaction->addObject($shipment)
            ->addObject($order)
            ->save();

        $order->addCommentToStatusHistory('Order was automatically shipped');
    }
}
