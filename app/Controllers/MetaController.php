<?php
namespace App\Controllers;

use App\Link;
use Transaction\Transaction;
use \Validation\Validator;

/**
 * @description Actions with files meta (create link ...)
 */
class MetaController {
   use \Router\Routable;

   /**
    * @summary Associate a file on file server with hash from controller.
    * @description Request file server from controller server to associate given hash with file id creating a temporary/persistent upload/read file link
    */
   public function saveLink () {
       //validate data
       $validator = new Validator($this->request->getArgs(),"SaveLink");
       $result = $validator->get();
       if ($errors = $validator->errors()){
           app()->setStatusCode(500);
           return $errors;
       }
       try {
           $link = new Link($result, app()->links, app()->mysql);
           $transaction = new Transaction($link);
           $transaction->notExist()->swooleSave()->dbSave();
           $transaction->commit();
       } catch (\Exception $e) {
           app()->setStatusCode($e->getCode());
           return $e->getMessage();
       }
       app()->setStatusCode(200);
       return true;
   }
}