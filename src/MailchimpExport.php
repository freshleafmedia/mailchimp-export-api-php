<?php

require_once 'MailchimpExportDataCallbackInterface.php';
require_once 'MailchimpExport/Ecomm.php';
require_once 'MailchimpExport/Lists.php';
require_once 'MailchimpExport/CampaignSubscriberActivity.php';
require_once 'MailchimpExport/Exceptions.php';

class MailchimpExport {
    
    public $apikey;
    public $ch;
    public $root  = 'https://api.mailchimp.com/export/1.0';
    public $debug = false;

    /** @var \MailchimpExport_Ecomm  */
    public $ecomm;
    /** @var \MailchimpExport_Lists  */
    public $lists;
    /** @var \MailchimpExport_CampaignSubscriberActivity  */
    public $campainSubscriberActivity;

    public function __construct($apikey=null, $opts=array()) {
        if (!$apikey) {
            $apikey = getenv('MAILCHIMP_APIKEY');
        }

        if (!$apikey) {
            $apikey = $this->readConfigs();
        }

        if (!$apikey) {
            throw new Mailchimp_Error('You must provide a MailChimp API key');
        }

        $this->apikey = $apikey;
        $dc           = "us1";

        if (strstr($this->apikey, "-")){
            list($key, $dc) = explode("-", $this->apikey, 2);
            if (!$dc) {
                $dc = "us1";
            }
        }

        $this->root = str_replace('https://api', 'https://' . $dc . '.api', $this->root);
        $this->root = rtrim($this->root, '/') . '/';

        if (!isset($opts['timeout']) || !is_int($opts['timeout'])){
            $opts['timeout'] = 600;
        }
        if (isset($opts['debug'])){
            $this->debug = true;
        }


        $this->ch = curl_init();

        if (isset($opts['CURLOPT_FOLLOWLOCATION']) && $opts['CURLOPT_FOLLOWLOCATION'] === true) {
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);    
        }

        curl_setopt($this->ch, CURLOPT_USERAGENT, 'MailChimp-PHP/2.0.6');
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $opts['timeout']);

        $this->ecomm = new MailchimpExport_Ecomm($this);
        $this->lists = new MailchimpExport_Lists($this);
        $this->campainSubscriberActivity = new MailchimpExport_CampaignSubscriberActivity($this);
    }

    public function __destruct() {
        if(is_resource($this->ch)) {
            curl_close($this->ch);
        }
    }

    public function call($url, $params, MailchimpExport_DataCallbackInterface $dataCallable) {

        $params['apikey'] = $this->apikey;
        
        $ch     = $this->ch;

        curl_setopt($ch, CURLOPT_URL, $this->root . $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_VERBOSE, $this->debug);

        $headings = null;
        $hasHeadingRow = $dataCallable->hasHeadingRow();

        curl_setopt($this->ch, CURLOPT_WRITEFUNCTION, function($ch, $data) use ($headings, $hasHeadingRow, $dataCallable) {
                $dataLength = strlen($data);
                if ($dataLength == 0) {
                    return $dataLength;
                }
                $result = explode("\n", $data);
                $data = null;

                do {
                    $currentRow = array_shift($result);

                    if (empty($currentRow)) {
                        continue;
                    }


                    $currentDataSet = json_decode($currentRow, true);
                    $currentRow = null;

                    // check for errors
                    if (isset($currentDataSet['error']) && isset($currentDataSet['code'])) {
                        throw new \MailchimpExport_Exception($currentDataSet['error'], $currentDataSet['code']);
                    }

                    if ($headings === null && $hasHeadingRow) {
                        $headings = $currentDataSet;
                        continue;
                    }

                    // if we have headings => combine them with the dataset
                    if ($headings !== null) {
                        $returnValue = array_combine($headings, $currentDataSet);
                    }
                    else {
                        $returnValue = $currentDataSet;
                    }

                    $dataCallable->addDataSet($returnValue);
                    $returnValue = null;
                    $currentDataSet = null;
                }
                while (!empty($result));
                $result = null;
                return $dataLength;
            });


        $start = microtime(true);
        $this->log('Call to ' . $this->root . $url . ': ' . json_encode($params));
        $curl_buffer = null;
        if($this->debug) {
            $curl_buffer = fopen('php://memory', 'w+');
            curl_setopt($ch, CURLOPT_STDERR, $curl_buffer);
        }

        curl_exec($ch);

        if($this->debug) {
            rewind($curl_buffer);
            $this->log(stream_get_contents($curl_buffer));
            fclose($curl_buffer);
        }

        $time = microtime(true) - $start;
        $this->log('Completed in ' . number_format($time * 1000, 2) . 'ms');

        if(curl_error($ch)) {
            throw new Mailchimp_HttpError("API call to $url failed: " . curl_error($ch));
        }
    }

    public function readConfigs() {
        $paths = array('~/.mailchimp.key', '/etc/mailchimp.key');
        foreach($paths as $path) {
            if(file_exists($path)) {
                $apikey = trim(file_get_contents($path));
                if ($apikey) {
                    return $apikey;
                }
            }
        }
        return false;
    }

    public function castError($result) {
        if ($result['status'] !== 'error' || !$result['name']) {
            throw new MailchimpExport_Error('We received an unexpected error: ' . json_encode($result));
        }

        $class = (isset(self::$error_map[$result['name']])) ? self::$error_map[$result['name']] : 'MailchimpExport_Error';
        return new $class($result['error'], $result['code']);
    }

    public function log($msg) {
        if ($this->debug) {
            error_log($msg);
        }
    }
}


