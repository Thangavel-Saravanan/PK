PK SITE - FULL PACKAGE

1. Extract everything into C:\xampp\htdocs\pk-site\  (index.php must be directly inside pk-site, not pk-site\pk-site)
2. Start Apache + MySQL in XAMPP.
3. phpMyAdmin > SQL tab > paste install.sql > Go   (only once, for a new database)
4. Open http://localhost/pk-site/admin/login.php  -> admin / ChangeMe@123
5. Click "Account" and set your own username and password (stored in the database).

Big videos/images: in C:\xampp\php\php.ini set upload_max_filesize=100M and post_max_size=100M, then restart Apache.
DB name, user, password: edit config.php.
