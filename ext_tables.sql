#
# Table structure for table 'tx_marketingautomation_persona'
#
CREATE TABLE tx_marketingautomation_persona (
    tx_marketingautomation_segments int(11) DEFAULT '0' NOT NULL
);

#
# Table structure for table 'tx_marketingautomation_segment'
#
CREATE TABLE tx_marketingautomation_segment (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,

    title varchar(255) DEFAULT '' NOT NULL,
    items int(11) DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid)
);

#
# Table structure for table 'tx_marketingautomation_segment_mm'
#
CREATE TABLE tx_marketingautomation_segment_mm (
    uid_local int(11) DEFAULT '0' NOT NULL,
    uid_foreign int(11) DEFAULT '0' NOT NULL,
    tablenames varchar(255) DEFAULT '' NOT NULL,
    fieldname varchar(255) DEFAULT '' NOT NULL,
    sorting int(11) DEFAULT '0' NOT NULL,
    sorting_foreign int(11) DEFAULT '0' NOT NULL,

    KEY uid_local_foreign (uid_local,uid_foreign),
    KEY uid_foreign_tablefield (uid_foreign,tablenames(40),fieldname(3),sorting_foreign)
);

CREATE TABLE tt_content (
    mautic_form_id int(11) unsigned DEFAULT '0',
);

CREATE TABLE pages (
    tx_mautic_tags int(11) DEFAULT '0' NOT NULL
);

CREATE TABLE tx_mautic_page_tag_mm (
    uid_local int(11) DEFAULT '0' NOT NULL,
    uid_foreign int(11) DEFAULT '0' NOT NULL,
    sorting int(11) DEFAULT '0' NOT NULL,
    sorting_foreign int(11) DEFAULT '0' NOT NULL,

    KEY uid_local_foreign (uid_local,uid_foreign)
);

CREATE TABLE tx_mautic_domain_model_tag (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,

    title varchar(255) DEFAULT '' NOT NULL,
    items int(11) DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid)
);