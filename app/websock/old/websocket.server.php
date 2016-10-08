<?php
require_once("websocket.functions.php");
require_once("websocket.exceptions.php");
require_once("websocket.users.php");
require_once("websocket.framing.php");
require_once("websocket.message.php");
require_once("websocket.resources.php");


/**
 * WebSocketServer
 *
 * @author Chris
 */
abstract class WebSocketServer{
	protected $master;
	protected $sockets = array();
	protected $users   = array();
	
	protected $debug = false;

	protected $purgeUserTimeOut = null;	
	
	/**
	 *
	 * Enter description here ...
	 * @var IWebSocketResourceHandler[]
	 */
	protected $resourceHandlers = array();

	/**
	 * Flash-policy-response for flashplayer/flashplugin
	 * @access protected
	 * @var string
	 */
	protected $FLASH_POLICY_FILE = "<cross-domain-policy><allow-access-from domain=\"*\" to-ports=\"*\" /></cross-domain-policy>\0";


	/**
	 * Handle incoming messages.
	 *
	 * Must be implemented by all extending classes
	 *
	 * @param IWebSocketUser $user The user which sended the message
	 * @param IWebSocketMessage $msg The message that was received (can be WebSocketMessage76 or WebSocketMessage)
	 */

	public function __construct($address,$port){
		error_reporting(E_ALL);
		set_time_limit(0);

		ob_implicit_flush();

		$this->FLASH_POLICY_FILE = str_replace('to-ports="*','to-ports="'.$port,$this->FLASH_POLICY_FILE);

		$this->master=socket_create(AF_INET, SOCK_STREAM, SOL_TCP)     or die("socket_create() failed");
		socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1)  or die("socket_option() failed");

		socket_bind($this->master, $address, $port)                    or die("socket_bind() failed");
		socket_listen($this->master,20)                                or die("socket_listen() failed");
		$this->sockets[] = $this->master;

