<?php

namespace MageSuite\AutoOrderCompletion\Test\Integration\Service;

class InvoiceCreatorTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var \MageSuite\AutoOrderCompletion\Service\InvoiceCreator
     */
    private $invoiceCreator;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->invoiceCreator = $this->objectManager->get(\MageSuite\AutoOrderCompletion\Service\InvoiceCreator::class);
        $this->orderFactory = $this->objectManager->get(\Magento\Sales\Model\OrderFactory::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoAdminConfigFixture autocompletion/settings/auto_invoicing_enabled 1
     * @magentoAdminConfigFixture autocompletion/settings/auto_shipment_enabled 1
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testItCreatesInvoice()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderFactory->create();
        $order->loadByIncrementId('100000001');

        $this->assertEquals(0, $order->hasInvoices());

        $this->invoiceCreator->createInvoice($order);

        $order = $this->orderFactory->create();
        $order->loadByIncrementId('100000001');

        $this->assertEquals(1, $order->hasInvoices());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoAdminConfigFixture autocompletion/settings/auto_invoicing_enabled 1
     * @magentoAdminConfigFixture autocompletion/settings/auto_shipment_enabled 1
     * @magentoDataFixture Magento/Sales/_files/invoice.php
     */
    public function testItDoesNotCreateInvoiceWhenThereIsOneAlready()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderFactory->create();
        $order->loadByIncrementId('100000001');

        $this->assertEquals(1, $order->hasInvoices());

        $this->invoiceCreator->createInvoice($order);

        $order = $this->orderFactory->create();
        $order->loadByIncrementId('100000001');

        $this->assertEquals(1, $order->hasInvoices());
    }
}
