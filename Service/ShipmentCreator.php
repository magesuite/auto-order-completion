<?php

namespace MageSuite\AutoOrderCompletion\Service;

class ShipmentCreator
{
    /**
     * @var \Magento\Sales\Model\Convert\OrderFactory
     */
    protected $convertOrderFactory;

    /**
     * @var \Magento\Sales\Model\Order\ShipmentFactory
     */
    protected $shipmentFactory;

    /**
     * @var \MageSuite\AutoOrderCompletion\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \Magento\Sales\Model\Convert\OrderFactory $convertOrderFactory,
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \MageSuite\AutoOrderCompletion\Helper\Configuration $configuration
    ) {
        $this->convertOrderFactory = $convertOrderFactory;
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

        $convertOrder = $this->convertOrderFactory->create();
        $shipment = $convertOrder->toShipment($order);

        foreach ($order->getAllItems() as $orderItem) {
            if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }

            $qtyShipped = $orderItem->getQtyToShip();
            $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
            $shipment->addItem($shipmentItem);
        }

        $shipment->register();
        $shipment->getOrder()->setIsInProcess(true);
        $shipment->save();
        $shipment->getOrder()->addCommentToStatusHistory('Order was automatically shipped')->save();
    }
}
