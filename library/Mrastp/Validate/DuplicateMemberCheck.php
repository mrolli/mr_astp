<?php

require_once 'Zend/Validate/Abstract.php';
require_once 'Mrastp/Db/Table/Person.php';

class Mrastp_Validate_DuplicateMemberCheck extends Zend_Validate_Abstract
{
    const MEMBER_EXISTS = 'memberExists';

    protected $_messageTemplates = array(
        self::MEMBER_EXISTS => 'A member with the given firstname, name and city or email address already exists.'
    );

    public function isValid($value, $context = null)
    {
        $value = (string) $value;
        $this->_setValue($value);

        $personTable = new Mrastp_Db_Table_Person();
        if (!empty($context['firstname']) && !empty($context['city'])) {
            $persons = $personTable->fetchAll(array('firstname = ?' => $context['firstname'], 'name = ?' => $value, 'city = ?' => $context['city']));
            if (count($persons) == 0) {
                return true;
            }
            if (isset($context['uid']) && $persons->current()->uid == $context['uid']) {
                return true;
            }
            $this->_error(self::MEMBER_EXISTS);
        }
        return false;
    }
}
