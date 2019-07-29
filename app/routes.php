<?php
$router->get("/file/{hash}", "FileController", "getFile")->before('routes.api');
$router->delete("/file/{id}", "FileController", "deleteFile")->before('routes.api');
$router->post("/file/{hash}", "FileController", "uploadFile")->before('routes.api');
$router->post("/meta", "MetaController", "saveLink")->before('routes.api');
$router->get("/status", "StatusController", "getStatus")->before('routes.api');
