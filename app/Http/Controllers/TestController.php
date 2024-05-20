<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Storage;
use DB;
class TestController extends Controller
{

    public function index()
    {
        
        // return $this->countDirectories($path);
        return $this->importData();
    }
    public function countDirectories($folderPath): array
    {
        // Ensure the folder path ends with a trailing slash
            $folderPath = rtrim($folderPath, '/') . '/';

            // Get list of all files (including directories) inside the specified folder
            $allFiles = File::allFiles($folderPath);

            // Filter out directories and retrieve file names with extensions
            $fileNames = array_map(function ($file) {
                return pathinfo($file, PATHINFO_BASENAME); // Get the file name with extension
            }, $allFiles);

            return $fileNames;
    }

    public function importData()
    {

        $path=storage_path('app\public\db');
        $fileArray=$this->countDirectories($path);
        // return $fileArray;
        for ($i=0; $i < count($fileArray); $i++) { 
            if (File::exists($path.'\\'.$fileArray[$i])) {
                // Read the contents of the SQL file
                // echo $path.'\\'.$fileArray[$i].'<br/>';
                $sqlContents = File::get($path.'\\'.$fileArray[$i]);
                $offConstraits="SET session_replication_role = 'replica';";
                $onConstraits="SET session_replication_role = 'origin';";
                if($sqlContents!=';'){
                    // echo $offConstraits.$sqlContents.$onConstraits;
                    try {
                        DB::connection('pgsql')->unprepared($offConstraits.$sqlContents.$onConstraits);
                        $msg=$fileArray[$i]." inserted successfully";
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                    $msg="<p style='color:green;'>".$msg."</p>";
                    echo $msg;
                }
                
                // echo $sqlContents; // Example: Output SQL content to the screen
                // break;
            } else {
                // Handle case when file does not exist
                echo "<p style='color:red;'>".$fileArray[$i]."  file not found! </p></br>";
            }
        }
    }
}
