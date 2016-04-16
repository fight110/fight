<?php
/*
 * jQuery File Upload Plugin PHP Example 5.14
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

error_reporting(0);
require('UploadHandler.php');
$upload_url = sprintf("files/%s/", date("Y/m/d/i"));
$options 	= array(
	'upload_dir'	=> $upload_url,
	'upload_url'	=> $upload_url,
	'image_versions'=> array(
		'' => array(
                    'auto_orient' => true
                ),
		'medium' => array(
                    'max_width' => 800,
                    'max_height' => 800
                ),
		// 'thumbnail' => array(
		// 			'max_width' => 80,
  //                   'max_height' => 80
  //               )
	)
);
$upload_handler = new UploadHandler($options);
