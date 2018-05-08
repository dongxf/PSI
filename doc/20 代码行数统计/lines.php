<?php

function getFileLines($file) {
	$line = 0 ; //初始化行数  
	//打开文件
	$fp = fopen($file, 'r');  
	if($fp){  
		while(stream_get_line($fp, 8192, "\n")){  
		   $line++;  
		}  
		fclose($fp);//关闭文件  
	}  else {
		echo $fp . " is not a file \n";
	}

	return $line;	
}

function allFileLines($directory)
{
	$result = 0;
	
    $mydir = dir($directory);
    while($file = $mydir->read())
    {
        if((is_dir("$directory/$file")) && ($file != ".") && ($file != ".."))
        {
			// 子文件夹，递归调用本身
            $result += allFileLines("$directory/$file");
        }
        else {
			if ($file == "." || $file == "..") {
				continue;
			} else {
				$result += getFileLines("$directory/$file");				
			}
		}
    }
    $mydir->close();
	
	return $result;
}

$totalLines = 0;

$baseDir = dirname(__FILE__) . "/../../web";

$pathList = array(
	$baseDir . "/Application/Home/DAO",
	$baseDir . "/Application/Home/Service",
	$baseDir . "/Application/Home/Controller",
	$baseDir . "/Application/Home/View",
	$baseDir . "/Public/Scripts/PSI",
	$baseDir . "/Public/Content",
	$baseDir . "/Application/H5/Service",
	$baseDir . "/Application/H5/Controller",
	$baseDir . "/Application/H5/View",
	$baseDir . "/Public/H5/JS",
	$baseDir . "/Public/H5/Pages",
	$baseDir . "/Public/H5/css",
	);

foreach ($pathList as $filePath) {
	$result = allFileLines($filePath);
	$totalLines += $result;
	echo "[{$filePath}] lines = {$result} \n";  	
}

echo "\n";
echo "total lines = {$totalLines} \n\n";