		$this->say("PHP WebSocket Server");
		$this->say("========================================");
		$this->say("Server Started : ".date('Y-m-d H:i:s'));
		$this->say("Listening on   : ".$address.":".$port);
		$this->say("========================================");
	}

	/**
	 * Start the server
	 */
	public function run(){
		
		
		while(true){

			$this->debug("Blocking on socket_select()");
			// Retreive sockets which are 'Changed'
			$changed = $this->sockets;
			socket_select($changed,$write=NULL,$except=NULL,NULL);
			
			$this->debug("Socket selected");
			

			foreach($changed as $socket){

				if($socket==$this->master){
					$client=socket_accept($this->master);
					if($client<0){
						self::log('socket_accept() failed'); continue;
					}else $this->connect($client);

				}else{
					// TODO: only reads up to 2048 bytes ?
					$bytes = @socket_recv($socket, $buffer, 2048, 0);
					$user = $this->getUserBySocket($socket);

					if($bytes == 0) {

						if($user == null)
							$this->disconnect($socket);
						else $this->disconnectUser($user);

					} else if(strpos($buffer,'<policy-file-request/>') === 0){
						$this->say("FLASH_POLICY_REQUEST");
						socket_write($socket,$this->FLASH_POLICY_FILE,strlen($this->FLASH_POLICY_FILE));
						$this->disconnect($socket);
					} else{
						$this->processSocketRead($socket, $buffer);
					}
				}
			}
			
			$this->debug('Number of users connected: '.count($this->users));
			$this->purgeUsers();
		}
	}

	/**
	 * Handle socketread. Performs a handshake when requested and also handles incoming admin messages and client messages.
	 *
	 * @param resource $socket
	 * @param string $buffer
	 */
	protected function processSocketRead($socket, $buffer){
		$user = $this->getUserBySocket($socket);

		if(!$user->hasHandshaked())
			$this->dohandshake($user,$buffer);
		else $this->processFrame($user,$buffer);
	}

	/**
	 * Dispatch an admin message to the associated resource handler or to the servers prefixed onAdmin functions

	 * @param WebSocketAdminUser $user
	 * @param stdClass $obj
	 */
	protected function dispatchAdminMessage(IWebSocketUser $user, IWebSocketMessage $msg){
		$obj = json_decode($msg->getData());

		if($user->getResource() != null && array_key_exists($user->getResource(), $this->resourceHandlers)){
			$this->resourceHandlers[$user->getResource()]->onAdminMessage($user, $obj);
		} else call_user_func(array($this, 'onAdmin'.ucfirst($obj->task)), $user, $obj);
	}

	/**
	 * Shuts down the server on Admin's shutdown message
	 */
	protected function onAdminShutdown(){
		exit();
	}

	/**
	 *
	 * Enter description here ...
	 * @param WebSocketAdminUser $user
	 * @param stdClass $obj
	 * @return bool False to stop event
	 */
	protected function onAdminMessage(IWebSocketUser $user, stdClass $obj){
		return true;
	}

	/**
	 * Associate a request uri to a IWebSocketResourceHandler.
	 *
	 * @param string $script For example 'handler1' to capture request with URI '/handler1/'
	 * @param IWebSocketResourceHandler $handler Instance of a IWebSocketResourceHandler. This instance will receive the messages.
	 */
	public function addResourceHandler($script, IWebSocketResourceHandler $handler){
		$this->resourceHandlers[$script] = $handler;
		$handler->setServer($this);
	}

	/**
	 * Process an incoming frame
	 *
	 * Here it should differentiate between the old single frame protocol and the latest
	 * draft which supports multiple frames per message.
	 *
	 * Control frames will be delegated to WebSocket::processControlFrame(), where other frames
	 * are delegated to WebSocket::onMessageFrame();
	 *
	 * @param IWebSocketUser $user
	 * @param string $msg
	 */
	protected function processFrame(IWebSocketUser $user,$msg){
		try{
			if($user->getProtocolVersion() == WebSocketProtocolVersions::HIXIE_76){
				$msg = WebSocketMessage76::fromFrame(WebSocketFrame76::decode($msg));
				$user->addMessage($msg);
				$this->dispatchMessage($user, $msg);
			} else {
				// New Protocol
				$frame = WebSocketFrame::decode($msg);
	
				if(WebSocketOpcode::isControlFrame($frame->getType()))
					$this->processControlFrame($user, $frame);
				else $this->processMessageFrame($user, $frame);
				
			}
		} catch(Exception $e){
			$this->say("Exception [Cant decode frame] occured. This will disconnect the user!");
			$this->disconnectUser($user);
		}

	}

	/**
	 * Handle incoming control frames
	 *
	 * Sends Pong on Ping and closes the connection after a Close request.
	 *
	 * @param IWebSocketUser $user
	 * @param WebSocketFrame $frame
	 */
	protected function processControlFrame(IWebSocketUser $user, WebSocketFrame $frame){
		switch($frame->getType()){
			case WebSocketOpcode::CloseFrame:
				$frame = WebSocketFrame::create(WebSocketOpcode::CloseFrame);
				$this->sendFrame($user, $frame);

				$this->disconnectUser($user);
				break;
			case WebSocketOpcode::PingFrame:
				$frame = WebSocketFrame::create(WebSocketOpcode::PongFrame);
				$this->sendFrame($user, $frame);
				break;
		}
	}


	/**
	 * Process a Message Frame
	 *
	 * Appends or creates a new message and attaches it to the user sending it.
	 *
	 * When the last frame of a message is received, the message is sent for processing to the
	 * abstract WebSocket::onMessage() method.
	 *
	 * @param IWebSocketUser $user
	 * @param WebSocketFrame $frame
	 */
	protected function processMessageFrame(IWebSocketUser $user, WebSocketFrame $frame){
		if($user->getLastMessage() instanceof IWebSocketMessage && $user->getLastMessage()->isFinalised() == false)
			$user->getLastMessage()->takeFrame($frame);
		else $user->addMessage(WebSocketMessage::fromFrame($frame));

		if($user->getLastMessage() instanceof IWebSocketMessage && $user->getLastMessage()->isFinalised()){
			if($user->isAdmin())
				$this->dispatchAdminMessage($user, $user->getLastMessage());
			else $this->dispatchMessage($user, $user->getLastMessage());
		}
	}

	/**
	 * Dispatch incoming message to the associated resource and to the general onMessage event handler

	 * @param IWebSocketUser $user
	 * @param IWebSocketMessage $msg
	 */
	protected function dispatchMessage(IWebSocketUser $user,IWebSocketMessage $msg){
		if(array_key_exists($user->getResource(),$this->resourceHandlers)){
			$this->resourceHandlers[$user->getResource()]->onMessage($user, $user->getLastMessage());
		}

		$this->onMessage($user, $user->getLastMessage());
	}

	/**
	 * Send a single frame to a client
	 * @param IWebSocketUser $client
	 * @param WebSocketFrame $frame
	 */
	public function sendFrame(IWebSocketUser $client, IWebSocketFrame $frame){
		$msg = $frame->encode();
		if(@socket_write($client->getSocket(), $msg,strlen($msg)) === false)
			$this->disconnectUser($client);
	}

	/**
	 * Send a (text) message to the client

	 * @param IWebSocketUser $client
	 * @param string $str
	 */
	public function send(IWebSocketUser $client, $str){

		if($client->getProtocolVersion() ==  WebSocketProtocolVersions::HIXIE_76)
			$msg = WebSocketMessage76::create($str);
		else $msg = WebSocketMessage::create($str);

		// Sent all fragments
		foreach($msg->getFrames() as $frame){
			$this->sendFrame($client, $frame);
		}
	}

	/**
	 * Connect a new client. Creates a new IWebSocketUser object
	 *
	 * @param resource $socket Socket resource used by client to connect.
	 * @return IWebSocketUser User that just connected
	 */
	protected function connect($socket){

		try{
			$user = $this->createUser($socket);

			// Add to our list
			array_push($this->users,$user);
			array_push($this->sockets,$socket);

			return $user;
		} catch(Exception $e){
			$this->say($e);
		}
	}

	/**
	 * Disconnect a client by user object. Also removes the user from associated resource
	 *
	 * @param IWebSocketUser $user
	 */
	public function disconnectUser(IWebSocketUser $user){
		if(isset($this->resourceHandlers[$user->getResource()]))
			$this->resourceHandlers[$user->getResource()]->removeUser($user);


		$this->disconnect($user->getSocket());

		$this->onDisconnect($user);
	}

	/**
	 * Disconnect a client by socket
	 * Enter description here ...
	 * @param unknown_type $socket
	 */
	protected function disconnect($socket){
		// Remove user
		for($i=0; $i < count($this->users); $i++){
			if($this->users[$i]->getSocket() == $socket){
				array_splice($this->users,$i,1);
				break;
			}
		}

		// Remove Socket
		$index = array_search($socket,$this->sockets);
		if($index>=0){
			 array_splice($this->sockets,$index,1);
		}

		@socket_close($socket);
	}

	/**
	 * Handle the clients handshake. Supports both HIXIE (#76) and HYBIE (#10) handshakes
	 *
	 * @param IWebSocketUser $user
	 * @param string $buffer
	 */
	protected function dohandshake(IWebSocketUser $user,$buffer){
		/* We need to parse the headers for the handshake
		 * to determine which protocol is used
		 * to generate an appropriate response
		 * to route the user to the appropriate resource
		 */
		try{
			$headers = WebSocketFunctions::parseHeaders($buffer);
			$user->setHeaders($headers);

			if(isset($headers['Sec-Websocket-Key1'])) {
				$response = $this->hixieHandshake($headers, $buffer);
				$user->setProtocolVersion(WebSocketProtocolVersions::HIXIE_76);
			} else {
				$response = $this->hybieHandshake($headers, $buffer);
				$user->setProtocolVersion(WebSocketProtocolVersions::LATEST);
			}

			if(@socket_write($user->getSocket(),$response,strlen($response)) == false)
				throw new Exception();

			$user->setHandshaked();

			// Raise event
			$this->onConnect($user);

			// Add the user to the appropriate resource handler
			$this->addUserToResource($user, $headers);

			return true;
		} catch (Exception $e){
			$this->say("User {$user->getId()}: Handshake failed!");
			$this->disconnectUser($user);
			return false;
		}

	}



	/**
	 * Perform a HIXIE (#76) / HYBIE (#00) handshake
	 *
	 * @param string $headers
	 * @param string $buffer
	 */
	protected function hixieHandshake($headers, $buffer){
		// Last 8 bytes of the client's handshake are used for key calculation later
		$l8b = substr($buffer, -8);

		// Check for 2-key based handshake (Hixie protocol draft)
		$key1 = isset($headers['Sec-Websocket-Key1']) ? $headers['Sec-Websocket-Key1'] : null;
		$key2 = isset($headers['Sec-Websocket-Key2']) ? $headers['Sec-Websocket-Key2'] : null;

		// Origin checking (TODO)
		$origin = isset($headers['Origin']) ? $headers['Origin'] : null;
		$host = $headers['Host'];
		$location = $headers['GET'];

		// Build response
		$response  = "HTTP/1.1 101 WebSocket Protocol Handshake\r\n" .
                "Upgrade: WebSocket\r\n" .
                "Connection: Upgrade\r\n";

		// Build HIXIE response
		$response .= "Sec-WebSocket-Origin: $origin\r\n"."Sec-WebSocket-Location: ws://{$host}$location\r\n";
		$response .= "\r\n" . WebSocketFunctions::calcHixieResponse($key1,$key2,$l8b);


		return $response;

	}

	/**
	 * Perform a HYBIE (#10) handshake
	 *
	 * @param string $headers
	 * @param string $buffer
	 */
	protected function hybieHandshake($headers, $buffer){
		// Check for newer handshake
		$challenge = isset($headers['Sec-Websocket-Key']) ? $headers['Sec-Websocket-Key'] : null;

		// Build response
		$response  = "HTTP/1.1 101 WebSocket Protocol Handshake\r\n" .
                "Upgrade: WebSocket\r\n" .
                "Connection: Upgrade\r\n";

		// Build HYBI response
		$response .= "Sec-WebSocket-Accept: ".WebSocketFunctions::calcHybiResponse($challenge)."\r\n\r\n";

		return $response;
	}

	/**
	 * Adds a user to a IWebSocketResourceHandler by using the request uri in the GET request of
	 * the client's opening handshake
	 *
	 * @param IWebSocketUser $user
	 * @param array $headers
	 * @return IWebSocketResourceHandler Instance of the resource handler the user has been added to.
	 */
	protected function addUserToResource(IWebSocketUser $user, $headers){
		if(isset($headers['GET']) == false)
			return;

		$url = parse_url($headers['GET']);

		if(isset($url['query']))
			parse_str($url['query'], $query);
		else $query = array();

		if(isset($url['path']) == false)
			$url['path'] = '/';

		$resource = array_pop(preg_split("/\//",$url['path'],0,PREG_SPLIT_NO_EMPTY));
		$user->parameters = $query;


		if(array_key_exists($resource, $this->resourceHandlers)){
			$this->resourceHandlers[$resource]->addUser($user);
			$user->setResource($resource);

			$this->say("User has been added to $resource");
		}
	}


	/**
	 * Find the user associated with the socket
	 *
	 * @param socket $socket
	 * @return IWebSocketUser User associated with the socket, returns null when none found
	 */
	protected function getUserBySocket($socket){
		$found=null;
		foreach($this->users as $user){
			if($user->getSocket()==$socket){ $found=$user; break; }
		}
		return $found;
	}

	/**
	 * Output a line to stdout
	 *
	 * @param string $msg Message to output to the STDOUT
	 */
	public function say($msg = ""){
		echo date("Y-m-d H:i:s")." | ".$msg."\n";
	}

	// Events to be implemented by subclass
	protected function onConnect(IWebSocketUser $user){}
	protected function onMessage(IWebSocketUser $user, IWebSocketMessage $msg){}
	protected function onDisconnect(IWebSocketUser $user){}

	protected function createUser($socket){
		return new WebSocketUser($socket, $this);
	}

	abstract protected function getAdminKey();
	
	protected function purgeUsers(){
		$currentTime = time();
		
		if($this->purgeUserTimeOut == NULL)
			return;
			
		foreach($this->getUsers() as $u){
			if($currentTime - $u->getLastMessageTime() > $this->purgeUserTimeOut)
				$this->disconnectUser($u);
		}
	}

	public function getUsers(){
		return $this->users;
	}
	
	public function debug($msg){
		if($this->debug)
			echo date("Y-m-d H:i:s")." | ".$msg."\n";
	}
}


