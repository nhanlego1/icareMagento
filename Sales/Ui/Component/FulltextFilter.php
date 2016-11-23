<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 03/11/2016
 * Time: 10:24
 */

namespace Icare\Sales\Ui\Component;


use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection\AbstractDb as DbCollection;
class FulltextFilter extends \Magento\Framework\View\Element\UiComponent\DataProvider\FulltextFilter{
    /**
     * Apply fulltext filters
     *
     * @param DbCollection $collection
     * @param Filter $filter
     * @return void
     */
    public function apply(DbCollection $collection, Filter $filter)
    {
        $columns = $this->getFulltextIndexColumns($collection, $collection->getMainTable());
        if (!$columns) {
            return;
        }
        if(method_exists($collection,'getMappedField')){
            foreach ($columns as &$column){
                $column = $collection->getMappedField($column);
            }
        }

        $collection->getSelect()
            ->where(
                'MATCH(' . implode(',', $columns) . ') AGAINST(?)',
                $filter->getValue()
            );
    }
}