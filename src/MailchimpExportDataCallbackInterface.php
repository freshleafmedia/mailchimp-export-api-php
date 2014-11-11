<?php
/**
 *
 * @author: sschulze@silversurfer7.de
 */

interface MailchimpExport_DataCallbackInterface {

    /**
     * return the information if the first row is a header row
     * @return bool
     */
    public function hasHeadingRow();

    /**
     * this function will be called with each dataset
     * @param array $dataset
     */
    public function addDataSet(array $dataset);
} 