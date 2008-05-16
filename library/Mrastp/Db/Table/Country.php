<?php

require_once 'Zend/Db/Table/Abstract.php';

class Mrastp_Db_Table_Country extends Zend_Db_Table_Abstract 
{
    protected $_name = 'tx_mrastp_country';
    protected $_primary = 'uid';
    protected $_dependentTables = array('tx_mrastp_person', 'tx_mrastp_workaddress');
}