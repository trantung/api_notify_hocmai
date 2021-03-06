<?php
namespace App\Http\Controllers;
use Illuminate\http\Request;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use Exception;

class FireBaseController extends Controller
{
   public function index()
   {
        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/dangnvcafe-firebase-adminsdk-it8n0-a0ed2d4c8c.json');
        $firebase = (new Factory)
        ->withServiceAccount($serviceAccount)
        ->create();
        $database = $firebase->getDatabase();

        $database ->getReference('config/website')->set([
            'id'=>2,
            'name'=>'dangnv22',
            'email'=>'dangnvư222@gmail.com'
        ]);
        echo 'thành công';
   }
       
}