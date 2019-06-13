<?php
define ('NOT_FOUND', ['code' => 404 , 'text' => '404 Not Found !']);
define ("DATA_FORMAT_ERROR",['code' => 105, 'text' => 'Неверный формат данных']);
define ("CONNECTION_ERROR",['code' => 199, 'text' => 'Не удалось установить соединение с сервером']);
define ("FILE_NOT_FOUND",['code' => 800, 'text' => 'Файл не найден']);
define ("PERMISSION_DENIED",['code' => 801, 'text' => 'Недостаточно прав']);
define ("DELETE_FILE_ERROR",['code' => 802, 'text' => 'Ошибка удаления файла']);
define ("DELETE_FILE_SUCCESS",['code' => 803, 'text' => 'Файл успешно удалён']);
define ("FILE_ALREADY_IN_SANDBOX",['code' => 804, 'text' => 'Этот файл уже загружен во временное хранилище']);
define ("FILE_CREATE_ERROR",['code' => 805, 'text' => 'Ошибка создания файла']);
define ("FILE_CREATE_SUCCESS",['code' => 806, 'text' => 'Файл успешно создан']);
define ("FILE_ALREADY_EXITS",['code' => 807, 'text' => 'Этот файл уже был загружен']);
define ("FILE_DATA_UPDATE_SUCCESS",['code' => 808, 'text' => 'Данные файла успешно обновлены']);
define ("FILE_DATA_UPDATE_ERROR",['code' => 809, 'text' => 'Ошибка обновления данных файла']);
define ("NO_FILES",['code' => 810, 'text' => 'Файлы не найдены']);
define ("EMPTY_SANDBOX",['code' => 811, 'text' => 'Нет файлов во временном хранилище']);
define ("FILE_SERVER_ERROR",['code' => 814, 'text' => 'Ошибка определения файлового сервера']);
define ("ERROR_DB_CONNECTION",['code' => 119, 'text' => 'Не удалось подключиться к базе данных!']);
define ("PERFORM_QUERY_ERROR",['code' => 120, 'text' => 'Не удалось выполнить запрос к базе данных!']);
define ("UNSUPPORTED_DATA_TYPE",['code' => 147, 'text' => 'Неподдерживаемый тип данных']);
define ("ERROR_DB_TRANSACTION",['code' => 124, 'text' => 'Ошибка транзакции БД!']);
define ("DB_CONN_CLOSE_ERROR",['code' => 129, 'text' => 'Ошибка закрытия соединения БД!']);


