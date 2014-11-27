
------------
Differences
------------

/config/config.inc.php
— set login info to database



a) local — changes:

/view/twig/templates/header.html
	— <base href="/semestralka/">

/view/php/logout.php
	— header("Location:/semestralka/index/");

/.htaccess
	— z local.htaccess


-------------


b) hosting — changes:

/view/twig/templates/header.html
	— <base href="http://semestralka.ondrejpittl.cz/">

/view/php/logout.php
	— header("Location:/index/");

/.htaccess
	— z hosting.htaccess