<?php
declare(strict_types=1);
define('APP_BASE', rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
define('PROJECT_PAGE', file_exists(__DIR__.'/projet.php') ? 'projet.php' : 'project.php');
