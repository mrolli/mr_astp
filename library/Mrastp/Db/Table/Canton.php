<?php

require_once 'Zend/Db/Table/Abstract.php';

class Mrastp_Db_Table_Canton extends Zend_Db_Table_Abstract 
{
    protected $_name = 'tx_mrastp_canton';
    protected $_primary = 'uid';
    protected $_dependentTables = array('tx_mrastp_person', 'tx_mrastp_workaddress');
}