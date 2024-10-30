<?php
defined( 'ABSPATH' ) or exit;


class llama_visitor_details {
	public function visitor_data() {
		global $_SERVER;
		global $HTTP_SERVER_VARS;
		$data = new stdClass();
		//1) IP:
		$data->ip = $this->visitor_ip($_SERVER);
		//2) OS:
		$data->os = $this->visitor_os();
		//3) BROWSER:
		$data->browser = $this->visitor_browser();
		//4) HASH:
		$data->user_agent_hash = $this->visitor_hash($_SERVER);
		return $data;
	}
	public function visitor_ip($server) {
		$ip = '';
		if (isset($server['REMOTE_ADDR'])) {
			$ip = $server['REMOTE_ADDR'];
		}
		if (isset($server['HTTP_X_FORWARDED_FOR'])) {
			$ip = $server['HTTP_X_FORWARDED_FOR'];	
		}
		return $ip;
	}
	private function visitor_user_agent() {
		$user_agent = '';
		global $_SERVER;
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$user_agent	= $_SERVER['HTTP_USER_AGENT'];
		}
		else {
			global $HTTP_SERVER_VARS;	
			if (isset($HTTP_SERVER_VARS['HTTP_USER_AGENT'])) {
				$user_agent = $HTTP_SERVER_VARS['HTTP_USER_AGENT'];
			}
			else {
				global $HTTP_USER_AGENT;
				if (isset($HTTP_USER_AGENT)) {
					$user_agent = $HTTP_USER_AGENT;
				}
			}
		}
		return $user_agent;
	}
	public function visitor_hash($server) {
		$user_agent = $this->visitor_user_agent();
		return md5($user_agent);	
	}
	public function visitor_wp_role($mode = '') {
		$role = '';
		$dic = array(
			'administrator' => 'Administrator',
			'editor' => 'Editor',
			'author' => 'Author',
			'contributor' => 'Contributor',
			'subscriber' => 'Subscriber'
		);	
		if ($mode == 'return_dic') {
			return $dic;	
		}
	}
	public function visitor_os($mode = '') {
		$os = '';
		$dic = array(
			'Windows 10' => array('Windows NT 10\.[0-9]{1,2}'),
			'Windows XP' => array('Windows XP','(Windows NT 5.1|Windows NT5.1)'),
			'Windows 2000' => array('Windows 2000','Windows NT 5.0'),
			'Windows NT' => array('Windows NT 4.0|WinNT4.0'),
			'Windows Server 2003' => array('Windows NT 5.2'),
			'Windows Vista' => array('Windows NT 6.0'),
			'Windows 7' => array('Windows NT 7.0'),
			'Windows CE' => array('Windows CE','UP.Browser'),
			'Windows Media Center' => array('(media center pc).([0-9]{1,2}\.[0-9]{1,2})'),
			'Windows' => array('(win)([0-9]{1,2}\.[0-9x]{1,2})','(win)([0-9]{2})','(windows)([0-9x]{2})','(windows)([0-9]{1,2}\.[0-9]{1,2})','win32','GetRight','go!zilla','gozilla','gulliver','ia archiver','NetPositive','mass downloader','microsoft','offline explorer','teleport','web downloader','webcapture','webcollage','webcopier','webstripper','webzip','wget','flashget','MS FrontPage','(msproxy)\/([0-9]{1,2}.[0-9]{1,2})','(msie)([0-9]{1,2}.[0-9]{1,2})','NetAnts'),
			'Windows ME' => array('Windows ME','Win 9x 4.90'),
			'Windows 98' => array('Windows 98|Win98'),
			'Windows 95' => array('Windows 95'),
			'Java' => array('(java)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2})'),
			'Solaris' => array('(Solaris)([0-9]{1,2}\.[0-9x]{1,2}){0,1}'),
			'DOS' => array('dos x86'),
			'Unix' => array('unix','libwww-perl'),
			'Mac OS X' => array('Mac OS X'),
			'Macintosh PowerPC' => array('Mac_PowerPC'),
			'Mac OS' => array('(mac|Macintosh)'),
			'SunOS' => array('(sunos)([0-9]{1,2}\.[0-9]{1,2}){0,1}'),
			'BeOS' => array('(beos)([0-9]{1,2}\.[0-9]{1,2}){0,1}'),
			'RISC OS' => array('(risc os)([0-9]{1,2}\.[0-9]{1,2})'),
			'OS/2' => array('os\/2'),
			'FreeBSD' => array('freebsd'),
			'OpenBSD' => array('openbsd'),
			'NetBSD' => array('netbsd'),
			'IRIX' => array('irix'),
			'Plan9' => array('plan9'),
			'OSF' => array('osf'),
			'AIX' => array('aix'),
			'GNU Hurd' => array('GNU Hurd'),
			'Linux - Fedora' => array('(fedora)'),
			'Linux - Kubuntu' => array('(kubuntu)'),
			'Linux - Ubuntu' => array('(ubuntu)'),
			'Linux - Debian' => array('(debian)'),
			'Linux - CentOS' => array('(CentOS)'),
			'Linux - Mandriva' => array('(Mandriva).([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)'),
			'Linux - SUSE' => array('(SUSE).([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)'),
			'Linux - Slackware (Dropline GNOME)' => array('(Dropline)'),
			'Linux - ASPLinux' => array('(ASPLinux)'),
			'Linux - Red Hat' => array('(Red Hat)'),
			'Linux' => array('(linux)','([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3})'),
			'AmigaOS' => array('(amigaos)([0-9]{1,2}\.[0-9]{1,2})','amiga-aweb'),
			'Amiga' => array('amiga'),
			'PalmOS' => array('AvantGo'),
			'WebTV' => array('(webtv)\/([0-9]{1,2}\.[0-9]{1,2})'),
			'Dreamcast OS' => array('Dreamcast'),
		);
		if ($mode == 'return_dic') {
			return $dic;	
		}
		//1) define user_agent
		$user_agent = $this->visitor_user_agent();
		//2) find os:
		foreach($dic as $os_name => $os_patterns) {
			if (!empty($os_patterns)) {
				foreach($os_patterns as $pattern) {
					if (preg_match("/".$pattern."/i",$user_agent)) {
						$os = $os_name;
						return $os;
					}
				}
			}
		}
		return $os;
	}
	public function visitor_browser($mode = '') {
		$dic = array(
			'Internet Explorer' => array('/msie/i'),
			'Firefox' => array('/firefox/i'),
			'Chrome' => array('/chrome/i'),
			'Safari' => array('/safari/i'),
			'Edge' => array('/edge/i'),
			'Opera' => array('/opera/i'),
			'Netscape' => array('/netscape/i'),
			'Maxthon' => array('/maxthon/i'),
			'Konqueror' => array('/konqueror/i'),
			'Handheld Browser' => array('/mobile/i')
		);
		if ($mode == 'return_dic') {
			return $dic;	
		}
		$browser = '';
		$user_agent = $this->visitor_user_agent();
		foreach($dic as $browser_name => $browser_patterns) {
			if (!empty($browser_patterns)) {
				foreach($browser_patterns as $pattern) {
					if (preg_match($pattern,$user_agent)) {
						$browser = $browser_name;
						return $browser;
					}
				}	
			}
		}
		return $browser;
	}
}