<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/20/16
 * Time: 6:23 PM
 */

namespace Icare\Sales\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class ReadyToShipAction extends Column
{
    const URL_ACTION_PRINT_PDF_SHIPMENT = 'sales/shipment/print';
    /** @var UrlInterface */
    protected $urlBuilder;
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id'])) {
                    $urlEntityParamName = $this->getData('config/urlEntityParamName') ?: 'entity_id';
                    $html = '<a onclick="location.reload();" href="'. $this->urlBuilder->getUrl(self::URL_ACTION_PRINT_PDF_SHIPMENT, [$urlEntityParamName => $item['entity_id'], 'isPick' => 0]) .'" target="_blank"><button>Print</button></a>';
                    $html .= '&nbsp;&nbsp;<a href="'. $this->urlBuilder->getUrl(self::URL_ACTION_PRINT_PDF_SHIPMENT, [$urlEntityParamName => $item['entity_id'], 'isPick' => 1]) .'"><button>Pickup</button></a>';
                    $item[$this->getData('name')] = $html;
                }
            }
        }

        return $dataSource;
    }
}