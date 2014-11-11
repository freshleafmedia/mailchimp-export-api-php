Overview
=============================================
A PHP 5.3+ API client for [v1 of the MailChimp Export-API](http://apidocs.mailchimp.com/export/1.0/).
Please note that we generate this client/wrapper, so while we're happy to look at any pull requests, ultimately we can't technically accept them.
We will, however comment on any additions or changes made due to them before closing them.


###Usage
This can be installed via [Composer](http://getcomposer.org/) and our [packagist package](https://packagist.org/packages/mailchimp/mailchimp).

---

A basic example for the usage of this class:


    $mailChimpExportClient = new \MailchimpExport('[your-API-key]');
        $mailChimpExportClient->lists->export(function($dataSet) {
                // you can process the received data here
                foreach ($dataset as $key => $value) {
                   echo $key . ' => ' . $value;
                }
            },
            '[list-id]');


