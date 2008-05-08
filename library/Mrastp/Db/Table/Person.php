<?php

require_once 'Zend/Db/Table/Abstract.php';

class Mrastp_Db_Table_Person extends Zend_Db_Table_Abstract 
{
    protected $_name = 'tx_mrastp_person';
    protected $_primary = 'uid';
}