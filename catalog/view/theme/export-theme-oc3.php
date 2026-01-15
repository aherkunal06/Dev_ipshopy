<?php
/**
 * @package Extension Download
 * @version 1.0.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2013 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 */


/**
 * return subfolder and file in directory
 */
	$exts_group = array(
						1=>'templates',
						); 
	
	/**
     * Get list folder
     *
     * @param     string              $dir       path directory
     *
     * @return    array               $dir      
    */
	function getFolder($dir , $type = 'folder'){
		$result = array();
		$items = scandir($dir);
		
		if(!empty($items)){
			foreach($items as $key => $item){
				if(!in_array($item,array(".",".."))){
					
					if($type == 'folder') {
						if(is_dir($dir.DIRECTORY_SEPARATOR.$item)){
							$result[] = $item;
							
						}
					}else{
						if(is_file($dir.DIRECTORY_SEPARATOR.$item)){
							$result[] = $item;
						}
					}
				}
			}
		}
		return $result;
	}

	/**
     * Remove charter('mod','_')
     *
     * @param     string              $str      
     *
     * @return    string              $_str      
    */
	function _ucWords($str){
		$_str = $str;
		if($str != ''){
			if(strpos($str, '_')) {
				$_str = str_replace('_', ' ', $str);
				$_str = str_replace('mod ', '', $_str);
			}
			$_str = ucwords($_str);
		}
		return $_str;
	}

	/**
     * zip gop lai tat ca folder
     *
     * @param     string              $folder_path       path directory
	 * @param     string              $local_path        path directory
	 * @z     	  string              $local_path        path directory
     *
     * @return    array               $dir      
     */
	function addAll($folder_path,$local_path,$z){
		if (is_dir($folder_path)){
			$dh=opendir($folder_path);
			while (($file = readdir($dh)) !== false) {
				if( ($file !== ".") && ($file !== "..") &&  $file !=="placehold_img" && $file !=="configuration.php" && $file !=="extensions.php" && strpos($file, '.svn') === false){
								

					if (is_file($folder_path.$file)){
						
						$z->addFile($folder_path.$file,$local_path.$file);
					}else{
						
						addAll($folder_path.$file."/",$local_path.$file."/",$z);
					}
				}
			}
			
		}else{
			echo "The directory $folder_path not exists.";
			exit;
		}
	}
	
	/**
     * Check if string contains a value in array
     */
	function contains($str, array $arr){
		foreach($arr as $a) {
			$place = strpos( $a ,$str);
			if (!empty($place)) {
				return $a;
			} 
		}
	}
	
	/**
     * zip group file
     *
     * @param     string              $type      	 single/group
	 * @param     string              $ext_name      name extenstion
	 * @param     string              $name_gr       name group
     *
     * @return    array               $dir      
     */
	function zipFileOuput($type,$ext_name,$name_gr = '', $read_me = ''){
		
		$folder = 'exts_dowload_tmp';
		$file_path_group = null;
		if($type == 'single'){
			$file_path = getFolder($folder , '');
			$file_path_name  = contains('zip',$file_path);
			$file_path_group =  $folder.'/'.$file_path_name;
			
		}else{
			if($name_gr != ''){
				$file_path_group = $folder.'/'.$name_gr.'.zip';
			}else{
				$file_path_group = $folder.'/please_rename_UNZIPFIRST.zip';
			}
			if($read_me != '') {
				$fileLocation = $folder. "/readme.txt";
				$file = fopen($fileLocation,"w");
				$content = $read_me;
				fwrite($file,$content);
				fclose($file);
			}
			
			$zip = new ZipArchive();
			
			if ($zip->open($file_path_group, ZIPARCHIVE::CREATE) === true) {
				if(!file_exists($file_path_group)){
					addAll($folder.'/',"",$zip);
				}
				$zip->close();
			}
			
		}
		
		if(file_exists($file_path_group)){
			ob_end_clean();
			
			header("Content-type: application/zip;\n");
			header("Content-Transfer-Encoding: Binary");
			header("Content-length: ".filesize($file_path_group).";\n");
			header("Content-disposition: attachment; filename=\"".basename($file_path_group)."\"");
			readfile($file_path_group);
		}else {
			exit("Could not find Zip file to download");
		}
		
		
		_delelteFolder($folder);
		return true;
		
	}
	
	/**
     * rename file
     *
     * @param     string              $oldfile      path directory
	 * @param     string              $newfile      path directory
     *     
    */
	function rename_win($oldfile,$newfile) {
	   if (!rename($oldfile,$newfile)) {
		  if (copy ($oldfile,$newfile)) {
			 unlink($oldfile);
			 return TRUE;
		  }
		  return FALSE;
	   }
	   return TRUE;
	}
	
	/**
     * delelte Folder
     *
     * @param     string              $folder      path directory
     *     
    */
	function _delelteFolder($folder){
		
		if(is_dir($folder)){
			$folder_handler = dir($folder);
			while ($file = $folder_handler->read()) {
				
				if ($file != "." && $file != "..") {
					if (filetype($folder."/".$file) == "dir") {
						_delelteFolder($folder."/".$file);
					} else { 
						unlink($folder."/".$file);
					}					
				}

			}
			$folder_handler->close();
			rmdir($folder);
			
		}
	}
	
	/**
     * Copy Folder
     *
     * @param     string              $source      path directory
     *     
    */
	function copydir($source,$destination){
		if(!is_dir($destination)){
			$oldumask = umask(0); 
			mkdir($destination, 01777); // so you get the sticky bit set 
			umask($oldumask);
		}	
		$dir_handle = @opendir($source);
		if ( $dir_handle != false ){
			while ($file = readdir($dir_handle)) 
			{
				if($file!="." && $file!=".." && !is_dir("$source/$file")) //if it is 				
					copy("$source/$file","$destination/$file");
				if($file!="." && $file!=".." && is_dir("$source/$file")) //if it is folder				
					copydir("$source/$file","$destination/$file");
			}
			closedir($dir_handle);
		}
	}	
	
	/**
     * check các exten,temp
     *
     * @param     string              $gr      path directory
	 * @param     string              $ext_name      path directory
     *     
    */
	function _proGeneral($gr = 'combo', $ext_name){
		$folder = 'exts_dowload_tmp';
		$jversion = null;
		if(!file_exists($folder)){
			mkdir ($folder, 0777);
		}

		if(!defined('_JEXEC')){
			define('_JEXEC',1) ;
		}
		
		if (!defined('JPATH_PLATFORM'))
		{
			define('JPATH_PLATFORM',__DIR__);
		}
		
		$templ	= dirname(realpath(__FILE__))."\\".$ext_name;
		$templ	= str_replace('\\', '/', $templ);
		
		if(file_exists($templ.'/index.php')) {
			if(!defined('VERSION')) {
				define('VERSION', '3.0.0');
				//include ($templ.'\\index.php');
			}
			$jversion = VERSION;
			
		}		
		
		$file_name = $ext_name;
		
		switch($gr){
			case 'combo':
				$templ	= dirname(realpath(__FILE__))."\\".$file_name;
				$templ	= str_replace('\\', '/', $templ);
				
				$path_vqmod = $templ.'/vqmod/xml';
				$xml = simplexml_load_file($path_vqmod.'/soconfig_theme.xml');
				$_file_name = _getVersion($file_name,$jversion, $xml,$gr);
				
				//Creative folder of exts_dowload_tmp/package 
				//Path folder system, image, vqmod
				$package 							= $folder.'/upload';	
				$path_system_library_src 			= $package.'/system/library/so';
				$path_system_library_onepagecheckout_src 					= $package.'/system/library/so_onepagecheckout';
				$path_system_library_social_src 			= $package.'/system/library/so_social';
				$path_image_src 					= $package.'/image/catalog';
				$path_admin_language 				= '/admin/language/en-gb/extension/module/';
				
				$path_catelog_language_mod 				= '/catalog/language/en-gb/extension/module';
				$path_catelog_language_simpleblog 				= '/catalog/language/en-gb/extension/simple_blog';
				$path_catelog_language_soconfig 				= '/catalog/language/en-gb/extension/soconfig';
				
				//Path folder catalog
				$path_cat_controller_module_src 	= $package.'/catalog/controller/extension/module';
				$path_cat_controller_soconfig_src 	= $package.'/catalog/controller/extension/soconfig';
				$path_cat_controller_mobile_src 	= $package.'/catalog/controller/extension/mobile';
				$path_cat_controller_simpleblog_src 	= $package.'/catalog/controller/extension/simple_blog';
				$path_cat_model_custom_src 	= $package.'/catalog/model/extension/soconfig';
				$path_cat_model_simpleblog_src 	= $package.'/catalog/model/extension/simple_blog';
				$path_cat_model_module_src 	= $package.'/catalog/model/extension/module';
				$path_cat_language_mod_src 				= $package.$path_catelog_language_mod;
				$path_cat_language_simpleblog_src 				= $package.$path_catelog_language_simpleblog;
				$path_cat_language_soconfig_src 				= $package.$path_catelog_language_soconfig;
				$path_cat_view_src 					= $package.'/catalog/view';
				
				//Path folder admin
				$path_admin_controller_module_src 	= $package.'/admin/controller/extension/module';
				$path_admin_language_src 			= $package.$path_admin_language;
			
				$path_admin_model_module_src 		= $package.'/admin/model/extension/module';
				$path_admin_view_javascript_src 	= $package.'/admin/view/javascript';
				$path_admin_view_template_soconfig 	= $package.'/admin/view/template/extension/soconfig';
				$path_admin_view_template_module	= $package.'/admin/view/template/extension/module';
				
				//The mkdir() function creates a directory.
				if(!file_exists($package))				 	mkdir($package, 0777);

				if(!file_exists($path_system_library_src))					mkdir($path_system_library_src, 0777, true);
				if(!file_exists($path_system_library_onepagecheckout_src)) 	mkdir($path_system_library_onepagecheckout_src, 0777, true);
				if(!file_exists($path_system_library_social_src)) 			mkdir($path_system_library_social_src, 0777, true);

				if(!file_exists($path_image_src)) 			mkdir($path_image_src, 0777, true);
				
				if(!file_exists($path_cat_controller_module_src)) 			mkdir($path_cat_controller_module_src, 0777, true);
				
				if(!file_exists($path_cat_controller_soconfig_src)) 		mkdir($path_cat_controller_soconfig_src, 0777, true);
				if(!file_exists($path_cat_controller_mobile_src)) 			mkdir($path_cat_controller_mobile_src, 0777, true);
				if(!file_exists($path_cat_controller_simpleblog_src)) 		mkdir($path_cat_controller_simpleblog_src, 0777, true);
				if(!file_exists($path_cat_model_custom_src)) 				mkdir($path_cat_model_custom_src, 0777, true);
				if(!file_exists($path_cat_model_simpleblog_src)) 			mkdir($path_cat_model_simpleblog_src, 0777, true);
				if(!file_exists($path_cat_model_module_src)) 				mkdir($path_cat_model_module_src, 0777, true);
				if(!file_exists($path_cat_language_mod_src)) 				mkdir($path_cat_language_mod_src, 0777, true);
				if(!file_exists($path_cat_language_simpleblog_src)) 		mkdir($path_cat_language_simpleblog_src, 0777, true);
				if(!file_exists($path_cat_language_soconfig_src)) 			mkdir($path_cat_language_soconfig_src, 0777, true);
				if(!file_exists($path_cat_view_src)) 						mkdir($path_cat_view_src, 0777, true);
				
				if(!file_exists($path_admin_controller_module_src)) 		mkdir($path_admin_controller_module_src, 0777, true);
				if(!file_exists($path_admin_language_src)) 					mkdir($path_admin_language_src, 0777, true);
			
				if(!file_exists($path_admin_model_module_src)) 				mkdir($path_admin_model_module_src, 0777, true);
				if(!file_exists($path_admin_view_javascript_src)) 			mkdir($path_admin_view_javascript_src, 0777, true);
				if(!file_exists($path_admin_view_template_soconfig)) 		mkdir($path_admin_view_template_soconfig, 0777, true);
				if(!file_exists($path_admin_view_template_module)) 			mkdir($path_admin_view_template_module, 0777, true);
				
				
				//Copy folder themes 
				copydir($templ.'/image/catalog', $path_image_src);	
				
				copydir($templ.'/system/library/so', $path_system_library_src);	
				copydir($templ.'/system/library/so_onepagecheckout', $path_system_library_onepagecheckout_src);	
				copydir($templ.'/system/library/so_social', $path_system_library_social_src);	
				
				copydir($templ.'/catalog/controller/extension/module', $path_cat_controller_module_src);	
				copydir($templ.'/catalog/controller/extension/soconfig', $path_cat_controller_soconfig_src);	
				copydir($templ.'/catalog/controller/extension/mobile', $path_cat_controller_mobile_src);
				copydir($templ.'/catalog/controller/extension/simple_blog', $path_cat_controller_simpleblog_src);	
				copydir($templ.'/catalog/model/extension/soconfig', $path_cat_model_custom_src);	
				copydir($templ.'/catalog/model/extension/simple_blog', $path_cat_model_simpleblog_src);	
				copydir($templ.'/catalog/model/extension/module', $path_cat_model_module_src);	
				copydir($templ.$path_catelog_language_mod, $path_cat_language_mod_src);	
				copydir($templ.$path_catelog_language_simpleblog, $path_cat_language_simpleblog_src);	
				copydir($templ.$path_catelog_language_soconfig, $path_cat_language_soconfig_src);	
				copydir($templ.'/catalog/view', $path_cat_view_src);	
				
				copydir($templ.'/admin/controller/extension/module', $path_admin_controller_module_src);
				copydir($templ.$path_admin_language, $path_admin_language_src);
				
				copydir($templ.'/admin/model/extension/module', $path_admin_model_module_src);
				copydir($templ.'/admin/view/javascript', $path_admin_view_javascript_src);
				copydir($templ.'/admin/view/template/extension/soconfig', $path_admin_view_template_soconfig);
				copydir($templ.'/admin/view/template/extension/module', $path_admin_view_template_module);
				
				
				$file_path= $folder.'/'.$_file_name.'.zip';
				$zip_admin_module_array= array(
					'account',
					'affiliate',
					'amazon_login',
					'amazon_pay',
					'banner',
					'carousel',
					'ebay_listing',
					'featured',
					'filter',
					'google_hangouts',
					'html',
					'information',
					'latest',
					'laybuy_layout',
					'pp_button',
					'pp_login',
					'sagepay_direct_cards',
					'sagepay_server_cards',
					'special',
					'store',
					'bestseller',
					'slideshow',
					'divido_calculator',
					'klarna_checkout_module',
					'pilibaba_button',
					'pp_braintree_button',
				);

				$zip = new ZipArchive();
				if ($zip->open($file_path, ZIPARCHIVE::CREATE) === true) {
					if(!file_exists($file_path)){
						addAll($folder."/","",$zip);
					}
					
					//Delete a folder inside zip
					delete_directory($zip,'upload/admin/view/javascript/bootstrap/');
					delete_directory($zip,'upload/admin/view/javascript/font-awesome/');
					delete_directory($zip,'upload/admin/view/javascript/jquery/');
					delete_directory($zip,'upload/admin/view/javascript/summernote/');
					//delete_directory($zip,'upload/admin/view/javascript/openbay/');
					
					delete_directory($zip,'upload/catalog/view/theme/default/image');
					delete_directory($zip,'upload/catalog/view/theme/default/images');
					delete_directory($zip,'upload/catalog/view/theme/default/stylesheet');
					delete_directory($zip,'upload/catalog/view/theme/default/template/affiliate');
					delete_directory($zip,'upload/catalog/view/theme/default/template/checkout');
					delete_directory($zip,'upload/catalog/view/theme/default/template/common');
					delete_directory($zip,'upload/catalog/view/theme/default/template/information');
					delete_directory($zip,'upload/catalog/view/theme/default/template/mail');
					delete_directory($zip,'upload/catalog/view/theme/default/template/product');
					delete_directory($zip,'upload/catalog/view/theme/default/template/extension/captcha');
					delete_directory($zip,'upload/catalog/view/theme/default/template/extension/credit_card');
					delete_directory($zip,'upload/catalog/view/theme/default/template/extension/payment');
					delete_directory($zip,'upload/catalog/view/theme/default/template/extension/total');
					
					//Delete file from directory admin inside zip
					foreach ($zip_admin_module_array as $item) {
						$zip->deleteName('upload/admin/language/en-gb/extension/module/'.$item.'.php');
						$zip->deleteName('upload/admin/controller/extension/module/'.$item.'.php');
						$zip->deleteName('upload/admin/view/template/extension/module/'.$item.'.tpl');
						$zip->deleteName('upload/catalog/controller/extension/module/'.$item.'.php');
						$zip->deleteName('upload/catalog/language/en-gb/extension/module/'.$item.'.php');
					}
					
					$zip->close();
				}
				
				break;	
			case 'quick':	
				$templ	= dirname(realpath(__FILE__))."\\".$file_name;
				$templ	= str_replace('\\', '/', $templ);
				$path_vqmod = $templ.'/vqmod/xml';
				$xml = simplexml_load_file($path_vqmod.'/soconfig_theme.xml');
				
				// Rename directory "install"
				$oldname = $templ. '/~install';
				$newname = $templ.'/install';
				if (file_exists($oldname)) {
					$renameResult = rename_win($oldname, $newname );
				}
				
				$_file_name = _getVersion($file_name,$jversion, $xml,$gr);
				$file_path= $folder.'/'.$_file_name.'.zip';
				$zip = new ZipArchive();
				
				if ($zip->open($file_path, ZIPARCHIVE::CREATE) === true) {
					if(!file_exists($file_path)){
						addAll((dirname(realpath(__FILE__))).'\\'.$file_name.'\\',"",$zip);
					}
					//Delete a folder inside zip
					delete_directory($zip,'image/cache/');
					delete_directory($zip,'vqmod/vqcache/');
					
					//clear a text file config.php
					$zip->addFromString('admin/config.php','');
					$zip->addFromString('config.php','');
					
					//Delete file from directory vqmod inside zip
					$zip->deleteName('vqmod/checked.cache');
					$zip->deleteName('vqmod/mods.cache');
					
					$zip->close();
				}else{
					echo 'failed';
				}

				break;
			default:
		}
	}
	
	/**
     * Get version module file .xml
     *
     * @param     string              $file_name      
	 * @param     string              $jversion      
	 * @param     string              $xml     
     *     
    */
	function _getVersion($file_name,$jversion = null,$xml,$gr = null){
		if($gr == 'tmp') $file_name = $file_name.'_template';
		else if ($gr == 'combo')$file_name = $file_name.'_template';
		else if ($gr == 'quick')$file_name = $file_name.'_quickstart';
		else if ($gr == 'pls')$file_name = 'plg_system_'.$file_name;
		else if ($gr == 'plc')$file_name = 'plg_content_'.$file_name;
	
		if(isset($xml)) $xml->version='1.0.0';
		if($jversion != null) {
			
			if($gr=='combo'){
				$_file_name = $file_name.'_o'.$jversion.'_v'.$xml->version.'.ocmod';
			}else{
				$_file_name = $file_name.'_o'.$jversion.'_v'.$xml->version;
			}
			
		}else{
			$_file_name = $file_name.'_v'.$xml->version;
		}
		return $_file_name;
	}
	
	
	 //load books from xml to array
     function load($fname){
        $doc= new DOMDocument();
        if($doc->load($fname))  $res= parse($doc);
        else     throw new Exception('error load XML');

        return $res;
     }


    function parse($doc){
        $xpath = new DOMXpath($doc);
        $items = $xpath->query("book");
        $result = array();
        foreach($items as $item)
        {
           $result[]=array('fields'=>parse_fields($item));
        }
        return $result;
    }


    function parse_fields($node) {
        $res=array();
        foreach($node->childNodes as $child)
        {
           if($child->nodeType==XML_ELEMENT_NODE)
           {
              $res[$child->nodeName]=$child->nodeValue;
           }
        }
        return $res;
     }


     //save array to xml
	 function save($fname, $rows){
        $doc = new DOMDocument('1.0','utf-8');
        $doc->formatOutput = true;

        $books = $doc->appendChild($doc->createElement('books'));

        foreach($rows as $row)
        {
           $book=$books->appendChild($doc->createElement('book'));
           foreach($row['fields'] as $field_name=>$field_value)
           {
              $f=$book->appendChild($doc->createElement($field_name));
              $f->appendChild($doc->createTextNode($field_value));
           }
        }

        file_put_contents($fname, $doc->saveXML());
     }

	 
	 
	if(isset($_POST['submit']) && $_POST['submit'] == 'Download' && !empty($_POST['zip_group']) ) {
		$zip_group = $_POST['zip_group'];
		
		if($zip_group !=''){
			$group = '';
			$ext_name = '';
			foreach($zip_group as $zip){
				$tmp = explode('.',$zip);
				$group = $tmp[0];
				$ext_name = $tmp[1];
				$type = 'single';
				_proGeneral($group,$ext_name);
			}
			if(count($zip_group) > 1) {
				$type = 'group';
			}else{
				$type = 'group';
			}
			$name_gr =  isset($_POST['name-group'])?$_POST['name-group']:'';
			$read_me = isset($_POST['read_me'])?$_POST['read_me']:'';
			 zipFileOuput($type,$ext_name,$name_gr, $read_me);
		}
	}
	
	if(isset($_GET['zipfile'])){
		$file_name = $_GET['zipfile'];
		$tmp = explode('.',$file_name);
		$group = $tmp[0];
		$ext_name = $tmp[1];
		$type = 'single';
		
		// Enables all errors reporting
		ini_set('display_startup_errors',1);
		ini_set('display_errors',1);
		error_reporting(1);
		
		_proGeneral($group,$ext_name);
		zipFileOuput($type,$ext_name);
	}
	
	
	
	/**
     * Delete a folder inside zip
     *
    */
	function delete_directory($zip, $folder_to_delete) {
		for($i=0;$i<$zip->numFiles;$i++){
			$entry_info = $zip->statIndex($i);
		
			if(substr($entry_info["name"],0,strlen($folder_to_delete))==$folder_to_delete){
				$zip->deleteIndex($i);
			}
		} 
	}
	
	function expandDirectories($base_dir) {
		  $directories = array();
		  foreach(scandir($base_dir) as $file) {
				if($file == '.' || $file == '..') continue;
				$dir = $base_dir.DIRECTORY_SEPARATOR.$file;
				if(is_dir($dir)) {
					$directories []= $dir;
					$directories = array_merge($directories, expandDirectories($dir));
				}
		  }
		  return $directories;
	}


