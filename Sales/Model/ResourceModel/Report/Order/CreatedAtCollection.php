<?php
namespace Icare\Sales\Model\ResourceModel\Report\Order;

/**
 * 
 * @author Nam Pham
 *
 */
class CreatedAtCollection extends \Magento\Sales\Model\ResourceModel\Report\Order\Collection
{
 
    /**
     * Aggregated Data Table
     *
     * @var string
     */
    protected $_aggregationTable = 'icare_sales_order_aggregated_created';
    
    protected $_userId;
    
    /**
     * 
     * @param integer $userId
     */
    public function setUserId($userId) 
    {
        $this->_userId = $userId;
    }
    
    /**
     * Order status filter is custom for this collection
     *
     * @return $this
     */
    protected function _applyCustomFilter()
    {
        parent::_applyCustomFilter();
        
        if (!empty($this->_userId)) {
            $this->getSelect()->where('user_id = ?', $this->_userId);
        }
    }
}