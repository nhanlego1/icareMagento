<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 04/10/2016
 * Time: 11:21
 */

namespace Icare\Deposit\Block\Adminhtml\View\Tabs;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class MakePayment extends \Magento\Backend\Block\Widget implements \Magento\Backend\Block\Widget\Tab\TabInterface{

    protected $_template = 'view/tabs/payment.phtml';
    
    protected $_objectManager;

    public $maxTotalTrans = 0;

    /**
     * @var null
     */
    private $_form = NULL;

    /**
     * Form factory
     *
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $_formFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceFormatter;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\FormFactory $formFactory,
        PriceCurrencyInterface $priceFormatter,
        array $data = []
    ) {
        $this->_formFactory = $formFactory;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->priceFormatter = $priceFormatter;
        parent::__construct($context, $data);
    }

    /**
     * Return Tab label
     *
     * @return string
     * @api
     */
    public function getTabLabel() {
        return __("Make a payment");
    }

    /**
     * Return Tab title
     *
     * @return string
     * @api
     */
    public function getTabTitle() {
        // TODO: Implement getTabTitle() method.
        return __("Make a Payment");
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @api
     */
    public function canShowTab() {
        // TODO: Implement canShowTab() method.
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     * @api
     */
    public function isHidden() {
        // TODO: Implement isHidden() method.
        return false;
    }

    public function getForm(){
        if($this->_form == NULL)
            $this->_form =  $this->getLayout()->createBlock('Icare\Deposit\Block\Adminhtml\Payment\Create\Form');
        return $this->_form;
    }

    /**
     * Preparing block layout
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareLayout()
    {

        $this->getToolbar()->addChild(
            'back_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Back'),
                'onclick' => "window.location.href = '" . $this->getUrl('*/*') . "'",
                'class' => 'action-back'
            ]
        );

        $this->getToolbar()->addChild(
            'reset_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Reset'),
                'onclick' => 'javascript:void(0);',
                'class' => 'reset',
                'id' => 'reset_payment_form'
            ]
        );





        $this->getToolbar()->addChild(
            'save_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Save Payment'),
                'data_attribute' => [
                    'role' => 'payment-save',
                ],
                'class' => 'save primary',
                'id' => 'submit_payment_form',
                'onclick' => 'javascript:void(0)',

            ]
        );


        return parent::_prepareLayout();
    }
    
    
    public function getTotalReceivale($user_id = FALSE) {
        if($user_id==FALSE)
            $user_id = $this->getRequest()->getParam('user_id');
        if ($user_id) {
            $userOm = $this->_objectManager->get('Magento\User\Model\User');
            $user = $userOm->load($user_id);
            $storeId = $user->getStoreId();
            $store = $this->_storeManager->getStore($storeId);
            $currencyCode = $store->getCurrentCurrency()->getCode();
            $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();

            $select = $connection->select()->from('icare_deposit',
            ['(sum(amount) - (SELECT IFNULL(sum(transaction_amount), 0) FROM icare_deposit_payment WHERE user_id = icare_deposit.user_id)) AS total_amount']
            )->where('icare_deposit.user_id = ?', $user_id
            )->group('icare_deposit.user_id');

            $row = $this->maxTotalTrans = $connection->fetchOne($select);
            return $this->priceFormatter->format($row, false, null, null, $currencyCode);
        }
    }
}