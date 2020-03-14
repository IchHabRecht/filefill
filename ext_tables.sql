#
# Table structure for table 'sys_file'
#
CREATE TABLE sys_file (
    tx_filefill_identifier varchar(255) DEFAULT '' NOT NULL
);

#
# Table structure for table 'sys_file_storage'
#
CREATE TABLE sys_file_storage (
    tx_filefill_enable tinyint(4) DEFAULT '0' NOT NULL,
    tx_filefill_resources text
);
