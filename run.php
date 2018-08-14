<?php
/**
 * save image
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
 * get from website
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


/**
 * download
 * @param $csv
 */
function downloadJpgFromCsv($csv)
{
    $root = __DIR__ . '/dir_' . $csv;
    if (!file_exists($root)) {
        mkdir($root);
        chmod($root, 0777);
    }
    $file = fopen($csv, "r");
    $i = 0;
    $comm = [];
    while (!feof($file)) {
        $data = fgetcsv($file);
        if (is_array($data)) {
            foreach ($data as $key => $url) {
                if (strstr($url, '.jpg')) {
                    $tmp = explode('/', $url);
                    $newFileName = array_pop($tmp);
                    $path = $root . '/' . $key;
                    if (!file_exists($path)) {
                        mkdir($path);
                        chmod($path, 0777);
                    }
                    $imgname = $path . '/' . $newFileName;
                    $command = "php run.php -f $csv -i $imgname -u $url";
                    $comm[] = popen($command . " &", 'r');
                    $i++;
                    if ($i % 100 == 1) {
                        echo ($i) . " files downloaded!\n";
                        while ($comm) {
                            $c = array_shift($comm);
                            pclose($c);
                        }
                    }
                }
            }
        }
    }
    while ($comm) {
        $c = array_shift($comm);
        pclose($c);
    }
    fclose($file);
    echo "Download finished!\n";
    echo "total files:" . $i;
}


set_time_limit(0);
ini_set('memory_limit', '4096M');
$argv = getopt('f:u:i:');
if (!isset($argv['f'])) {
    exit("php run.php -f yourcsv.csv");
}
$url = isset($argv['u']) ? $argv['u'] : NULL;
$imgname = isset($argv['i']) ? $argv['i'] : NULL;
if ($url && $imgname) {
    if (strtolower(substr($url, 0, 4)) != 'http') {
        exit("URL must start with http");
    }
    get_image_byurl($url, $imgname);
    exit;
}
if (strtolower(substr($argv['f'], -4)) != '.csv') {
    exit("only download by *.csv");
}
$file = trim($argv['f']);
if (!file_exists($file)) {
    exit("$file does not exist!");
}
echo "start download {$file}\n";
downloadJpgFromCsv($file);