?>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>Quick Tool Package</title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width">
	
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css"/>
	<style type="text/css">.cf:after,.cf:before{content:" ";display:table}.cf:after{clear:both}.ext-download{margin:20px auto;padding:0;overflow:hidden;width:700px}.ext-download ul.extd-tabs{list-style:none;margin:0;padding:0;border-bottom:1px solid #DDD;border-left:1px solid #DDD}.ext-download ul.extd-tabs li{float:left;margin:0 0 -1px}.ext-download ul.extd-tabs li>a{text-decoration:none;border:1px solid #DDD;line-height:20px;padding:8px 15px;border-left:0;background-color:#F5F5F5;background-image:linear-gradient(to bottom,#FFF,#E6E6E6);background-repeat:repeat-x;border-color:rgba(0,0,0,.1) rgba(0,0,0,.1) #B3B3B3;border-image:none;box-shadow:0 1px 0 rgba(255,255,255,.2) inset,0 1px 2px rgba(0,0,0,.05);color:#333;cursor:pointer;display:inline-block;font-size:18px;margin-bottom:0;text-align:center;text-shadow:0 1px 1px rgba(255,255,255,.75);vertical-align:middle;border-bottom-color:#DDD}.ext-download ul.extd-tabs li.active>a{border-bottom-color:#FFF;box-shadow:none;background:0 0}.extd-tabs-content{border:1px solid #DDD;border-top:0;margin-bottom:30px;padding:20px 0}.extd-tabs-content ul{margin:0;padding:0 20px;list-style:none;display:none}.extd-tabs-content ul li{margin:2px 0;border-bottom:1px solid #FFF;border-top:1px solid transparent;color:#669;padding:8px;font-size:16px}.extd-tabs-content ul li.color{background:#eee}.extd-tabs-content ul li a{float:right;padding:3px 10px}.ext-download .extd-sbumit{text-align:center;margin:10px}.extd-tabs-content ul.extd-content-active{display:block}.extd-name-group,.extd-readmre{margin:10px 0}.align-center{text-align:center;margin:50px 0 30px;font-weight:700}</style>
	<?php $tag_id = 'ext_download'.rand().time(); ?>
	
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"  type="text/javascript"></script>
	<script type="text/javascript">
		$.noConflict();
		jQuery(document).ready(function($){
			;(function(element){
				var $element = $(element);
				var $extd_tab = $('.extd-tab',$element);
				var $tab_content = $('.extd-tab-pane',$element);
				$extd_tab.each(function(val,el){
					var $tab = $(this);
					$tab.on('click.download', function(){
						var $this = $(this);
						if($this.hasClass('active')) return false;
						$extd_tab.removeClass('active');
						$this.addClass('active');	
						$tab_content.removeClass('extd-content-active');
						var $tab_content_active = $this.attr('data-tab');
						$($tab_content_active).addClass('extd-content-active');
						return false;
					});
					
				});
			})('#<?php echo $tag_id; ?>');
		});
	</script>
	
</head>	
<body>
	<div class="ext-download" id="<?php echo $tag_id; ?>" >
		<h1 class="align-center">Tool Creative Package</h1>
		<ul class="extd-tabs cf">
			<?php foreach($exts_group  as $key=> $ext){ ?>	
			<li class="extd-tab <?php echo ($key == 1)?' active':'';?>" data-tab="<?php echo '.extd-'.$ext; ?>">
				<a data-toggle="tab" href="#<?php echo $ext ?>"><?php echo _ucWords($ext); ?></a>
			</li>
		  <?php  } ?>
		</ul>
		<form  method="post" action="" class="form-horizontal">
			<div class="extd-tabs-content">
				<div class="extd-tabs-content-inner">
				<?php
				
				
				foreach($exts_group  as $_key => $ext){
				$_ext = '';
				$gext = '';	
				if($ext == 'modules'){
					$gext = 'mod';
					$_ext = 'modules';
				}else if($ext == 'plugins_content'){
					$gext = 'plc';
					$_ext = 'plugins/content';
				}else if($ext == 'plugins_system'){
					$gext = 'pls';
					$_ext = 'plugins/system';
				}else if($ext == 'templates'){
					$gext = 'combo';
					$templ	= dirname(realpath(__FILE__));
					$templ = explode('\\', $templ);
					$templ_name = array_pop($templ);
					$_ext = dirname(dirname(realpath(__FILE__)));
					$_ext = $_ext.'\\'.$templ_name;
					
				}else if($ext == 'quickstart'){
					$gext = 'quick';
					$templ	= dirname(realpath(__FILE__));
					$templ = explode('\\', $templ);
					$templ_name = array_pop($templ);
					$_ext = dirname(dirname(realpath(__FILE__)));
					$_ext = $_ext.'\\'.$templ_name;
					
					
				}
				
				if(file_exists((__FILE__).'/index.php')) {
					if(!class_exists('JVersion')) {
						require_once dirname(__FILE__).'/index.php';
					}
					$version = new JVersion();
					$jversion = $version->RELEASE;
					
				}	
				$items = getFolder($_ext);
				$cls = 'extd-'.$ext;
				$cls .=  ($_key == 1)?' extd-content-active':'';
				if(!empty($items)) { ?>
				<ul class="extd-tab-pane  <?php echo $cls; ?> ">
					<?php  $i = 0; 
					foreach($items as $item) { 
						$i++;
					?>
						<li class="item-content <?php echo ($i%2)?' color':''; ?>">
							<input type="checkbox" value="<?php echo $gext.'.'.$item; ?>" name="zip_group[]">
							<span  class="title"><?php echo _ucWords($item); ?></span>
							<a class="btn btn-info" href="<?php echo '?zipfile='.$gext.'.'.$item; ?>" title="<?php echo _ucWords($item); ?>" >Download</a>
						</li>
					
					<?php 
						
					} 
					?>
				</ul>
				<?php } 
				} ?>
				</div>
			</div>
			
			
		</form>
	</div>
</body>
</html>