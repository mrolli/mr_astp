<?php

require_once 'Zend/Db/Table/Abstract.php';

class Mrastp_Db_Table_Person extends Zend_Db_Table_Abstract 
{
    protected $_name = 'tx_mrastp_person';
    protected $_primary = 'uid';
    protected $_dependentTables = array('Mrastp_Db_Table_Workaddress');
    
    protected $_referenceMap = array(
        'Salutation' => array(
            'columns'       => 'salutation_id',
            'refTableClass' => 'Mrastp_Db_Table_Salutation',
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
        'Language' => array(
            'columns'       => 'language_id',
            'refTableClass' => 'Mrastp_Db_Table_Language',
            'refColumns'    => 'uid',
        ),
        'Section' => array(
            'columns'       => 'section_id',
            'refTableClass' => 'Mrastp_Db_Table_Section',
            'refColumns'    => 'uid',
        ),
        'Status' => array(
            'columns'       => 'status',
            'refTableClass' => 'Mrastp_Db_Table_Status',
            'refColumns'    => 'uid',
        ),
    );
}