<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Icare\Sales\Block\Adminhtml\Order\Create;

use Icare\Custom\Helper\Custom;
/**
 * Order create errors block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Messages extends \Magento\Sales\Block\Adminhtml\Order\Create\Messages
{
    /**
     * Preparing global layout
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        $object = \Magento\Framework\App\ObjectManager::getInstance();
        $session =  $object->get('Magento\Backend\Model\Session\Quote');
        $quote = $session->getQuote();
        $customerId = $session->getCustomerId();
        $total = $quote->getBaseGrandTotal();
        if($total > 0){
            try {
                $check = Custom::create()->checkCreditDueLimitBefore($total, $customerId);
                if(!$check){
                    $this->messageManager->addError(__('Credit Limit and Due Limit is not enough to place order. Please contact to customer to get more credit limit and due limit.'));
                }
            }
            catch (\Exception $ex) {
                $this->_logger->error('Unexpected error occured: '.$ex->getMessage(), array('error' => $ex));
                $this->messageManager->addError(__('Error occured when checking accounting limits: %1', array($ex->getMessage())));
            }
        }

        parent::_prepareLayout();
    }
}
