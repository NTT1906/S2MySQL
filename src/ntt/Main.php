<?php
declare(strict_types=1);
namespace foxel;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;

class Main extends PluginBase{
	
	public $mySQL_BASE = [
		'tip'=> "Don't edit confirm value, for port, set null for nothing",
		'confirm' => 'true', //U can edit from down here
	    'servername' => 'localhost',
		'username' => 'username',
		'password' => 'password',
		'dbname' => 'myDB',
		'port'=> null
	];
	public $mySQL_data;
	public $msql;
	
	
	
	public function onEnable(){
		if(!extension_loaded('mysqli')){
            throw new PluginException('§cMySQLi is not enabled!');
        }
		$this->mySQL_data = $this->getConfig();
		if($this->mySQL_data->get('config') !== true) $this->mySQL_data->setAll($this->mySQL_BASE);
		$this->mySQL_data->save();
		$this->getLogger()->info('§aenabled!');
	}
	
	public function sendData(){
		$this->msql = new \mysqli($this->mySQL_data->get('servername'), $this->mySQL_data->get('username'), $this->mySQL_data->get('password'), $this->mySQL_data->get('dbname'), $this->mySQL_data->get('port'));
		if($this->msql->connect_error){
			$this->getLogger()->info('§cConnection failed: ' . mysqli_connect_error());
			$this->setEnabled(false);
		}else{
			$this->getLogger()->info('§aConnection success!');
		}
		if($result = $this->msql->query("SHOW TABLES LIKE 'Data'")) {
    		if(!$result->num_rows == 1) {
				$this->msql->query('CREATE TABLE Data (count INT(6) NOT NULL)');
			}
		}
		$count = count($this->getServer()->getOnlinePlayers())+10;
		if($this->msql->query("INSERT into Data (count) VALUES ('$count')") === false){
			$this->getLogger()->info('§cError: ' . $this->msql->error);
		}
		$this->msql->close();
	}
	
	public function getData(){/*U can paste this function to the site, remember to edit this for the site*/
		$this->msql = new \mysqli($this->mySQL_data->get('servername'), $this->mySQL_data->get('username'), $this->mySQL_data->get('password'), $this->mySQL_data->get('dbname'), $this->mySQL_data->get('port'));
		if($this->msql->connect_error){
			$this->getLogger()->info('§cConnection failed: ' . mysqli_connect_error());
			$this->setEnabled(false);
		}else{
			$this->getLogger()->info('§aConnection success!');
		}
		$data=[];
		if($result = $this->msql->query('SELECT count FROM Data')){
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$data[]=$row['count'];
	    		}
			$this->getLogger()->info('§aData: ' . max($data));
	    	}else{
				$this->getLogger()->info('§aData: null');
			}
		}
		$this->msql->close();
	}
	
	public function onJoin(PlayerJoinEvent $event){
		$this->sendData();
	}
	public function onKick(PlayerKickEvent $event){
		$this->sendData();
	}
	public function onQuit(PlayerQuitEvent $event){
		$this->sendData();
	}
	
    public function onDisable(){
    	$this->getLogger()->info('§adisabled!');
    }
}