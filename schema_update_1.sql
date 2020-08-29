alter table mbank_transactions
	add config_code varchar(50) default 'default' null after id;

create index mbank_transactions_config_code_index
	on mbank_transactions (config_code);

alter table mbank_transactions drop primary key;

alter table mbank_transactions
	add primary key (id, config_code);

