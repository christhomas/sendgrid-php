<?php

class SendGrid
{
    const VERSION = '3.2.0';

    protected $namespace	= 'SendGrid';
	protected $headers		= array('Content-Type' => 'application/json');
    protected $client;

    protected $apiKey;
    protected $apiUser;
    protected $apiPass;
    protected $url;
    protected $endpoint;
    // NOTE:	seems pretty pointless, could just do self::VERSION
    //			or SendGrid::VERSION anywhere in the code instead of just $this->version
    protected $version    = self::VERSION;

    /**
     * 	Sendgrid options
     *
     * 	array: $options
     */
	protected $options;

	/**
	 * 	Guzzle Options
	 *
	 * 	array: $guzzle
	 */
	protected $guzzle;

	protected function getOptionList(&$option)
	{
		$list = array(
			"verify_ssl",
			"guzzle_exceptions",
			"proxy"
		);

		if(!in_array($option,$list)) return &$this->options;

		$map = array(
				"verify_ssl"		=> "verify",
				"guzzle_exceptions"	=> "exceptions"
		);

		//	optionally alter the option name here to set the right value in the guzzle options
		if(array_key_exists($option,$map)){
			$option = $map[$option];
		}

		return &$this->guzzle;
	}

   	public function setAPIKey($key)
   	{
   		$this->apiKey = $key;

   		$guzzleOption['request.options']['headers'] = array('Authorization' => 'Bearer ' . $this->apiKey);

   		//	calculate the authorization header based on this key
   	}

   	public function setLogin($username,$password)
   	{
   		$this->apiUser	= $username;
   		$this->apiPass	= $password;

   		//	calculate the authorization header based on this key
   	}

   	public function setOption($option,$value=null)
   	{
   		if($value === null){
   			if(!is_array($options)) $options = array();

   			foreach($options as $k=>$v){
   				$this->setOption($k,$v);
   			}
   		}

   		if(!is_string($option || !strlen($option)){
   			throw new InvalidArgumentException("key has to be a string",$option);
   		}

   		$data = $this->getOptionList($option);
   		$data[$option] = $value;
   	}

   	public function hasOption($option)
   	{
   		$args = func_get_args();

   		if(count($args) > 1){
			return !in_array(false,array_map(array($this,"hasOption"),$args));
   		}else if(is_string($option)){
   			//	We cannot combine these two lines of code, because "option" might be modified inside selectOptions
   			//	This happens so I can "re-map" the guzzle keys to what it requires transparently
   			$data = $this->getOptionList($option);

   			return array_key_exists($option,$data);
   		}

   		throw new InvalidArgumentException("option requested was not valid, neither string nor array",$option);
   	}

   	public function getOption($option,$default=null)
   	{
   		if(!is_string($option)){
   			throw new InvalidArgumentException("option requested was not valid, required to be a string",$option);
   		}

   		$data = $this->getOptionList($option);

   		return array_key_exists($option,$data)
   			? $data[$option]
   			: $default;
   	}

   	public function setSSLVerify($state=false)
   	{
   		$this->setOption("verify_ssl",!!$state);
   	}

   	public function setRaiseExceptions()
   	{
   		$state = func_get_args() + array(true,false);

		$this->setOption("sendgrid_exceptions",!!$state[0]);
		$this->setOption("guzzle_exceptions",!!$state[1]);
   	}

   	public function setProxy($proxy)
   	{
   		$this->setOption("proxy",$proxy);
   	}

   	public function setURL($url)
   	{
   		if(is_string($url) && strlen($url)){
   			$this->url = $url;
   		}else if($this->hasOption("url")){
   			$this->url = $this->options["url"];
   		}else if($this->hasOption("protocol","host")){
   			$pl = $this->options["protocol"];
   			$ho = $this->options["host"];
   			$pt = $this->hasOption("port") ? ":".$this->options["port"] : "";

   			$this->url = "$pl://{$ho}:$pt";
   		}
   	}

    public function __construct($options=array())
    {
    	$this->options = array();

    	//	Set some defaults before you import all the options
    	$this->setRaiseExceptions(true,false);
    	$this->setSSLVerify(true);

    	//	Now import the options
    	$this->setOption($options);

    	//	You can only set the url safely, after the options are imported
    	$this->setURL(null);

    	//	seems very specific to mark this endpoint apart from the others??
        $this->endpoint = isset($this->options['endpoint']) ? $this->options['endpoint'] : '/api/mail.send.json';
    }

    public function initialise()
    {
        $this->client = new \Guzzle\Http\Client(
        	$this->url,
        	array('request.options' => $this->guzzle)
        );

        $client->setUserAgent('sendgrid/' . $this->version . ';php');
    }

    /**
     * @return array The protected options array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Makes a post request to SendGrid to send an email
     * @param SendGrid\Email $email Email object built
     * @throws SendGrid\Exception if the response code is not 200
     * @return stdClass SendGrid response object
     */
    public function send(SendGrid\Email $email)
    {
        $form = $email->toWebFormat();

        // Using username password
        if ($this->apiUser !== null) {
            $form['api_user'] = $this->apiUser;
            $form['api_key']  = $this->apiKey;
        }

        $response = $this->postRequest($this->endpoint, $form);

        if ($response->code != 200 && $this->options['exceptions']) {
            throw new SendGrid\Exception($response->raw_body, $response->code);
        }

        return $response;
    }

    /**
     * Makes the actual HTTP request to SendGrid
     * @param $endpoint string endpoint to post to
     * @param $form array web ready version of SendGrid\Email
     * @return SendGrid\Response
     */
    public function postRequest($endpoint, $form)
    {
        $req = $this->client->post($endpoint, null, $form);

        $res = $req->send();

        $response = new SendGrid\Response($res->getStatusCode(), $res->getHeaders(), $res->getBody(true), $res->json());

        return $response;
    }

    public function getRequest($endpoint)
    {
    	// Using username password
    	if ($this->apiUser !== null) {
    		$auth['api_user'] = $this->apiUser;
    		$auth['api_key']  = $this->apiKey;
    	}else{
    		$auth = null;
    	}

    	$req = $this->client->get($endpoint,$auth);

    	$res = $req->send();

    	$response = new SendGrid\Response($res->getStatusCode(), $res->getHeaders(), $res->getBody(true), $res->json());

    	return $response;
    }

    public static function register_autoloader()
    {
        spl_autoload_register(array('SendGrid', 'autoloader'));
    }

    public static function autoloader($class)
    {
        // Check that the class starts with 'SendGrid'
        if ($class == 'SendGrid' || stripos($class, 'SendGrid\\') === 0) {
            $file = str_replace('\\', '/', $class);

            if (file_exists(dirname(__FILE__) . '/' . $file . '.php')) {
                require_once(dirname(__FILE__) . '/' . $file . '.php');
            }
        }
    }
}
