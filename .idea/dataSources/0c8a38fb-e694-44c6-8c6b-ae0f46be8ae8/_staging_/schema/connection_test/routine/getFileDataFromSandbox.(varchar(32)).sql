CREATE PROCEDURE getFileData(IN idFile VARCHAR(32))
  BEGIN
    SELECT * FROM controller_files WHERE file_id = idFile;
  END;
