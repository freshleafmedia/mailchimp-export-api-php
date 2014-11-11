<?php

class MailchimpExport_Ecomm implements MailchimpExport_DataCallbackInterface {

    private $callable;

    /**
     * @param MailchimpExport $master
     */
    public function __construct(MailchimpExport $master) {
        $this->master = $master;
    }

    /**
     * Exports/dumps all Ecommerce Orders for an account.
     * @param callable $callable callable will be called for each received dataset
     * @param null $since only return orders with order dates since a GMT timestamp – in YYYY-MM-DD HH:mm:ss format
     * @return string
     */
    public function export(callable $callable, $since = null) {
        $_params = array("since" => $since);
        $this->callable = $callable;
        $this->master->call('ecommOrders/', $_params, $this);
        $this->callable = null;
    }

    /**
     * @inheritdoc
     */
    public function hasHeadingRow()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function addDataSet(array $dataset)
    {
        $tmp = $this->callable;
        $tmp($dataset);
    }
}


