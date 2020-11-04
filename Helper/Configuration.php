<?php

namespace MageSuite\AutoOrderCompletion\Helper;

class Configuration extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_AUTO_INVOICING_ENABLED = 'autocompletion/settings/auto_invoicing_enabled';
    const XML_PATH_AUTO_SHIPMENT_ENABLED = 'autocompletion/settings/auto_shipment_enabled';

    /**
     * @param null|int $storeId
     * @return bool
     */
    public function isAutoInvoicingEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_AUTO_INVOICING_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     * @return bool
     */
    public function isAutoShippingEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_AUTO_SHIPMENT_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
