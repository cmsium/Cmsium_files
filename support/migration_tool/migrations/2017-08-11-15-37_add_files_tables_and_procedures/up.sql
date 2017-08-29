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

create table connects
(
	ip varchar(32) not null,
	file_path varchar(255) not null,
	primary key (ip, file_path)
);



CREATE PROCEDURE getFile(IN FileLink VARCHAR(32))
  BEGIN
     SELECT * FROM files_links WHERE link=FileLink;
END;

CREATE PROCEDURE getFileLink(IN FilePath VARCHAR(255))
  BEGIN
     SELECT * FROM files_links WHERE file_path=FilePath;
END;

CREATE PROCEDURE saveFileLink(IN FilePath VARCHAR(255), IN FileLink VARCHAR(32), IN ExpTime DATETIME)
  BEGIN
     INSERT INTO files_links (file_path, link,expired_time) VALUES (FilePath,FileLink,ExpTime);
END;

CREATE EVENT event_name ON SCHEDULE EVERY 1 HOUR DO DELETE from files_links where expired_time < NOW();

CREATE PROCEDURE addConnect(IN UserIp VARCHAR(32), IN FilePath VARCHAR(255))
BEGIN
    INSERT INTO connects (ip,file_path) VALUES (UserIp,FilePath);
END;

CREATE PROCEDURE checkConnects(IN UserIp VARCHAR(32))
BEGIN
   SELECT count(*) FROM connects where ip=UserIP;
END;

CREATE PROCEDURE deleteConnect(IN UserIp VARCHAR(32), IN FilePath VARCHAR(255))
BEGIN
    DELETE FROM connects WHERE ip=UserIP and file_path=FilePath;
END;

CREATE PROCEDURE countConnects()
BEGIN
   SELECT count(*) FROM connects;
END;