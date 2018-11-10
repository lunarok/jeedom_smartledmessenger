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
			if ($eqLogic->getConfiguration('manage')) {
				$options['message'] = date("H:i");
				if ($eqLogic->getConfiguration('addition') != '') {
					$options['message']+= cmd::cmdToValue($eqLogic->getConfiguration('addition'));
					$options['manage'] = 1;
				}
				$eqLogic->sendMessage($options);
			}
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
		$this->loadCmdFromConf('smartledmessenger');
	}

	public function sendMessage($_options = array()) {
		if ($_options['message'] == '') {
			return;
		}
		if (isset($_options['title'])) {
			$options = arg2array($_options['title']);
		}
		$intensity = (isset($options['intensity'])) ? $options['intensity'] : $this->getConfiguration('intensity'); // 0 à 15
		$speed = (isset($options['speed'])) ? $options['speed'] : $this->getConfiguration('speed'); // 10 à 50
		if ($_options['manage'] == 1) {
			$static = 0;
		} else {
			$static = (isset($options['static'])) ? $options['static'] : $this->getConfiguration('static'); // binaire
		}
		$url = 'http://' . $this->getConfiguration('addr') . '/?message=' . urlencode($_options['message']) . '&intensity=' . $intensity . '&speed=' . $speed . '&local=1&static=' . $static;
		$request_http = new com_http($url);
		$data = $request_http->exec(30);
		log::add('smartledmessenger', 'debug', 'Call : ' . $url);
	}

}

class smartledmessengerCmd extends cmd {
	public function execute($_options = array()) {
		$eqLogic = $this->getEqLogic();
		$eqLogic->sendMessage($_options);
	}
}
?>
