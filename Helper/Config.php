<?php

namespace MageSuite\AutoOrderCompletion\Helper;

class Config
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isAutoInvoicingEnabled()
    {
        return $this->scopeConfig->getValue('autocompletion/settings/auto_invoicing_enabled') == 1;
    }

    public function isAutoShippingEnabled()
    {
        return $this->scopeConfig->getValue('autocompletion/settings/auto_shipment_enabled') == 1;
    }
}