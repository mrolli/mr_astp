<?php

require_once 'Zend/Db/Table/Abstract.php';

class Mrastp_Db_Table_Hashes extends Zend_Db_Table_Abstract 
{
    protected $_name = 'tx_mrastp_hashes';
    protected $_primary = 'hash';
}