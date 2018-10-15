<?php

namespace MageSuite\AutoOrderCompletion\Test\Integration\Observer;

class AutoCompleteAfterOrderSaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->orderFactory = $this->objectManager->get(\Magento\Sales\Model\OrderFactory::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoAdminConfigFixture autocompletion/settings/auto_invoicing_enabled 1
     * @magentoAdminConfigFixture autocompletion/settings/auto_shipment_enabled 1
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testItCompletesOrderAutomaticallyOnOrderSave()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderFactory->create();
        $order->loadByIncrementId('100000001');

        $order->setCustomerEmail('customer1@null.com');
        $order->save();

        $order = $this->orderFactory->create();
        $order->loadByIncrementId('100000001');

        /** @var \Magento\Sales\Model\Order $order */
        $this->assertEquals(1, $order->hasInvoices());
        $this->assertEquals(1, $order->hasShipments());
        $this->assertEquals(\Magento\Sales\Model\Order::STATE_COMPLETE, $order->getStatus());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoAdminConfigFixture autocompletion/settings/auto_invoicing_enabled 0
     * @magentoAdminConfigFixture autocompletion/settings/auto_shipment_enabled 0
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testOrderIsNotCompletedAutomaticallyWhenAutoInvoicingAndShippmentAreDisabled()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderFactory->create();
        $order->loadByIncrementId('100000001');

        $order->setCustomerEmail('customer1@null.com');
        $order->save();

        $order = $this->orderFactory->create();
        $order->loadByIncrementId('100000001');

        /** @var \Magento\Sales\Model\Order $order */
        $this->assertEquals(0, $order->hasInvoices());
        $this->assertEquals(0, $order->hasShipments());
        $this->assertEquals(\Magento\Sales\Model\Order::STATE_PROCESSING, $order->getStatus());
    }
}