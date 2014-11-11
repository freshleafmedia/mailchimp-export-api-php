<?php

class MailchimpExport_CampaignSubscriberActivity implements MailchimpExport_DataCallbackInterface{

    private $callable;

    /**
     * @param MailchimpExport $master
     */
    public function __construct(MailchimpExport $master) {
        $this->master = $master;
    }

    /**
     * Exports/dumps all Subscriber Activity for the requested campaign.
     *
     * @param callable $callable callable will be called for each received dataset
     * @param $id
     * @param bool|null $includeEmpty
     * @param null $since only return orders with order dates since a GMT timestamp – in YYYY-MM-DD HH:mm:ss format
     * @return string
     */
    public function export(callable $callable, $id, $includeEmpty = false, $since = null) {
        $_params = array("id" => $id, "include_empty" => $includeEmpty, "since" => $since);
        $this->callable = $callable;
        $this->master->call('campaignSubscriberActivity/', $_params, $this);
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


