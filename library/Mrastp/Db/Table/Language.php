<?php

require_once 'Zend/Db/Table/Abstract.php';

class Mrastp_Db_Table_Language extends Zend_Db_Table_Abstract 
{
    protected $_name = 'tx_mrastp_language';
    protected $_primary = 'uid';
    protected $_dependentTables = array('tx_mrastp_person');
}