#
# Table structure for table 'tx_mrastp_person'
#
CREATE TABLE tx_mrastp_person (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	salutation_ud int(11) DEFAULT '0' NOT NULL,
	firstname tinytext NOT NULL,
	name tinytext NOT NULL,
	street tinytext NOT NULL,
	compl tinytext NOT NULL,
	zip varchar(6) DEFAULT '' NOT NULL,
	city varchar(255) DEFAULT '' NOT NULL,
	canton_id int(11) DEFAULT '0' NOT NULL,
	country_id int(11) DEFAULT '0' NOT NULL,
	phone varchar(20) DEFAULT '' NOT NULL,
	mobile varchar(20) DEFAULT '' NOT NULL,
	fax varchar(20) DEFAULT '' NOT NULL,
	email varchar(255) DEFAULT '' NOT NULL,
	lang int(11) DEFAULT '0' NOT NULL,
	section_id int(11) DEFAULT '0' NOT NULL,
	status int(11) DEFAULT '0' NOT NULL,
	entry_date int(11) DEFAULT '0' NOT NULL,
	workaddress blob NOT NULL,
	groups int(11) DEFAULT '0' NOT NULL,
	feuser_id int(11) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY feuser (feuser_id)
);



#
# Table structure for table 'tx_mrastp_salutation'
#
CREATE TABLE tx_mrastp_salutation (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	label_de varchar(255) DEFAULT '' NOT NULL,
	label_fr varchar(255) DEFAULT '' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_mrastp_canton'
#
CREATE TABLE tx_mrastp_canton (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	abbrevation char(2) DEFAULT '' NOT NULL,
	label_de varchar(255) DEFAULT '' NOT NULL,
	label_fr varchar(255) DEFAULT '' NOT NULL,
	label_en varchar(255) DEFAULT '' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_mrastp_country'
#
CREATE TABLE tx_mrastp_country (
    uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
    pid int(11) unsigned DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    cn_iso_2 char(2) DEFAULT '' NOT NULL,
    cn_iso_3 char(3) DEFAULT '' NOT NULL,
    cn_short_en varchar(50) DEFAULT '' NOT NULL,
    cn_short_de varchar(50) DEFAULT '' NOT NULL,
    cn_short_fr varchar(50) DEFAULT '' NOT NULL,
    PRIMARY KEY (uid),
    UNIQUE uid (uid)
);



#
# Table structure for table 'tx_mrastp_section'
#
CREATE TABLE tx_mrastp_section (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	abbrevation char(2) DEFAULT '' NOT NULL,
	label_de varchar(255) DEFAULT '' NOT NULL,
	label_fr varchar(255) DEFAULT '' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_mrastp_state'
#
CREATE TABLE tx_mrastp_state (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	abbrevation char(10) DEFAULT '' NOT NULL,
	label_de varchar(255) DEFAULT '' NOT NULL,
	label_fr varchar(255) DEFAULT '' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_mrastp_workaddress'
#
CREATE TABLE tx_mrastp_workaddress (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	parentuid int(11) DEFAULT '0' NOT NULL,
    parenttable varchar(255) DEFAULT '' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	employment tinyint(4) DEFAULT '0' NOT NULL,
	name_practice varchar(255) DEFAULT '' NOT NULL,
	name_supplement varchar(255) DEFAULT '' NOT NULL,
	address1 varchar(255) DEFAULT '' NOT NULL,
	address2 varchar(255) DEFAULT '' NOT NULL,
	zip varchar(6) DEFAULT '' NOT NULL,
	city varchar(255) DEFAULT '' NOT NULL,
	country_id int(11) DEFAULT '0' NOT NULL,
	canton_id int(11) DEFAULT '0' NOT NULL,
	phone varchar(20) DEFAULT '' NOT NULL,
    mobile varchar(20) DEFAULT '' NOT NULL,
	fax varchar(20) DEFAULT '' NOT NULL,
	email varchar(255) DEFAULT '' NOT NULL,
	audience varchar(255) DEFAULT '' NOT NULL,
	services varchar(255) DEFAULT '' NOT NULL,
	languages varchar(255) DEFAULT '' NOT NULL,
	website varchar(255) DEFAULT '' NOT NULL,
	startofwork int(11) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_mrastp_group'
#
CREATE TABLE tx_mrastp_group (
        uid int(11) NOT NULL auto_increment,
        pid int(11) DEFAULT '0' NOT NULL,
        tstamp int(11) DEFAULT '0' NOT NULL,
        crdate int(11) DEFAULT '0' NOT NULL,
        cruser_id int(11) DEFAULT '0' NOT NULL,
        sorting int(10) DEFAULT '0' NOT NULL,
        deleted tinyint(4) DEFAULT '0' NOT NULL,
        hidden tinyint(4) DEFAULT '0' NOT NULL,
        starttime int(11) DEFAULT '0' NOT NULL,
        endtime int(11) DEFAULT '0' NOT NULL,
        label_de varchar(255) DEFAULT '' NOT NULL,
	label_fr varchar(255) DEFAULT '' NOT NULL,
	cat_id int(11) DEFAULT '0' NOT NULL,
        persons int(11) DEFAULT '0' NOT NULL,

        PRIMARY KEY (uid),
        KEY parent (pid)
);



#
# Table structure for table 'tx_mrastp_group'
#
CREATE TABLE tx_mrastp_group_cat (
        uid int(11) NOT NULL auto_increment,
        pid int(11) DEFAULT '0' NOT NULL,
        tstamp int(11) DEFAULT '0' NOT NULL,
        crdate int(11) DEFAULT '0' NOT NULL,
        cruser_id int(11) DEFAULT '0' NOT NULL,
        sorting int(10) DEFAULT '0' NOT NULL,
        deleted tinyint(4) DEFAULT '0' NOT NULL,
        hidden tinyint(4) DEFAULT '0' NOT NULL,
        starttime int(11) DEFAULT '0' NOT NULL,
        endtime int(11) DEFAULT '0' NOT NULL,
        label_de varchar(255) DEFAULT '' NOT NULL,
	label_fr varchar(255) DEFAULT '' NOT NULL,

        PRIMARY KEY (uid),
        KEY parent (pid)
);



#
# Table structure for table 'tx_mrastp_persons_groups_rel'
#
CREATE TABLE tx_mrastp_persons_groups_rel (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    personid int(11) DEFAULT '0' NOT NULL,
    groupid int(11) DEFAULT '0' NOT NULL,
    personsort int(10) DEFAULT '0' NOT NULL,
    groupsort int(10) DEFAULT '0' NOT NULL,
    funktion_de varchar(255) DEFAULT '' NOT NULL,
    funktion_fr varchar(255) DEFAULT '' NOT NULL,
    canton_id int(11) DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);
