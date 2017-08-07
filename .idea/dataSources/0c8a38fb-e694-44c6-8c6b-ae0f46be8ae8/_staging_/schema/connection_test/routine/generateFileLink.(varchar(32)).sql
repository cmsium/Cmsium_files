CREATE PROCEDURE saveFileLink(IN FilePath VARCHAR(255),IN FileLink VARCHAR(32),IN ExpTime DATETIME)
  BEGIN
     INSERT INTO files_links (file_path, link,expired_time) VALUES (FilePath,FileLink,ExpTime);
END;
