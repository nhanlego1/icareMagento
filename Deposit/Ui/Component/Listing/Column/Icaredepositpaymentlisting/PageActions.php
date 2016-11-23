<?php
namespace Icare\Deposit\Ui\Component\Listing\Column\Icaredepositpaymentlisting;

class PageActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as & $item) {
                $name = $this->getData("name");
                $id = "X";
                if(isset($item["payment_id"]))
                {
                    $id = $item["payment_id"];
                }

                $item[$name]["view"] = [
                    "href"=>$this->getContext()->getUrl(
                        "icare/deposit/payment/view",["id"=>$id]),
                    "label"=>__("Detail"),
                    "options" => "payment-detail-btn"
                ];
            }
        }

        return $dataSource;
    }

}
