Overview
=============================================
A PHP 5.3+ API client for [v1 of the MailChimp Export-API](http://apidocs.mailchimp.com/export/1.0/).

###Usage
This can be installed via [Composer](http://getcomposer.org/) if setting this repo as a custom repository.

---

A basic example for the usage of this class:


    $mailChimpExportClient = new \MailchimpExport('[your-API-key]');
    $mailChimpExportClient->lists->export(function($dataSet) {
            // you can process the received data here
            foreach ($dataset as $key => $value) {
               echo $key . ' => ' . $value;
            }
        },
        '[list-id]'
        );


