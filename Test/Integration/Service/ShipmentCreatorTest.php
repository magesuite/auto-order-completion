<?php

namespace MageSuite\AutoOrderCompletion\Test\Integration\Service;

class ShipmentCreatorTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var \MageSuite\AutoOrderCompletion\Service\ShipmentCreator
     */
    private $shipmentCreator;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->shipmentCreator = $this->objectManager->get(\MageSuite\AutoOrderCompletion\Service\ShipmentCreator::class);
        $this->orderFactory = $this->objectManager->get(\Magento\Sales\Model\OrderFactory::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoAdminConfigFixture autocompletion/settings/auto_invoicing_enabled 1
     * @magentoAdminConfigFixture autocompletion/settings/auto_shipment_enabled 1
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testItCreatesShipment()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderFactory->create();
        $order->loadByIncrementId('100000001');

        $this->assertEquals(0, $order->hasShipments());

        $this->shipmentCreator->createShipment($order);

        $order = $this->orderFactory->create();
        $order->loadByIncrementId('100000001');

        $this->assertEquals(1, $order->hasShipments());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoAdminConfigFixture autocompletion/settings/auto_invoicing_enabled 1
     * @magentoAdminConfigFixture autocompletion/settings/auto_shipment_enabled 1
     * @magentoDataFixture Magento/Sales/_files/shipment.php
     */
    public function testItDoesNotCreateShipmentWhenThereIsOneAlready()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderFactory->create();
        $order->loadByIncrementId('100000001');

        $this->assertEquals(1, $order->hasShipments());

        $this->shipmentCreator->createShipment($order);

        $order = $this->orderFactory->create();
        $order->loadByIncrementId('100000001');

        $this->assertEquals(1, $order->hasShipments());
    }
}
