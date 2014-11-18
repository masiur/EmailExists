<?php

class EmailExists {

	private $_ip	 = '';
	private $_email  = '';
	private $_domain = '';
	private $_result = array();

	const IPV4 = FILTER_FLAG_IPV4;
	const IPV6 = FILTER_FLAG_IPV6;


	static public function check($email) {

		return new Self($email);
	}

	private function __construct($email) {
		if( $this->isEmail($email)) {

			$mxhosts = array();

			$this->_email  = $email;
			$this->_domain = end(explode('@', $email));

			if( $this->isIP($this->_domain)) {
				$this->_ip = $this->_domain;
			} else {
				getmxrr($this->_domain, $mxhosts);
			}


			$this->_connect($mxhosts);
		} else {
			$this->_result['invalid'] = 'Invalid Email Format';
			$this->_result['status']  = False;
		}
	}

	private function isEmail($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	private function isIP($ip, $type = False) {

		if( $type) {

			return filter_var($ip, FILTER_VALIDATE_IP, $type);
		}
		
		return filter_var($ip, FILTER_VALIDATE_IP);
	}

	private function isIPV4($ip) {
		return $this->isIP($ip, self::IPV4);
	}

	private function isIPV6($ip) {
		return $this->isIP($ip, self::IPV6);
	}

	private function _getRecords($mxhosts = array()) {
		if( !empty($mxhosts)) {
			$this->_ip = array_shift($mxhosts);

			return true;
		} else {

			$domain_record = NULL;

			if( $this->isIPV4($this->_ip))
				$domain_record = dns_get_record($this->_domain, DNS_A);

			if( $this->isIPV6($this->_ip))
				$domain_record = dns_get_record($this->_domain, DNS_AAAA);


			if( !empty($domain_record) ) {
				$this->_ip = $domain_record[0]['ip'];
			} else {
				$this->_result['invalid'] = 'No suitable MX records found.';
				$this->_result['status']  = False;
			}

			return true;
		}

		return false;
	}

	private function _connect($mxhosts) {
		if( $this->_getRecords($mxhosts)) {

			$details = '';
			$connect = @fsockopen( $this->_ip, 25);

			if( $connect) {

				$to = $this->_email;
				$ip = $this->_ip;

				if( preg_match("/^220/i", $out = fgets($connect, 1024))) {
					fputs($connect , "HELO $ip\r\n"); 
					$out = fgets ($connect, 1024);
					$details .= $out."\n";
		 
					fputs ($connect , "MAIL FROM: <$to>\r\n"); 
					$from = fgets ($connect, 1024); 
					$details .= $to."\n";

					fputs ($connect , "RCPT TO: <$to>\r\n"); 
					$to = fgets ($connect, 1024);
					$details .= $to."\n";

					fputs ($connect , "QUIT"); 
					fclose($connect);

					if(!preg_match("/^250/i", $from) || !preg_match("/^250/i", $to)) {
						$this->_result['invalid'] = 'Could not connect';
						$this->_result['status']  = False;
					} else {
						$this->_result['valid'] = 'Email exists';
						$this->_result['status']  = True;
					}
				}	
			} else {
				$this->_result['invalid'] = 'Could not connect to server';
				$this->_result['status']  = False;
			}
		}
	}

	public function failed() {
		return array_key_exists('invalid', $this->_result);
	}

	public function passed() {
		return ! $this->failed();
	}

	public function messages() {
		return json_decode($this->toJSON());
	}

	public function toJSON() {
		return json_encode($this->_result);
	}
}