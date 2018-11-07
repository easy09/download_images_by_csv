# download_images_by_csv
get images's url from csv file,then download them.
## effect
> The website may not be classified when uploading files. In this case, you will need to export the images according to the database field classification.
>
> This project uses the "popen" method for multi-threaded downloads, no additional requirements for the php version and system environment.
## steps
- If the data in the table is not appropriate, maybe you need to generate a view
- explort csv file from datebase
- run commond

## params
```
    -f csv file name
    -p the url prefix
    -s the start number,if download stopped,you can restart from the start number
```
## Example:
```
php run.php -f yourcsv.csv
php run.php -f yourcsv.csv -s 19001
php run.php -f yourcsv.csv -p http://www.yourwebsite.com/image_path
```
### Note:
```
1.Only download .png/.jpg/.bmp/.gif file
2.If file exist,download will jump it
```
