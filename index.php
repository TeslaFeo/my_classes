<?php
// error_reporting(E_ALL);


class image_colors_proportions{

private $step = 8;

private $ranges;

//  F U N C T I O N S  ///////////////////////////////////////////////////////////////////

function __construct(){
	
	$this->ranges = $this->getRanges();
	
}

//////////////////////////////////////////////////////////////////////////////////////////

private function getRanges(){
$step = $this->step;

$count = 256;
$colors3D = [];

for ($r = 0;   $r <= $count;   $r = $r + $step){
	$colors2D = [];
	for ($g = 0;  $g <= $count;   $g = $g + $step){
		$colors1D = [];
		for ($b = 0;   $b <= $count;   $b = $b + $step){
			$colors1D[] = [$r, $g, $b];
		}
		$colors2D[] = $colors1D;
	}
	$colors3D[] = $colors2D;
}

$ranges3D = [];

foreach($colors3D as $r => $colors2D){
	$ranges2D = [];
	foreach($colors2D as $g => $colors1D){
		$ranges1D = [];
		foreach($colors1D as $b => $color){
			$rNext = $r + 1;
			$gNext = $g + 1;
			$bNext = $b + 1;
			
			if(!empty($colors3D[$rNext][$gNext][$bNext])){
				$tmp = [];
				
				$tmp[] = $colors3D[$rNext][$gNext][$bNext][0] - 1;
				$tmp[] = $colors3D[$rNext][$gNext][$bNext][1] - 1;
				$tmp[] = $colors3D[$rNext][$gNext][$bNext][2] - 1;
				
				$centerColor = [];
				
				$centerColor[] = $color[0] + $step / 2;
				$centerColor[] = $color[1] + $step / 2;
				$centerColor[] = $color[2] + $step / 2;
				
				$key1D = $color[2];
				$ranges1D[$key1D] = [$color, $tmp, $centerColor];
			}
		}
		$key2D = $color[1];
		if($key2D < 256){
			$ranges2D[$key2D] = $ranges1D;
		}
	}
	$key3D = $color[0];
	if($key3D < 256){
		$ranges3D[$key3D] = $ranges2D;
	}
}

return $ranges3D;
}

//////////////////////////////////////////////////////////////////////////////////////////

private function getImg($mime, $path){
	switch($mime){
		case 'image/png':
			return imagecreatefrompng($path);
		break;
		case 'image/jpeg':
			return imagecreatefromjpeg($path);
		break;
		case 'image/gif';
			return imagecreatefromgif($path);
		break;
	}
}

//////////////////////////////////////////////////////////////////////////////////////////

public function getProportions($path){

$ranges3D = $this->ranges;

$step = $this->step;

$sizeArr = getimagesize($path);

$img = $this->getImg($sizeArr['mime'], $path);
	
$width = $sizeArr[0];
$height = $sizeArr[1];

$pixelsCount = $width * $height;

$imgRanges = [];

for( $y = 0; $y < $height; $y++ ){
	for( $x = 0; $x < $width; $x++ ){
		$rgb = imagecolorat($img, $x, $y);
		$color = imagecolorsforindex($img, $rgb);
		
		foreach($ranges3D as $r => $ranges2D){
			if($color['red'] >= $r && $color['red'] < $r + $step){
				foreach($ranges2D as $g => $ranges1D){
					if($color['green'] >= $g && $color['green'] < $g + $step){
						foreach($ranges1D as $b => $range){
							if($color['blue'] >= $b && $color['blue'] < $b + $step){
								$k = $r.'_'.$g.'_'.$b;
								$imgRanges[$k] = (isset($imgRanges[$k])) ? $imgRanges[$k] + 1 : 1;
								break 3;
							}
						}
					}
				}
			}
		}
	}
}

arsort($imgRanges);

$proportions = [];

foreach($imgRanges as $k => $v){
	$keyRgb = explode('_', $k);
	
	$r = $keyRgb[0];
	$g = $keyRgb[1];
	$b = $keyRgb[2];
	
	$proportion['color'] = $ranges3D[$r][$g][$b][2];
	$proportion['percent'] = round($v / $pixelsCount * 100);
	
	if($proportion['percent'] > 0){
		$proportions[] = $proportion;
	} else {
		break;
	}
}
return $proportions;
}

//////////////////////////////////////////////////////////////////////////////////////////

public function viewColors(){

$ranges3D = $this->ranges;

echo '<div style="margin-top:5px;box-shadow:0 0 3px #000;min-height:10px;">';
foreach($ranges3D as $ranges2D){
	foreach($ranges2D as $ranges1D){
		foreach($ranges1D as $range){
			$color = $range[2];
			echo '<span style="display:inline-block;width:25px;height:25px;background-color:rgb('.$color[0].', '.$color[1].', '.$color[2].');"></span>';
			
			
		} echo '<br>';
	}echo '</div><div style="margin-top:5px;box-shadow:0 0 3px #000;min-height:10px;">';
}
echo '</div>';
}

}// class

?>