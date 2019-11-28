<?php
declare(strict_types=1);
namespace ntt;

use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Utils\Config;

class Main extends PluginBase implements Listener{
	public $list = [];
	public $file = [];
	public $path;
	public $filePhar;
	public $imageType = ['jpeg', 'png', 'xpm', 'xbm', 'bmp', 'wbmp', 'webp'];

	public function onEnable(){
		$this->path = $this->getServer()->getDataPath();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->filePhar = $this->path . "ItB64E/";
		if(!file_exists($this->filePhar)){
			@mkdir($this->filePhar);
			$this->getLogger()->info("§aFolder §fItB64E §aGenerated!");
		}
		if(!file_exists($this->filePhar . "Image-Input/")){
			@mkdir($this->filePhar . "Image-Input/");
			$this->getLogger()->info("§aFolder §fImage-Input §aGenerated!");
		}
		if(!file_exists($this->filePhar . "Data-Output/")){
			@mkdir($this->filePhar . "Data-Output/");
			$this->getLogger()->info("§aFolder §fData-Output §aGenerated!");
		}
		$this->getLogger()->info("§aPlugin enabled!");
		$this->list = array_diff(@scandir($this->filePhar . "Image-Input/"), array('.', '..'));
    	foreach($this->list as $f){
    		$ff = strtolower(pathinfo($this->filePhar . "Image-Input/" . $f, PATHINFO_EXTENSION));
			$name = @str_replace("." . $ff, '', $f);
			$ImagePhar = $this->filePhar . "Image-Input/" . $f;
			if(in_array($ff, $this->imageType)){
				$data = $this->getDataFromImage($ImagePhar, $ff);
				$data_output = $this->makeData($data, $name, $ff);
				$this->getLogger()->info("§f" . $data_output . "§aGenerated!");
			}else{
				$this->getLogger()->info("§c$ff is not allowed image type");
			}
			@unlink($this->filePhar . "Image-Input/" . $f);
		}
	}
	
	public function getDataFromImage($file, $type){
		switch ($type){
	        case 'jpeg':
	            $im = @imagecreatefromjpeg($file);
	        break;
	        case 'png':
	            $im = @imagecreatefrompng($file);
	        break;
	        case 'xpm':
	            $im = @imagecreatefromxpm($file);
	        break;
	        case 'xbm':
	            $im = @imagecreatefromxbm($file);
	        break;
	        case 'bmp':
	            $im = @imagecreatefrombmp($file);
	        break;
	        case 'wbmp':
	            $im = @imagecreatefromwbmp($file);
	        break;
			case 'webp':
	            $im = @imagecreatefromwebp($file);
	        break;
    	}   
    	return $im;
    }
    
    public function makeData($data, $name, $type){
		$bytes = "";
		$m = (int)@getimagesize($this->filePhar . "Image-Input/" . $name . "." . $type)[0];
		$n = (int)@getimagesize($this->filePhar . "Image-Input/" . $name . "." . $type)[1];
		for($y = 0; $y < $n; $y++){
			for($x = 0; $x < $m; $x++){
				$colorat = @imagecolorat($data, $x, $y);
				$a = ((~((int)($colorat >> 24))) << 1) & 0xff;
				$r = ($colorat >> 16) & 0xff;
				$g = ($colorat >> 8) & 0xff;
				$b = $colorat & 0xff;
				$bytes .= chr($r) . chr($g) . chr($b) . chr($a);
			}
		}
		@file_put_contents($this->filePhar . "Data-Output/" . $name . ".txt" , @base64_encode($bytes));
		return $name . ".txt";
	}
}