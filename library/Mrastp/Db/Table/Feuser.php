<?php

require_once 'Zend/Db/Table/Abstract.php';

class Mrastp_Db_Table_Feuser extends Zend_Db_Table_Abstract 
{
    protected $_name = 'fe_users';
    protected $_primary = 'uid';
}