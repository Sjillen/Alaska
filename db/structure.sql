drop table if exists t_billet;

create table t_billet (
billet_id integer not null primary key auto_increment,
billet_title varchar(100) not null,
billet_content varchar(2000) not null
) engine=innodb character set utf8 collate utf8_unicode_ci;