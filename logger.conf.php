<?php

/**
 * Prios:
 * - EMERG
 * - ALERT
 * - CRIT
 * - ERR
 * - WARN
 * - NOTICE
 * - INFO
 * - DEBUG
 */
$logfile = '/srv/www/www.astp.ch/log/mr_astp.log';
$minPrioFile = Zend_Log::DEBUG;
$minPrioMail = Zend_Log::CRIT;
$mailto = 'michael@rollis.ch';
$mailFrom = array('michael@rollis.ch', 'astp Website');
$mailSubject = 'mr_astp Alert: Critical error detected!';


/* not edits beyond this line */
Zend_Loader::loadClass('Zend_Log_Filter_Priority');

Zend_Loader::loadClass('Zend_Log_Writer_Stream');
$fileWriter = new Zend_Log_Writer_Stream($logfile);
$fileWriter->addFilter(new Zend_Log_Filter_Priority($minPrioFile));
$this->_logger->addWriter($fileWriter);

Zend_Loader::loadClass('Zend_Mail');
$mail = new Zend_Mail('utf-8');
$mail->addTo($mailto)
     ->setFrom($mailFrom[0], $mailFrom[1])
     ->setSubject($mailSubject);
     
Zend_Loader::loadClass('Mrastp_Log_Writer_Mail');
$mailWriter = new Mrastp_Log_Writer_Mail($mail);
$mailWriter->addFilter(new Zend_Log_Filter_Priority($minPrioMail));
$this->_logger->addWriter($mailWriter);
