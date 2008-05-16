<?php

require_once 'Zend/Db/Table/Abstract.php';

class Mrastp_Db_Table_Workaddress extends Zend_Db_Table_Abstract 
{
    protected $_name = 'tx_mrastp_workaddress';
    protected $_primary = 'uid';
    
    protected $_referenceMap = array(
        'Person' => array(
            'columns'       => 'parentuid',
            'refTableClass' => 'Mrastp_Db_Table_Person',
            'refColumns'    => 'uid',
        ),
        'Canton' => array(
            'columns'       => 'canton_id',
            'refTableClass' => 'Mrastp_Db_Table_Canton',
            'refColumns'    => 'uid',
        ),
        'Country' => array(
            'columns'       => 'country_id',
            'refTableClass' => 'Mrastp_Db_Table_Country',
            'refColumns'    => 'uid',
        ),
    );

}