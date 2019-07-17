<?php
$router->get("/file/{hash}", "FileController", "getFile");
$router->delete("/file/{id}", "FileController", "deleteFile");
$router->post("/file/{hash}", "FileController", "uploadFile");
$router->post("/meta", "MetaController", "saveLink");
$router->get("/status", "StatusController", "getStatus");
