<?php
class VideoPresenter{
	private static function _getYoutubeIdFromUrl($url) {    
		$parts = parse_url($url);
		if(($parts["host"]=="m.youtube.com" || $parts["host"]=="youtube.com" || $parts["host"]=="www.youtube.com" || $parts["host"]=="youtu.be" || $parts["host"]=="www.youtu.be") && !strstr($url,"/c/") && !strstr($url,"/channel/") && !strstr($url,"/user/")){
		if(isset($parts['query'])){
			parse_str($parts['query'], $qs);
			if(isset($qs['v'])){
				return $qs['v'];
			}else if(isset($qs['vi'])){
				return $qs['vi'];
			}
		}
		if(($parts["host"]=="youtu.be" || $parts["host"]=="www.youtu.be") && isset($parts['path'])){
			$path = explode('/', trim($parts['path'], '/'));
			return $path[count($path)-1];
		}
		}
		if(strlen($url)==11 && (!strstr($url, "http://") && !strstr($url, "https://") && !strstr($url, "www.") && !strstr($url, "youtube") && !strstr($url, "www.") && !strstr($url, "youtu.be"))) return $url;
		return false;
	}

	private static function _validateFbVideoUrl($url){
		$parts = parse_url($url);
		if(($parts["host"]=="facebook.com" || $parts["host"]=="www.facebook.com" || $parts["host"]=="fb.me" || $parts["host"]=="fb.com") && !strstr($url,"/pg/")){
			return $url;
		}
		return false;
	}

	private static function _getVimeoId($url){
		$parts = parse_url($url);
		if($parts['host'] == 'www.vimeo.com' || $parts['host'] == 'vimeo.com'){
			$vidid=substr($parts['path'], 1);
			return $vidid;
		}
		return false;
	}

	private static function _getvidinfo($url){
		$getYT=self::_getYoutubeIdFromUrl($url);
		if($getYT){
			$result["type"]="yt";
			$result["string"]=$getYT;
			$result["img"]="https://img.youtube.com/vi/".$getYT."/mqdefault.jpg";
			return($result);
		// }else{
			// //fb video
			// $getFB=self::_validateFbVideoUrl($url);
			// if($getFB){
				// $result["type"]="fb";
				// $result["string"]=$getFB;
				// $result["img"]="https://www.prahazijehudbou.cz/wp-content/uploads/zahraj_si.png";
				// return($result);
			}else{
				global $vimeoid;
				$vimeoid=self::_getVimeoId($url);
				if($vimeoid){
					$hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$vimeoid.php"));
					$result["type"]="vim";
					$result["string"]=$vimeoid;
					$result["img"]=$hash[0]["thumbnail_large"];
					return($result);
				}
			// }
		}
		return false;
	}

	public static function returnvideo($url){
		if($url){
			$vidinfo=self::_getvidinfo($url);
			if($vidinfo){
				if($vidinfo["type"]=="yt"){
					$toreturn='<div>
					<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/'.$vidinfo["string"].'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
				}elseif($vidinfo["type"]=="fb"){
					$toreturn='
					<div>
					<iframe src="https://www.facebook.com/plugins/video.php?href='.$vidinfo["string"].'&show_text=0&width=560&height=315" width="560" height="315" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allowFullScreen="true"></iframe></div>';
				}elseif($vidinfo["type"]=="vim"){
					global $vimeoid;
					$toreturn='<iframe src="https://player.vimeo.com/video/'.$vimeoid.'" width="640" height="360" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
				}
			return $toreturn;
			} else return false;
		}
	}
}
?>