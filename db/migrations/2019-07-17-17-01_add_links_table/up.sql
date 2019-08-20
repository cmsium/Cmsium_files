create table links
(
    hash   varchar(255)                      not null,
    file   varchar(32)                       not null,
    temp   tinyint default 0                 not null,
    expire datetime                          null,
    type   enum ('read', 'upload', 'delete') not null,
    constraint links_pk
        primary key (hash)
);
