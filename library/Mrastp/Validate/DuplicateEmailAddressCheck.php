<?php

require_once 'Zend/Validate/Abstract.php';
require_once 'Mrastp/Db/Table/Person.php';

class Mrastp_Validate_DuplicateEmailAddressCheck extends Zend_Validate_Abstract
{
    const EMAIL_EXISTS = 'emailExists';

    protected $_messageTemplates = array(
        self::EMAIL_EXISTS => 'A member with the given email address already exists.'
    );

    public function isValid($value, $context = null)
    {
        $value = (string) $value;
        $this->_setValue($value);

        $personTable = new Mrastp_Db_Table_Person();
        $persons = $personTable->fetchAll(array('email = ?' => $value));
        if (count($persons) == 0) {
            return true;
        }
        $this->_error(self::EMAIL_EXISTS);
        return false;
    }
}