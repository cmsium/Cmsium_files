<?php
namespace App\Controllers;

use App\File;
use App\Link;
use Files\drivers\Swoole;
use Files\FileManager;
use Transaction\Transaction;
use \Validation\Validator;

/**
 * @description Basic actions with files itself (upload, read, delete ...)
 */
class FileController {
   use \Router\Routable;

   /**
    * @summary Returns a file associated with given hash.
    * @description Returns requested file associated with given hash (usually received from controller).
    */
   public function getFile ($hash) {
       $validator = new Validator(['hash' => $hash],"GetFile");
       $result = $validator->get();
       if ($errors = $validator->errors()){
           app()->setStatusCode(500);
           return $errors;
       }
       $link = new Link($result, app()->links, app()->mysql);
       $client = app()->queue_client;
       $file = new File(app()->files, app()->mysql, $link, $client);
       try {
           $transaction = new Transaction(compact("link","file"));
           $transaction->link->CheckStatus("read");
           $transaction->file->createFromLink()->get()->send(app());
           $transaction->link->tempDelete();
           $transaction->commit();
       } catch (\Exception $e) {
           app()->setStatusCode($e->getCode());
           return $e->getMessage();
       }
       //app()->setStatusCode(200);
       return $file->isSend();
   }

   /**
    * @summary Delete a file associated with given hash.
    * @description Delete requested file associated with given hash (usually received from controller).
    */
   public function deleteFile ($hash) {
       $validator = new Validator(['hash' => $hash],"DeleteFile");
       $result = $validator->get();
       if ($errors = $validator->errors()){
           app()->setStatusCode(500);
           return $errors;
       }

       $link = new Link($result, app()->links, app()->mysql);
       $client = app()->queue_client;
       $file = new File(app()->files, app()->mysql, $link, $client);
       try {
           $transaction = new Transaction(compact("link","file"));
           $transaction->link->CheckStatus("delete");
           $transaction->file->createFromLink()->get()->makeDeleted()->deferredDelete();
           $transaction->link->delete();
           $transaction->commit();
       } catch (\Exception $e) {
           app()->setStatusCode($e->getCode());
           return $e->getMessage();
       }
       app()->setStatusCode(200);
       return true;
   }

   /**
    * @summary File upload request.
    * @description File upload request.
    */
   public function uploadFile ($hash) {
       // validate
       $validator = new Validator(['hash' => $hash],"UploadFile");
       $result = $validator->get();
       if ($errors = $validator->errors()){
           app()->setStatusCode(500);
           return $errors;
       }
       $link = new Link($result, app()->links, app()->mysql);
       $manager = new FileManager(new Swoole());
       $file = new File(app()->files, app()->mysql, $link);
       try {
           $transaction = new Transaction(compact("link","file"));
           $transaction->link->CheckStatus("upload");
           $transaction->file->createFromLink();
           $transaction->file->upload($manager, app()->request->files, 'file', ROOTDIR . '/storage');
           $transaction->file->swooleSave()->dbSave();
           $transaction->link->makeRead();
           $transaction->commit();
       } catch (\Exception $e) {
           if ($file->file_id) {
               try{
                   app()->controller_client->deleteFile($file);
               } catch (\Exception $ex){
                   //TODO log
                   var_dump($ex->getMessage());
               }
           }
           app()->setStatusCode($e->getCode());
           return $e->getMessage();
       }
       app()->setStatusCode(200);
       return ["url" => $link->getUploadLink(app()->host), "id" => $file->file_id];
   }
}