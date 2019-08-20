<?php
namespace App\Controllers;

use App\Exceptions\ValidationException;
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
       try {
           //validate data
           $validator = new Validator($this->request->getArgs(),"SaveLink");
           $result = $validator->get();
           if ($errors = $validator->errors()){
                 throw new ValidationException($errors);
           }
           $link = new Link($result, app()->links);
           $transaction = new Transaction($link);
           $transaction->notExist()->swooleSave()->dbSave();
           $transaction->commit();
       } catch (\Exception $e) {
           return app()->error_handler->handle(app(), $e);
       }
       app()->setStatusCode(200);
       return true;
   }
}