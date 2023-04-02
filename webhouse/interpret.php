<?php
require 'OpenGraph.class.php';
require 'VideoPresenter.class.php';
require 'JsonTable.class.php';

// go through text file line by line and see what is text and what url
function readlines($lines){
	foreach ($lines as $line){
		$maybeurl = trim(preg_replace('/\s\s+/', '', $line));
		//line is valid url
		if (filter_var($maybeurl, FILTER_VALIDATE_URL)) {
			$filters=array();
			if($_GET["whoisthere"]=="mynameisfunky") $filters[]="addform";
			echo interpret($maybeurl, $_GET["mode"], $filters);
		}
		else
		{
		//line is text
			//get rid of newlines
			$line = preg_replace('/\r?\n$/', '', $line);
			
			$line= htmlspecialchars($line);
			$line=preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="">$1</a>', $line);
			$line.="<br>";
			echo $line;
		}
	}
}

function interpret($link, $mode="og", $filters=Array()) {
	$return="";
	
	if($mode=="dry"){
		$return='<a href="'.$link.'" target="">'.$link.'</a><br>';
	}else{
	$context = stream_context_create( array(
		'ssl' => array(
			'verify_peer' => false,
			'verify_peer_name' => false,
		),
	));
	$headers = get_headers($link, 1, $context);
	// print_r($headers);
	$typelong = $headers["Content-Type"];
	if(is_array($typelong)){
		foreach($typelong as $typelong_this){
			$headers = explode(";",$typelong_this);
			$type = $headers[0];//get last
		}
	}else {$headers = explode(";",$typelong);
		$type = $headers[0];
	}
	// echo"type:".$typelong;
	$plain_image=false;
	if($type=="text/html"){
		$embed=VideoPresenter::returnvideo($link);
		$graph = OpenGraph::fetch($link);
		// print_r($graph);
		$title=$graph->title;
		$desc=$graph->description;
		$video=$graph->video;
		if(!$title && $video) $title="video post";
		$audio=$graph->audio;
		if(!$title && $audio) $title="audio post";
		$image=$graph->image;
		// print_r($graph);
		$jsonld=$graph->json_ld_data;
		$ldobject=json_decode($jsonld);
		
		//interpret json-ld
		if(!$desc && $ldobject->{'@type'}=="Article" && $ldobject->headline) $desc=$ldobject->headline;
		if(!$title) $title=$link;
		
	}elseif($type=="video/mp4"){
		$title=$link;
		$desc="";
		$video=$link;
		$image="";
		$audio="";
	}elseif($type=="audio/mpeg"){
		$title=$link;
		$desc="";
		$audio=$link;
		$audio=$link;
		$image="";
		$video="";
	}elseif($type=="image/jpeg" || $type=="image/png" || $type=="image/gif"){
		$title="";
		$desc="";
		$image=$link;
		$audio="";
		$video="";
		$plain_image=true;
	}elseif($type=="application/pdf"){
		$title=basename($link);
		$desc="";
		$image="";
		$audio="";
		$video="";
	}elseif($type=="text/plain"){
		$title="";
		$desc=nl2br(file_get_contents($link));
		$image="";
		$audio="";
		$video="";
		$addclass="unfoldable";
	}else{
		$image="";
		$title=$link;
	}
	
	if($plain_image){
		//add alt filename
		$return.='<div class="imgcont"><img src="'.$image.'" alt=""></div>';
	}else{
		$return.='<div class="centercontent"><div class="interpreted '.$addclass.'">';
		if(!$video && !$embed){
			$return.='
			<div class="og_image_cont"><a href="'.$link.'" target=""><img src="'.$image.'" alt="" & onerror="this.style.display=\'none\'"></a>
			</div>';
		}
		$return.='<a href="'.$link.'" target=""><h2>'.$title.'</h2></a>
		
		
		<p>'.$desc.'</p>';
		if($audio){
			$return.='<audio controls>
			  <source src="'.$audio.'" type="audio/ogg">
			</audio>';
		}elseif($video){
			$return.='<video poster="'.$image.'" controls>
			  <source src="'.$video.'" type="">
			</video>';
		}elseif($embed){
			$return.= $embed;
		}else{
		}
		$return.=JsonTable::jsonToDebug($jsonld);
		
	$return.='</div></div>';
	
	}
	if(in_array("addform",$filters)) $return.='
	<form>
		 <input type="mail" name="to" placeholder="your@friend.com">
		 <input type="hidden" name="jsonld" value="'.$jsonld.'">
		 <input type="hidden" name="url" value="'.$link.'">
		 <input type="submit" value="mail this">
	</form>';
	
	}
	return $return;
}