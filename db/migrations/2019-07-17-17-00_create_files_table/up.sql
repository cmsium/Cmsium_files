create table files
(
    file_id   varchar(32)       not null,
    path      varchar(255)      null,
    is_delete tinyint default 0 null,
    name      varchar(255)      not null,
    constraint files_file_id_uindex
        unique (file_id)
);

alter table files
    add primary key (file_id);