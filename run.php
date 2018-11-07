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
    if (!file_exists($filename)) {
        $write_fd = @fopen($filename, "a");
        @fwrite($write_fd, CurlGet($url));
        @fclose($write_fd);
    }
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
    curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; SeaPort/1.2; Windows NT 5.1; SV1; InfoPath.2)");
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
function downloadPicFromCsv($csv, $prefix = '', $start = 0)
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
                if (strstr($url, '.jpg') || strstr($url, '.png') || strstr($url, '.gif') || strstr($url, '.bmp')) {
                    $tmp = explode('/', $url);
                    $newFileName = array_pop($tmp);
                    $path = $root . '/' . $key;
                    if (!file_exists($path)) {
                        mkdir($path);
                        chmod($path, 0777);
                    }
                    $imgname = $path . '/' . $newFileName;
                    if (strtolower(substr($url, 0, 4)) != 'http') {
                        if ($prefix) {
                            $url = $prefix . $url;
                        } else {
                            exit("URL must start with http,may be you need to add the param -p");
                        }
                    }
                    $i++;
                    if ($i > $start) {
                        $command = "php run.php -f $csv -i $imgname -u $url";
                        $comm[] = popen($command . " &", 'r');
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
$argv = getopt('f:p:u:i:s:');

$file = isset($argv['f']) ? trim($argv['f']) : '';
$url = isset($argv['u']) ? trim($argv['u']) : '';
$imgname = isset($argv['i']) ? trim($argv['i']) : '';
$prefix = isset($argv['p']) ? trim($argv['p']) : '';
$start = isset($argv['s']) ? intval($argv['s']) : 0;
if (!$file) {
    echo "    -f csv file name \n";
    echo "    -p the url prefix \n";
    echo "    -s the start number,if download stopped,you can restart from the start number \n";
    echo "Example:\n";
    echo "php run.php -f yourcsv.csv\n";
    echo "php run.php -f yourcsv.csv -s 19001\n";
    echo "php run.php -f yourcsv.csv -p http://www.yourwebsite.com/image_path\n";
    echo "Note:\n";
    echo "1.Only download .png/.jpg/.bmp/.gif file\n";
    echo "2.If file exist,download will jump it\n";
    exit;
}
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
if (!file_exists($file)) {
    exit("$file does not exist!");
}
if ($prefix && strtolower(substr($prefix, 0, 4)) != 'http') {
    exit("prefix must start with http");
}
if ($prefix) {
    $prefix = substr($prefix, -1) == '/' ? $prefix : $prefix . '/';
}
echo "start download {$file}\n";
if ($start) {
    echo "start from number = " . $start . '\n';
}
downloadPicFromCsv($file, $prefix, $start);