create table files_links
(
	file_path varchar(255) not null
		primary key,
	link varchar(32) not null,
	expired_time datetime not null,
	constraint files_links_file_path_uindex
		unique (file_path),
	constraint files_links_link_uindex
		unique (link)
);

CREATE PROCEDURE getFile(IN FileLink VARCHAR(32))
  BEGIN
     SELECT * FROM files_links WHERE link=FileLink;
END;

CREATE PROCEDURE getFile(IN FileLink VARCHAR(32))
  BEGIN
     SELECT * FROM files_links WHERE link=FileLink;
END;

CREATE PROCEDURE saveFileLink(IN FilePath VARCHAR(255), IN FileLink VARCHAR(32), IN ExpTime DATETIME)
  BEGIN
     INSERT INTO files_links (file_path, link,expired_time) VALUES (FilePath,FileLink,ExpTime);
END;

