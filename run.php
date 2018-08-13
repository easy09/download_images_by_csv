<?php
/**
 * 保存图片
 * @param $url
 * @param string $filename
 * @return bool|string
 */
function get_image_byurl($url, $filename = "")
{

    if ($url == "") {
        return false;
    }
    $ext = strrchr($url, ".");
    if ($ext != ".gif" && $ext != ".jpg" && $ext != ".bmp") {
        $ext = ".jpg";
    }
    if ($filename == "") {
        $filename = time() . "_" . rand(100000, 999999) . $ext;
    }
    $write_fd = @fopen($filename, "a");
    @fwrite($write_fd, CurlGet($url));
    @fclose($write_fd);
    return ($filename);
}

/**
 * 远程获取
 * @param $url
 * @return mixed
 */
function CurlGet($url)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, false);
//curl_setopt($curl, CURLOPT_REFERER,$url);
    curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; SeaPort/1.2; Windows NT 5.1; SV1; InfoPath.2)");  //模拟浏览器访问
    curl_setopt($curl, CURLOPT_COOKIEJAR, ‘cookie . txt’);
    curl_setopt($curl, CURLOPT_COOKIEFILE, ‘cookie . txt’);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
    $values = curl_exec($curl);
    curl_close($curl);
    return ($values);
}


function downloadJpgFromCsv($csv)
{
    $file = fopen($csv, "r");
    while (!feof($file)) {
        $data = fgetcsv($file);
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (strstr($value, '.jpg')) {
                    $tmp=explode('/', $value);
                    $newFileName = array_pop($tmp);
                    $filename = 'dir_'.$csv . '/' . $key . '/' . $newFileName;
                    get_image_byurl($value, $filename);
                }
            }
        }
    }
    fclose($file);
}

set_time_limit(0);
ini_set('memory_limit', '4096M');
$argv=getopt('f:');
if(!isset($argv['f'])){
    exit("php run.php -f yourcsv.csv");
}
if(strtolower(substr($argv['f'],-4)) != '.csv'){
    exit("only download by *.csv");
}
$file=trim($argv['f']);
if(!file_exists($file)){
    exit("$file does not exist!");
}
echo "start download  {$file}\n";
downloadJpgFromCsv($file);

