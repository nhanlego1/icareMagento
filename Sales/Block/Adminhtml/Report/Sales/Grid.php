<?php 

namespace Icare\Sales\Block\Adminhtml\Report\Sales;

use Magento\Reports\Block\Adminhtml\Grid\AbstractGrid;

/**
 * Overrides to use overrided collection class which read data from more customized filter
 * @author Nam Pham
 *
 */
class Grid extends \Magento\Reports\Block\Adminhtml\Sales\Sales\Grid
{
    protected $_bookmarkMgnt;
    
    protected $_urlBuilder;
    
    protected $_jsonEncoder;
    
    /**
     * 
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $resourceFactory
     * @param \Magento\Reports\Model\Grouped\CollectionFactory $collectionFactory
     * @param \Magento\Reports\Helper\Data $reportsData
     * @param array $data
     */
    public function __construct(
        \Magento\Ui\Api\BookmarkManagementInterface $bookmarkMgnt,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $resourceFactory,
        \Magento\Reports\Model\Grouped\CollectionFactory $collectionFactory,
        \Magento\Reports\Helper\Data $reportsData,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $resourceFactory, $collectionFactory, $reportsData, $data);    
        
        $this->_bookmarkMgnt = $bookmarkMgnt;
        $this->_jsonEncoder = $jsonEncoder;   
        $this->_urlBuilder = $context->getUrlBuilder();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getResourceCollectionName()
    {
        $filterData = $this->getFilterData();
        $userId = $filterData->getData('sales_agent');
        if (empty($userId)) {
            return parent::getResourceCollectionName();
        }
        else {
            return $this->getFilterData()->getData('report_type') == 'updated_at_order'
                ? 'Icare\Sales\Model\ResourceModel\Report\Order\UpdatedAtCollection'
                : 'Icare\Sales\Model\ResourceModel\Report\Order\CreatedAtCollection';
        }
    }
    
    /**
     * overrides to return storeIds if store group or website is selected
     * 
     * {@inheritDoc}
     * @see \Magento\Reports\Block\Adminhtml\Grid\AbstractGrid::_getStoreIds()
     */
    protected function _getStoreIds()
    {
        /** \Magento\Framework\DataObject $filterData **/
        $filterData = $this->getFilterData();
        if ($filterData && empty($filterData->getStoreIds())) {
            if ($value = $this->getRequest()->getParam('website')) {
                $method = 'getWebsiteId';
            }
            elseif ($value = $this->getRequest()->getParam('group')) {
                $method = 'getStoreGroupId';
            }
            
            if ($value) {
                $storeIds = [];
                foreach ($this->_storeManager->getStores() as $store) {
                    $refMethod = new \ReflectionMethod($store, $method);
                    if ($refMethod->invoke($store) == $value) {
                        $storeIds[] = $store->getId();
                    }
                }
                return $storeIds;
            }
        }
        
        return parent::_getStoreIds();
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Magento\Reports\Block\Adminhtml\Grid\AbstractGrid::_addCustomFilter()
     */
    protected function _addCustomFilter($collection, $filterData)
    {
        if ($saleAgentId = $filterData->getData('sales_agent')) {
            // @todo lookup user id
            $collection->setUserId($saleAgentId);
        }
        return parent::_addCustomFilter($collection, $filterData);
    }
    
    /**
     * 
     * @return array bookmark data of sales_order_grid
     * 
     * @see \Magento\Ui\Component\Bookmark::prepare()
     */
    protected function _getBookmarkConfig() 
    {
        $config = [];
        $bookmarks = $this->_bookmarkMgnt->loadByNamespace('sales_order_grid');
        /** @var \Magento\Ui\Api\Data\BookmarkInterface $bookmark */
        foreach ($bookmarks->getItems() as $bookmark) {
            if ($bookmark->isCurrent()) {
                $config['activeIndex'] = $bookmark->getIdentifier();
            }
        
            $config = array_merge_recursive($config, $bookmark->getConfig());
        }
        
        return isset($config['current'])?$config['current']:['filters' => 0];
    }
    
    /**
     * overrides after html to implement click redirect to sales order view
     * {@inheritDoc}
     * @see \Magento\Framework\View\Element\AbstractBlock::_afterToHtml()
     */
    protected function _afterToHtml($html)
    {
        // periodData
        $filterData = [];
        foreach ($this->getCollection() as $item) {
            $filterData[] = $this->_generateFilterData($item);
        }
        $filterJson = $this->_jsonEncoder->encode($filterData);
        
        // config data
        $configData = $this->_getBookmarkConfig();
        $configJson = $this->_jsonEncoder->encode($configData);
        
        $saveUrl = $this->_urlBuilder->getUrl('mui/bookmark/save');
        $redirectUrl = $this->_urlBuilder->getUrl('sales/order/index');
        
        $suffix = <<<HTML
    <div id="sales-order-report-spinner" data-role="spinner" class="admin__data-grid-loading-mask" style="display:none">
        <div class="spinner">
            <span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span>
        </div>
    </div>
    <script>
        require(["jquery"], function($){
            var configData = $configJson;
            var filterData = $filterJson;
            $('table.data-grid tbody tr').click(function(event) {
                if ($(this).find('td.col-period').get().length == 0) return;
                // find the data associated with clicked row
                var index = $(this.parentNode).find('tr').get().indexOf(this);
                //alert('filterData[' + index + '] = ' + filterData[index] + ' ' + filterData.length);
                
                $('#sales-order-report-spinner').show();
                
                configData.filters = filterData[index];
                $.ajax({
                    type: "POST",
                    url: "$saveUrl?isAjax=true", 
                    data: {
                        data : JSON.stringify({current: configData}),
                        namespace: 'sales_order_grid',
                        form_key: FORM_KEY,
                        redirect: {
                            path: 'sales/order/index'
                        }
                    }
                })    
                .always(function(data, textStatus, jqXHR) {
                    var error = typeof jqXHR.always == 'function' ? null : jqXHR;
                    jqXHR = error ? data : jqXHR;
                    if (error == null) {
                        document.location =  '$redirectUrl';
                    }
                    else {
                        alert(jqXHR.status + ' ' + textStatus + ' ' + error);
                        $('#sales-order-report-spinner').hide();
                    }
                });
                
                return false;
            });
        });
    </script>
HTML;
        return $html.$suffix;
    }
    
    /**
     * 
     * @param \Magento\Reports\Model\Item $item
     * @return array
     */
    protected function _generateFilterData($item)
    {
        $filterData = $this->getFilterData();
        $parts = explode('-', $item->getPeriod());
        switch ($filterData->getData('period_type')) {
            case 'year':
                // TODO parse for period
                $createdFrom = '1/1/'.$parts[0];
                $createdTo = '12/31/'.$parts[0];
                break;
            case 'month':
                // TODO parse for period
                $createdFrom = $parts[1].'/1/'.$parts[0];
                $createdTo = $parts[1].'/'.date("t", strtotime($item->getPeriod().'-1')).'/'.$parts[0];
                break;
            case 'day':
            default:
                $createdFrom = $createdTo = "${parts[1]}/${parts[2]}/${parts[0]}";
                break;
        }
        $filterParams = [
            'placeholder' => true,
            'created_at' => [
                'from' => $createdFrom,
                'to' => $createdTo,
            ],
            'user_id' => $filterData->getData('sales_agent'),
        ];
        
        return  ['applied' => $filterParams];
    }
    
}