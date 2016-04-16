<?php

class UserAgentInfo {
	function __construct(){
		$this->user_agent 	= $_SERVER['HTTP_USER_AGENT'];
	}

	function is_mobile() {
		$user_agent = $this->user_agent;
		return $this->userAgent($this->user_agent) == "mobile" ? true : false;
	}

	private function userAgent($ua){

		$iphone = strstr(strtolower($ua), 'mobile'); //Search for 'mobile' in user-agent (iPhone have that)
		$android = strstr(strtolower($ua), 'android'); //Search for 'android' in user-agent
		$windowsPhone = strstr(strtolower($ua), 'phone'); //Search for 'phone' in user-agent (Windows Phone uses that)
	
		$androidTablet = $this->androidTablet($ua); //Do androidTablet function
		$ipad = strstr(strtolower($ua), 'ipad'); //Search for iPad in user-agent

		if($androidTablet || $ipad){ //If it's a tablet (iPad / Android)
			return 'tablet';
		}elseif($iphone && !$ipad || $android && !$androidTablet || $windowsPhone){ //If it's a phone and NOT a tablet
			return 'mobile';
		}else{ //If it's not a mobile device
			return 'desktop';
		}
	}

	private function androidTablet($ua){ //Find out if it is a tablet
		if(strstr(strtolower($ua), 'android') ){//Search for android in user-agent
			if(!strstr(strtolower($ua), 'mobile')){ //If there is no ''mobile' in user-agent (Android have that on their phones, but not tablets)
				return true;
			}
		}
	}

	
}