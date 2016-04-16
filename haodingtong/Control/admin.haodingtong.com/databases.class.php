<?php
	class Control_databases{
		public static function Action_export($r){
	        set_time_limit(0);
	        @ini_set('memory_limit','1024M');

			$host 		= '127.0.0.1';
			$dbname 	= HDT_MYSQL_DBNAME;
			$user 		= DB_MASTER_USERNAME;
			$password	= DB_MASTER_PASSWORD;
			$filename 	= $dbname.'_'.date('YmdH').'.sql';
			$dir  		= 'tmpl/sql/';

			$dumpfile 	= $dir.$filename;
			$shell		= 'mysqldump -u'.$user.' -p'.$password.' '.$dbname.' > '.$dumpfile;
			
        	Rrmdir::rrmdir($dir);

	        if(!is_dir($dir)){
	            mkdir($dir, 0777, true);
	        }

			exec($shell);

	        header('Content-type: application/sql');
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			readfile($dumpfile);
		}
	}