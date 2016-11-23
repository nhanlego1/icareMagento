<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 10/5/16
 * Time: 1:56 PM
 */

namespace Icare\Sales\Block\Adminhtml\Shipment;


use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class ShipButton implements ButtonProviderInterface
{
    protected $_urlBuilder;
    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        $this->_urlBuilder = $context->getUrlBuilder();
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->canShip()) {
            $data = [
                'label' => __('Ship'),
                'class' => 'primary',
                'id' => 'ship_button',
                'on_click' => 'return false',
                'data_attribute' => [
                    'url' => $this->getShipUrl(),
                ],
            ];
        }
        return $data;
    }

    protected function canShip() {
        return true;
    }

    protected function getShipUrl() {
        return $this->_urlBuilder->getUrl('sales/shipment/shipaction');
    }
}