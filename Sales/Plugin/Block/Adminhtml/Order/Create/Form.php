<?php
/**
 * Copyright © 2016 iCareBenefits . All rights reserved.
 * Created by: nhan_nguyen
 * Date: 14/11/2016
 * Time: 17:32
 */

namespace Icare\Sales\Plugin\Block\Adminhtml\Order\Create;

class Form
{
    /**
     * Json encoder
     *
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * Json encoder
     *
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $_jsonDecoder;

    /**
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     */
    public function __construct(
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_jsonDecoder = $jsonDecoder;
    }

    public function afterGetOrderDataJson(
        \Magento\Framework\View\Element\AbstractBlock $subject,
        $result
    ) {
        $data = $this->_jsonDecoder->decode($result);

        if (!empty($data['addresses'])) {
            foreach ($data['addresses'] as &$address) {
                $address['company'] = $subject->escapeQuote($address['company']);
                $address['street'] = $subject->escapeQuote($address['street']);
                $address['city'] = $subject->escapeQuote($address['city']);
            }
        }

        return $this->_jsonEncoder->encode($data);
    }
}