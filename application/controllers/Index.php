<?php

use Illuminate\Database\Capsule\Manager as DB;

class IndexController extends AbstractController
{

    // 默认Action
    public function indexAction()
    {

        $user = DB::table('users')->where('username', 'molaifeng1')->first();
        var_dump($user);exit;

//        DB::table('users')->chunk(100, function($users)
//        {
//
//            foreach ($users as $user)
//            {
//                echo $user['username'];
//            }
//            return false;
//        });

//        $name = DB::table('users')->lists('username', 'email');
//        print_r($name);

//        $users = DB::table('users')->select('username as name', 'email')->get();
//        print_r($users);

//        $admin = DB::table('users')
//                ->whereIdAndEmail(2, 'john@doe.com')
//                ->first();
//        print_r($admin);

//        try {
//            DB::beginTransaction();
//            $users = DB::table('users')
//                        ->select('username as name', 'email')
//                        ->where('username', 'molaifeng')
//                        ->orwhere('username', 'overtrue')
//                        ->get();
//            DB::commit();
//            //echo print_r(DB::getQueryLog(), 1);
//        } catch (Exception $e) {
//            echo $e->getMessage();
//        }
//
//
//        exit;


//        DB::table('users')->insert([
//            array('username' => 'molaifeng1',  'email' => 'molaifeng1@foxmail.com')
//        ]);

//        $test = new UserModel();
//        $data = $test->getInfo();
//        print_r($data);
//exit;
//        $this->getView()->assign("content", "Hello World");
        $this->getView()->display('layout/index.html');
    }

}
