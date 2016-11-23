<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Icare\Customer\Block\Adminhtml\Edit\Tab\View;

use Magento\Customer\Controller\RegistryConstants;

use Icare\Mifos\Helper\Mifos;
use Icare\Custom\Helper\Custom;

/**
 * Adminhtml customer recent orders grid block
 */
class Compared extends \Magento\Backend\Block\Widget\Grid\Extended
{
    const CUSTOMER_TABLE = 'customer_entity';
    const CUSTOMER_ENTITY_FIELD = 'entity_id';
    const CREDIT_LIMIT = 'credit_limit';
    const DUE_LIMIT = 'due_limit';
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Grid\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Sales\Model\Resource\Order\Grid\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    )
    {

        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initialize the orders grid.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        //$this->setId('comparedproduct_view_compared_grid');
        $this->setDefaultSort('created_at', 'desc');
        $this->setSortable(false);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }

    protected function _prepareGrid()
    {
        $this->setId('comparedproduct_view_compared_grid' . $this->getWebsiteId());
        parent::_prepareGrid();
    }

    /**
     * {@inheritdoc}
     */
    protected function _preparePage()
    {
        $this->getCollection()->setPageSize(5)->setCurPage(1);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        // $this->getInstallment($this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID));
        $collection = $this->_collectionFactory->create()->setCustomerId(
            $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)
        )->useProductItem()->addAttributeToSelect(array("name", "price"));
        $this->setCollection($collection);
        return parent::_prepareCollection();

    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'accountNo',
            ['header' => __('Saving Account Number'), 'index' => 'accountNo', 'type' => 'number', 'width' => '100px']
        );

        $this->addColumn(
            'totalLoanAmount',
            [
                'header' => __('Total Loan Amount'),
                'index' => 'totalLoanAmount',
            ]
        );

        $this->addColumn(
            'totalLoanBalance',
            [
                'header' => __('Total Loan Balance'),
                'index' => 'totalLoanBalance',
            ]
        );
        $this->addColumn(
            'totalPaidAmount',
            [
                'header' => __('Total Paid Amount'),
                'index' => 'totalPaidAmount',
            ]
        );
        $this->addColumn(
            'totalUsingCreditAmount',
            [
                'header' => __('Using Credit Amount'),
                'index' => 'totalUsingCreditAmount',
            ]
        );
        $this->addColumn(
            'totalUsingDueAmount',
            [
                'header' => __('Using Due Amount'),
                'index' => 'totalUsingDueAmount',
            ]
        );
        $this->addColumn(
            'accountBalance',
            [
                'header' => __('Saving Account Balance'),
                'index' => 'accountBalance',
            ]
        );
        return $this;
        //return parent::_prepareColumns();
    }

    /**
     * Get headers visibility
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getHeadersVisibility()
    {
        return $this->getCollection()->getSize() >= 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('catalog/product/edit', ['id' => $row->getProductId()]);
    }

    /**
     * ge installment
     * @return mixed
     */
    public function getInstallment()
    {

        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $data = null;
        if ($data = Mifos::create()->getInstallment($customerId)) {
            return $data;
        }
        return $data;
    }
    /**
     * get Credit limit and Due Limit
     */
    public function getCreditDueLimit(){
        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $credit = Custom::create()->getCreditDueLimit($customerId);
        return $credit;
    }
}
