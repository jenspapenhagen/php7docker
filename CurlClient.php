<?php
declare(strict_types=1);
class CurlClient {
	private $protocol;
	private $host;
	private $port;
	private $base_uri;


    /**
     * CurlClient constructor.
     * @param string $host
     * @param string $port
     * @param string $protocol
     */
    public function __construct(string $host, string $port, string $protocol = 'http') {
		$this->host = $host;
		$this->port = $port;
		$this->protocol = $protocol;
		$this->base_uri = $protocol . '://' . $this->host . ':' . $this->port;
            if(! ($this->validate_URL($this->base_uri)) ){
                die("URL is broken");
            }
		}

	/**
	 * validate_IP
	 * @param string $host
	 * @return bool
     */
	public function validate_IP(string $host): bool{
		if(!filter_var($host, FILTER_VALIDATE_IP) === false){
			return true;
		}else{
			die("IP not validated, please try again.");
		}
	}

    /**
     * validate_URL
     * @param string $url
     * @return bool
     */
    public function validate_URL(string $url): bool{
        if(!filter_var($url, FILTER_VALIDATE_URL) === false){
            return true;
        }else{
            die("IP not validated, please try again.");
        }
    }


    /**
     * curl GET
     * @param string $get
     * @param int $getStatus
     * @return mixed
     */
    public function get(string $get, $getStatus=0) {
		$ch = curl_init();
		$uri = $this->base_uri;
        $fullURL = $uri . $get;

		curl_setopt($ch,CURLOPT_URL, $fullURL);
		curl_setopt($ch,CURLOPT_HEADER,false);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_TIMEOUT,30);

        if($getStatus != 0){
            curl_exec($ch);
            $result = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return $result;
        }else{
            $result = curl_exec($ch);
        }
		
		curl_close($ch);

        if (!$result) {
            die("Connection Failure.n");
        }
	return json_decode($result, true);
	}


    /**
     * curl POST
     * @param string $post
     * @param mixed $parameter
     * @param int $getStatus
     * @return mixed
     */
    public function post(string $post, $parameter=NULL, $getStatus=0) {
	    $uri = $this->base_uri;
        $fullURL = $uri . $post;
        if(empty($parameter) or $parameter == NULL){
            $parameter = 0;
        }

		$ch = curl_init();

		curl_setopt($ch,CURLOPT_URL, $fullURL);
		curl_setopt($ch,CURLOPT_POST,sizeof($parameter));
		curl_setopt($ch,CURLOPT_HEADER,false);
		curl_setopt($ch,CURLOPT_FRESH_CONNECT,true);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);
		curl_setopt($ch,CURLOPT_FORBID_REUSE,true);
		curl_setopt($ch,CURLOPT_TIMEOUT,30);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$parameter);
		if($getStatus != 0){
            curl_exec($ch);
            $result = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return $result;
        }else{
            $result = curl_exec($ch);
        }

		curl_close($ch);

        if (!$result) {
            die("Connection Failure.n");
        }
		
	return json_decode($result, true);
	}

    /**
     * curl DELETE
     * @param $path
     * @param string $json
     * @return mixed
     */
    public function del(string $path, object $json) {
        $uri = $this->base_uri;
        $fullURL = $uri . $path;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullURL);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_TIMEOUT,30);
        $result = curl_exec($ch);

        curl_close($ch);

        if (!$result) {
            die("Connection Failure.n");
        }

        return json_decode($result, true);
    }

    /**
     * curl PUT
     * @param string $path
     * @param object $json
     * @return mixed
     */
    public function put(string $path, object $json) {
        $uri = $this->base_uri;
        $fullURL = $uri . $path;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullURL);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($json));
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_TIMEOUT,600); //have to be change to a higher number if remote timeout
        $result = curl_exec($ch);

        curl_close($ch);

        if (!$result) {
            die("Connection Failure.n");
        }

        return json_decode($result, true);
    }


    /**
     * check if the Server is running
     * @param string $path
     * @return mixed|string
     */
    public function ServerRunning(string $path="/"){
        $uri = $this->base_uri;
        $fullURL = $uri . $path;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullURL);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);

        curl_exec($ch);
        $result = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if(curl_errno($ch)) {
            $result .= '    [Informational 1xx]"
                            100=\"Continue\"
                            101=\"Switching Protocols\"
                            
                            [Successful 2xx]
                            200=\"OK\"
                            201=\"Created\"
                            202=\"Accepted\"
                            203=\"Non-Authoritative Information\"
                            204=\"No Content\"
                            205=\"Reset Content\"
                            206=\"Partial Content\"
                            
                            [Redirection 3xx]
                            300=\"Multiple Choices\"
                            301=\"Moved Permanently\"
                            302=\"Found\"
                            303=\"See Other\"
                            304=\"Not Modified\"
                            305=\"Use Proxy\"
                            306=\"(Unused)\"
                            307=\"Temporary Redirect\"
                            
                            [Client Error 4xx]
                            400=\"Bad Request\"
                            401=\"Unauthorized\"
                            402=\"Payment Required\"
                            403=\"Forbidden\"
                            404=\"Not Found\"
                            405=\"Method Not Allowed\"
                            406=\"Not Acceptable\"
                            407=\"Proxy Authentication Required\"
                            408=\"Request Timeout\"
                            409=\"Conflict\"
                            410=\"Gone\"
                            411=\"Length Required\"
                            412=\"Precondition Failed\"
                            413=\"Request Entity Too Large\"
                            414=\"Request-URI Too Long\"
                            415=\"Unsupported Media Type\"
                            416=\"Requested Range Not Satisfiable\"
                            417=\"Expectation Failed\"
                            
                            [Server Error 5xx]
                            500=\"Internal Server Error\"
                            501=\"Not Implemented\"
                            502=\"Bad Gateway\"
                            503=\"Service Unavailable\"
                            504=\"Gateway Timeout\"
                            505=\"HTTP Version Not Supported\" ';
            $result .= "\n\n".curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }

}
