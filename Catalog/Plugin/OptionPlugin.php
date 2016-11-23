<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 7/20/2016
 * Time: 9:49 AM
 */

namespace Icare\Catalog\Plugin;

use Icare\Mifos\Helper\Mifos;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Icare\Custom\Helper\Custom;

class OptionPlugin
{

    public function __construct(
        AdminSession $adminSession,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Model\Order\Status\HistoryFactory $historyFactory
    )
    {
        $this->_adminSession = $adminSession;
        $this->_historyFactory = $historyFactory;
        $this->request = $request;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    public function afterSave(\Magento\Catalog\Api\Data\ProductCustomOptionInterface $option)
    {
//        $request = $this->request->getParams();
//        var_dump($request);
//        die;
    }


}