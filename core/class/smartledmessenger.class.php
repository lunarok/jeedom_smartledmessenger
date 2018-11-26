<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class smartledmessenger extends eqLogic {

	public static function cron() {
		$eqLogics = eqLogic::byType('smartledmessenger', true);
		foreach ($eqLogics as $eqLogic) {
			$eqLogic->refresh();
		}
	}

	public function loadCmdFromConf($type) {
		if (!is_file(dirname(__FILE__) . '/../config/devices/' . $type . '.json')) {
			return;
		}
		$content = file_get_contents(dirname(__FILE__) . '/../config/devices/' . $type . '.json');
		if (!is_json($content)) {
			return;
		}
		$device = json_decode($content, true);
		if (!is_array($device) || !isset($device['commands'])) {
			return true;
		}
		foreach ($device['commands'] as $command) {
			$cmd = null;
			foreach ($this->getCmd() as $liste_cmd) {
				if ((isset($command['logicalId']) && $liste_cmd->getLogicalId() == $command['logicalId'])
				|| (isset($command['name']) && $liste_cmd->getName() == $command['name'])) {
					$cmd = $liste_cmd;
					break;
				}
			}
			if ($cmd == null || !is_object($cmd)) {
				$cmd = new smartledmessengerCmd();
				$cmd->setEqLogic_id($this->getId());
				utils::a2o($cmd, $command);
				$cmd->save();
			}
		}
	}

	public function postAjax() {
		$this->loadCmdFromConf($this->getConfiguration('type','smartledmessenger'));
	}

	public function refresh() {
			$messActive = $this->getConfiguration('messActive',0);
			if (intval($messActive) > 0) {
				$messActive = $messActive -1;
				$this->setConfiguration('messActive', $messActive);
				$this->save();
			}
			if ($messActive == 0) {
				if ($this->getConfiguration('manage') == 1) {
					$options['message'] = scenarioExpression::setTags($this->getConfiguration('addition'));
					$this->sendMessage($options);
				} else {
					if ($this->getConfiguration('type') == 'smartledmessenger') {
						$url = 'http://' . $this->getConfiguration('addr') . '/?local=0';
						$request_http = new com_http($url);
						$request_http->exec(30);
					}
				}
			}
			if ($this->getConfiguration('type') == 'notifheure') {
				$this->getNotifHeure();
			}
	}

	public function sendMessage($_options = array()) {
		if ($_options['message'] == '') {
			return;
		}
		log::add('smartledmessenger', 'debug', 'Message : ' . $_options['message']);
		$options = array();
		if (isset($_options['title'])) {
			$options = arg2array($_options['title']);
		}
		if ($this->getConfiguration('type') == 'smartledmessenger') {
			$this->sendSmartLedMessenger($_options, $options);
		}
		if ($this->getConfiguration('type') == 'notifheure') {
			$this->sendNotifHeure($_options, $options);
		}
		if (isset($options['time']) && is_int($options['time']) && ($options['time'] > 0))	{
			log::add('smartledmessenger', 'debug', 'Time set : ' . $_options['time']);
			$this->setConfiguration('messActive',$options['time']);
			$this->save();
		}
	}

	public function sendSmartLedMessenger($_message = array(), $_options = array()) {
		$intensity = (isset($_options['intensity'])) ? $_options['intensity'] : $this->getConfiguration('intensity'); // 0 à 15
		$speed = (isset($_options['speed'])) ? $_options['speed'] : $this->getConfiguration('speed'); // 10 à 50
		$static = (strlen($_message['message']) > 5) ? 0 : 1;
		$url = 'http://' . $this->getConfiguration('addr') . '/?message=' . urlencode($_message['message']) . '&intensity=' . $intensity . '&speed=' . $speed . '&local=1&static=' . $static;
		$request_http = new com_http($url);
		$request_http->exec(30);
		log::add('smartledmessenger', 'debug', 'Call : ' . $url);
	}

	public function sendNotifHeure($_message = array(), $_options = array()) {
		$intensity = (isset($_options['lum'])) ? $_options['lum'] : $this->getConfiguration('intensity'); // 0 à 15
		$type = (isset($_options['type'])) ? $_options['type'] : $this->getConfiguration('effect'); // 0 à 15
		$txt = (isset($_options['txt'])) ? $_options['txt'] : $this->getConfiguration('txt'); // 0 à 15
		$flash = (isset($_options['flash'])) ? $_options['flash'] : $this->getConfiguration('flash'); // binary
		$url = 'http://' . $this->getConfiguration('addr') . '/Notification?msg=' . urlencode(iconv("UTF-8", "CP1252",$_message['message'])) . '&lum=' . $intensity . '&type=' . $type . '&txt=' . $txt . '&flash=' . $flash;
		$request_http = new com_http($url);
		$request_http->exec(30);
		log::add('smartledmessenger', 'debug', 'Call : ' . $url);
	}

	public function getNotifHeure() {
		$url = 'http://' . $this->getConfiguration('addr') . '/getInfo';
		$request_http = new com_http($url);
		$data = $request_http->exec(30);
		$data = json_decode($data);
		log::add('smartledmessenger', 'debug', 'Infos : ' . print_r($data , true));
	}

	public function sendConfiguration($_options = array()) {
		if ($_options['message'] != '') { $this->setConfiguration('addition',$_options['message']); }
		if (isset($_options['title'])) {
			$options = arg2array($_options['title']);
		}
		if (isset($options['intensity'])) { $this->setConfiguration('intensity',$options['intensity']);} // 0 à 15
		if (isset($options['speed'])) { $this->setConfiguration('speed',$options['speed']);} // 10 à 50
		if (isset($options['static'])) { $this->setConfiguration('static',$options['static']);} // binaire
		if (isset($options['manage'])) { $this->setConfiguration('manage',$options['manage']);} // binaire
		$this->save();
	}
}

class smartledmessengerCmd extends cmd {
	public function execute($_options = array()) {
		$eqLogic = $this->getEqLogic();
		switch ($this->getLogicalId()) {
			case 'message:options':
			$eqLogic->sendMessage($_options);
			break;
			case 'message:settings':
			$eqLogic->sendConfiguration($_options);
			break;
			case 'message:refresh':
			$eqLogic->refresh();
			break;
		}
	}
}
?>
