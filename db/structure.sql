drop table if exists t_billet;

create table t_billet (
	billet_id integer not null primary key auto_increment,
	billet_title varchar(100) not null,
	billet_content varchar(2000) not null
) engine=innodb character set utf8 collate utf8_unicode_ci;

create table t_user (
	usr_id integer not null primary key auto_increment,
	usr_name varchar(50) not null,
	usr_password varchar(88) not null,
	usr_salt varchar(23) not null,
	usr_role varchar(50) not null
) engine=innodb character set utf8 collate utf8_unicode_ci;

create table t_comment (
	com_id integer not null primary key auto_increment,
	com_content varchar(500) not null,
	billet_id integer not null,
	constraint fk_com_billet foreign key(billet_id) references t_billet(billet_id),
	usr_id integer not null,
	constraint fk_com_usr foreign key(usr_id) references t_user(usr_id),
	parent_id integer	
) engine=innodb character set utf8 collate utf8_unicode_ci;