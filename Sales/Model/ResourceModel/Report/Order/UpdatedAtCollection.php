<?php
namespace Icare\Sales\Model\ResourceModel\Report\Order;

/**
 * 
 * @author Nam Pham
 *
 */
class UpdatedAtCollection extends CreatedAtCollection
{
    /**
     * Aggregated Data Table
     *
     * @var string
     */
    protected $_aggregationTable = 'icare_sales_order_aggregated_updated';
}