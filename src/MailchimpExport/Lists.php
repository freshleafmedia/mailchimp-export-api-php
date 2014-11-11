<?php

class MailchimpExport_Lists implements MailchimpExport_DataCallbackInterface {

    const STATUS_SUBSCRIBED = 'subscribed';
    const STATUS_UNSUBSCRIBED = 'unsubscribed';
    const STATUS_CLEANED = 'cleaned';

    private $callable;


    /**
     * @param MailchimpExport $master
     */
    public function __construct(MailchimpExport $master) {
        $this->master = $master;
    }

    /**
     * Exports/dumps members of a list and all of their associated details. This is a very similar to exporting via the web interface.
     *
     * @param callable $callable callable will be called for each received dataset
     * @param string $id the list id to get members from
     * @param string $status the status to get members for - one of (subscribed, unsubscribed, cleaned), defaults to subscribed
     * @param array $segment pull only a certain Segment of your list.
     * @param null $since only return member whose data has changed since a GMT timestamp – in YYYY-MM-DD HH:mm:ss format
     * @param null $hashed if, instead of full list data, you'd prefer a hashed list of email addresses, set this to the hashing algorithm you expect. Currently only "sha256" is supported.
     * @throws MailchimpExport_NoCallableError
     */
    public function export(callable $callable, $id, $status = self::STATUS_SUBSCRIBED, array $segment = array(), $since = null, $hashed = null) {

        $_params = array("id" => $id, "status" => $status, "segment" => $segment, "since" => $since, 'hashed' => $hashed);
        $this->callable = $callable;
        $this->master->call('list/', $_params, $this);
        $this->callable = null;
    }

    /**
     * @inheritdoc
     */
    public function hasHeadingRow()
    {
        return true;
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


