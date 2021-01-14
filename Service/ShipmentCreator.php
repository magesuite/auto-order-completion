<?php

namespace MageSuite\AutoOrderCompletion\Service;

class ShipmentCreator
{
    /**
     * @var \Magento\Sales\Model\Order\ShipmentFactory
     */
    protected $shipmentFactory;

    /**
     * @var \MageSuite\AutoOrderCompletion\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \MageSuite\AutoOrderCompletion\Helper\Configuration $configuration
    ) {
        $this->shipmentFactory = $shipmentFactory;
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

        foreach ($order->getAllItems() as $orderItem) {
            if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }

            $items[$orderItem->getId()] = $orderItem->getQtyToShip();
        }

        $shipment = $this->shipmentFactory->create($order, $items);
        $shipment->register();
        $shipment->save();

        $order->setIsInProcess(true);
        $order->addCommentToStatusHistory('Order was automatically shipped');
        $order->save();
    }
}
