I started writing this small test project 'Muesli' in PHP at 22:40, 24th November 2012, after about 8 years of not writing in PHP for money.
Why 'muesli'? I have a tradition to name home projects after the last disappeared piece of food.
The purpose of this project is to imagine I want to add some sample code in PHP to my CV.
Keywords: php sample code, PHP Testaufgabe, php interview sample code.

03:02am, 25th November - enough for now. Features:
+ Register, login, protected area access, logout, password reset functionality.
+ Maintain last login time for every user.
+ Password reset page sends you an e-mail with reset token, you can enter it manually or follow the link.
+ If the user logs in after the password reset e-mail has been sent, reset-token will be nullified because we know the user knows his password.
+ Use SQLite (apt-get install php5-sqlite) via PDO.
+ Use prepared statements not to worry about SQL injection.
+ Extracted templates for web-pages and e-mails into separate templates/ folder. Class Template can work with them.
+ Auto-create tables when necessary, bonus feature is to show count of users logged in the past 60 seconds.
+ Display errors on localhost only (aka development) and always log errors in your tmp directory (/tmp for example).
+ Store SQLite DB under tmp directory.
+ All post requests are protected from the double-submit problem: process request, set message to show and redirect to the next page.
+ I deliberately have not used already existing CMSes or third-party libraries, such as Smarty.
+ Show in the footer of every page how long it took to generate it. Also show how many users logged in in the past 60 seconds.

08:42am, 25th November 2012. Features: 
+ Added PHPUnits for UserManager class. Run phpunit/run.sh, which will download 2.4mb phpunit and run the tests.
09:44am, 25th November 2012 - enough for now.

TODO: 
* Add client-side validation, passwords should be non-empty and strong enough, also entered in two fields.
* Add MySQL support, it should not be a big problem since I use PDO and SQL I used should be compatible with MySQL.
* Add installation page, where you can specify which DB to connect to and so on.
* Add prefix to 'user' table since some people may re-use already existing DB with user table.
* Add CSS for pages to make them a bit nicer.
* Add a GD-based turing test to force a user to confirm he is a human, otherwise he can guess a password.
* Extract all messages into separate messages file, using gettext for example.
* One can even translate these messages into other languages and then show messages in user's favourite language.
