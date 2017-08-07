CREATE PROCEDURE getFile(IN FileLink VARCHAR(32))
  BEGIN
     SELECT * FROM files_links WHERE link=FileLink;
END;
