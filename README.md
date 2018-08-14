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
```
php run.php -yourcsv.csv
```
