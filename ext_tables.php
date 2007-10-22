<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$TCA["tx_mrastp_person"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person',		
                'label'     => 'name',
                'label_alt' => 'firstname',
                'label_alt_force' => 1,
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'name',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mrastp_person.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, salutation, firstname, name, street, compl, zip, city, canton_id, country_id, phone, mobile, fax, email, lang, section_id, status, entry_date, workaddress",
	)
);

$TCA["tx_mrastp_canton"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_canton',		
		'label'     => 'abbrevation',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'abbrevation',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mrastp_canton.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, abbrevation, label_de, label_fr, label_en",
	)
);

$TCA["tx_mrastp_section"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_section',		
		'label'     => 'abbrevation',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'abbrevation',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mrastp_section.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, abbrevation, label_de, label_fr, label_en",
	)
);

$TCA["tx_mrastp_state"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_state',		
		'label'     => 'abbrevation',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mrastp_state.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, abbrevation, label_de, label_fr, label_en",
	)
);

$TCA["tx_mrastp_workaddress"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_workaddress',		
		'label'     => 'name_practice',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'city',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mrastp_workaddress.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, employment, name_practice, name_supplement, address1, address2, zip, city, country_id, canton_id, phone, fax, email, audience, services, languages, website, startofwork",
	)
);

$TCA["tx_mrastp_group_cat"] = array (
        "ctrl" => array (
                'title'     => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_group_cat',
                'label'     => 'label_de',
                'tstamp'    => 'tstamp',
                'crdate'    => 'crdate',
                'cruser_id' => 'cruser_id',
                'default_sortby' => 'label_de',
                'delete' => 'deleted',
                'enablecolumns' => array (
                        'disabled' => 'hidden',
                        'starttime' => 'starttime',
                        'endtime' => 'endtime',
                ),
                'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
                'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mrastp_group_cat.gif',
        ),
        "feInterface" => array (
                "fe_admin_fieldList" => "hidden, starttime, endtime, label_de, label_fr, label_en",
        )
);

$TCA["tx_mrastp_group"] = array (
        "ctrl" => array (
                'title'     => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_group',
                'label'     => 'label_de',
                'tstamp'    => 'tstamp',
                'crdate'    => 'crdate',
                'cruser_id' => 'cruser_id',
                'default_sortby' => 'label_de',
                'delete' => 'deleted',
                'enablecolumns' => array (
                        'disabled' => 'hidden',
                        'starttime' => 'starttime',
                        'endtime' => 'endtime',
                ),
                'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
                'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mrastp_group.gif',
        ),
        "feInterface" => array (
                "fe_admin_fieldList" => "hidden, starttime, endtime, label_de, label_fr, label_en, cat_id",
        )
);

$TCA["tx_mrastp_persons_groups_rel"] = array(
        "ctrl" => array (
                'title'     => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_persons_groups_rel',
                'label'     => 'groupid',
		'label_alt' => 'personid',
                'label_alt_force' => 1,
                'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
                'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mrastp_group.gif',
        ),
);

?>
