<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use App\CustomStuff\CustomDirectory\WavFile;
ini_set('max_execution_time', 300);
ini_set('memory_limit', '-1');


Route::get('/', 'Index@index')->name("index_page");

Route::post('login', 'Login@checkLogin')->name('Login');

Route::post('signup', 'SignUp@Sign_up')->name('signup');


Route::get('songDetail/{id}', 'SongDetail@get_Song');

Route::get('BuySong', 'BuySong@song_table')->name('buy_song')->middleware(['Normal_user']);

Route::get('BuySong/{id}', 'BuySong@signtature_song')->middleware(['Normal_user'])->where(['so' => '[0-9]'])->name('buysong_detail');

Route::get('logout','Logout@index')->name('logout');

Route::get('UploadSong','UploadSong@index')->name('Uploadsong')->middleware(['Normal_user', 'Admin_user']);

Route::post('UploadSong','UploadSong@postSong')->name('PostSong')->middleware(['Normal_user', 'Admin_user']);

Route::get('RevertSignature', 'RevertSignatureSong@index')->name('get_Revert')->middleware(['Normal_user']);

Route::post('RevertSignature','RevertSignatureSong@postSong')->name('Revert')->middleware(['Normal_user']);

// Route Put SOng item from google API
Route::get('put-existing/{filename}', function($filename) {
    $fileName= '../public/audios/'.$filename;;
    $fileData = File::get($fileName);
    Storage::cloud()->put($filename, $fileData);
    return 'File was saved to Google Drive';
});

// Route get list item from google API
Route::get('list', function() {
    $dir = '/';
    $recursive = false; // Get subdirectories also?
    $contents = collect(Storage::cloud()->listContents($dir, $recursive));
    //return $contents->where('type', '=', 'dir'); // directories
    return $contents->where('type', '=', 'file'); // files
})->name("get_list");

// route permision google drive API
Route::get('share', function() {
    $filename = 'test.txt';
    // Store a demo file
    Storage::cloud()->put($filename, 'Hello World');
    // Get the file to find the ID
    $dir = '/';
    $recursive = false; // Get subdirectories also?
    $contents = collect(Storage::cloud()->listContents($dir, $recursive));
    $file = $contents
        ->where('type', '=', 'file')
        ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
        ->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
        ->first(); // there can be duplicate file names!
    // Change permissions
    $service = Storage::cloud()->getAdapter()->getService();
    $permission = new \Google_Service_Drive_Permission();
    $permission->setRole('reader');
    $permission->setType('anyone');
    $permission->setAllowFileDiscovery(false);
    $permissions = $service->permissions->create($file['basename'], $permission);
    return Storage::cloud()->url($file['path']);
});




Route::get('downloadfile', function() { 
    $path = '1v-JaIv6putERcWxtrEi8jzpGlNxzZXQQ';
    $dir = '/';
    $recursive = false; // Get subdirectories also?
    $contents = collect(Storage::cloud()->listContents($dir, $recursive));
    //var_dump($contents);
    $file = $contents
        ->where('type', '=', 'file')
        ->where('path', '=', $path)
        ->first(); // there can be duplicate file names!
    //return $file; // array with file info
    //var_dump($file);
    if(!$file){
        echo "Your file have problem.";
        die();
    }
    $rawData = Storage::cloud()->get($file['path']);
    return response($rawData, 200)
        ->header('ContentType', $file['mimetype'])
        ->header('Content-Disposition', "attachment; filename='$path'");
});