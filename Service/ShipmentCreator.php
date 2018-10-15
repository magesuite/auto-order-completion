<?php

namespace MageSuite\AutoOrderCompletion\Service;

class ShipmentCreator
{

    /**
     * @var \Magento\Sales\Model\Order\ShipmentFactory
     */
    private $shipmentFactory;

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
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \MageSuite\AutoOrderCompletion\Helper\Config $configHelper
    )
    {
        $this->shipmentFactory = $shipmentFactory;
        $this->transactionFactory = $transactionFactory;
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->configHelper = $configHelper;
    }

    public function createShipment(\Magento\Sales\Model\Order $order)
    {
        if (!$this->configHelper->isAutoShippingEnabled()) {
            return;
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepositoryInterface->get($order->getId());

        if (!$order->canShip()) {
            return;
        }

        if ($order->hasShipments()) {
            return;
        }

        $items = [];
        foreach ($order->getItems() as $item) {
            $items[$item->getId()] = $item->getQtyOrdered();
        }

        $shipment = $this->shipmentFactory->create($order, $items);
        $shipment->register();

        $transaction = $this->transactionFactory->create();

        $transaction
            ->addObject($shipment)
            ->addObject($order)
            ->save();

        $order->addStatusHistoryComment('Order was automatically shipped', false);
    }
}