CREATE PROCEDURE getFileLink(IN FilePath VARCHAR(255))
  BEGIN
     SELECT * FROM files_links WHERE file_path=FilePath;
END;
