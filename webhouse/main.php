<?php 
if($_GET["nav"]) $append.=$_GET["nav"]."/";
if($_GET["subnav"]) $append.=$_GET["subnav"]."/";
$protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
$fullpath=$protocol.$_SERVER['HTTP_HOST'].'/'.$append;

if ($handle = opendir('../'.$append)) {
	//get first txt file in dir
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
			$namearray=explode(".",$entry);
            
			if(!isset($_GET["index"])){
				if(end($namearray)=="txt") {
					$pagetitle = str_replace(".txt","",$entry);
					$linesfile = $entry;
					break;
				}
			}else{
				$lines[]=$fullpath.rawurlencode($entry);
			}
        }
    }
	if(!$lines) $lines=file("../".$append.$linesfile);
	
	//get first image in dir and make it thumbnail
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
			$namearray=explode(".",$entry);
            if(end($namearray)=="jpg" || end($namearray)=="png") {
				$ogimage = $entry;
				break;
			}
        }
    }
    closedir($handle);
}

?>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $pagetitle;?></title>
<link rel="stylesheet" href="/webhouse/css/monospace.css" />
<?php if($ogimage){
	echo '<meta property="og:image" content="'.$fullpath.$ogimage.'"/>';
}?>
</head>
<body>
<script
  src="https://code.jquery.com/jquery-3.4.1.min.js"
  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
  crossorigin="anonymous"></script>
<script>
	$(document).on("submit", "form", function(e){
		e.preventDefault();
		var field=$(this).find("input[name=to]");
		if(!field.is(":visible")){
			field.show();
		}
		else $.post("webhouse/mailurl.php", $(this).serialize(), function(data){
			alert(data);
		});
	});
	$(document).on("click",".detailstoggle",function(e){
		e.preventDefault();
		var details=$(this).closest(".interpreted").find(".json_ld_table");
		details.toggle();
		if(details.is(":visible")) $(this).text("hide details"); else $(this).text("show details");
	});
</script><?php if($_GET["mode"]=="dry") echo '<a class="versionswitch" href="?">view rich version</a>'; else echo'<a class="versionswitch" href="?mode=dry">view dry version</a>';?><?php


include("interpret.php");
readlines($lines);

?>
</body>