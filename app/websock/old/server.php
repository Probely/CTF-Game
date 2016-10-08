<?php
// #!/php -q

// Run from command prompt > php demo.php
require_once("websocket.server.php");

/**
 * This demo resource handler will respond to all messages sent to /echo/ on the socketserver below
 *
 * All this handler does is echoing the responds to the user
 * @author Chris
 *
 */
class DemoEchoHandler extends WebSocketResourceHandler{
	public function onMessage(IWebSocketUser $user, IWebSocketMessage $msg){
		$this->say("[ECHO] {$msg->getData()}");

        $received = $msg->getData(); 

        $aRec = json_decode($received); 

        $this->say("[DEBUG_MSG] ".print_r($aRec, true)."");

        if(is_object($aRec) && isset($aRec->sekjdfSAEwelnfsdWT)) {
            $adminMessage = (string) $aRec->sekjdfSAEwelnfsdWT;
        } else {
            $adminMessage = false;
        }
        
        foreach($this->users as $userr) {
            if(!empty($adminMessage)) {
		        $this->send($userr, json_encode(array('reload' => true, 'msg' => $adminMessage)));
            } else {
		        $this->send($userr, json_encode(array('reload' => true)));
            }
        }
	}
	
	public function onAdminMessage(IWebSocketUser $user, stdClass $obj){
		$this->say("[DEMO] Admin TEST received!");
		
		$frame = WebSocketFrame::create(WebSocketOpcode::PongFrame);
		$this->server->sendFrame($user, $frame);
	}
}

/**
 * Demo socket server. Implements the basic eventlisteners and attaches a resource handler for /echo/ urls.
 * 
 * 
 * @author Chris
 *
 */
class DemoSocketServer extends WebSocketServer{
	protected $debug = true;
	
	public function getAdminKey(){
		return "superdupersecretkey";
	}
	
	public function __construct($address, $port){
		parent::__construct($address, $port);
		
		$this->addResourceHandler("echo", new DemoEchoHandler());
	}
	protected function onConnect(IWebSocketUser $user){
		$this->say("[DEMO] {$user->getId()} connected");
	}
	
	public function onMessage($user, IWebSocketMessage $msg){
		$this->say("[DEMO] {$user->getId()} says '{$msg->getData()}'");
	}
	
	protected function onDisconnect(IWebSocketUser $user){
		$this->say("[DEMO] {$user->getId()} disconnected");
	}
	
	protected function onAdminTest(IWebSocketUser $user){
		$this->say("[DEMO] Admin TEST received!");
		
		$frame = WebSocketFrame::create(WebSocketOpcode::PongFrame);
		$this->sendFrame($user, $frame);
	}
}

// Start server
//$server = new DemoSocketServer(0,12345);
$server = new DemoSocketServer('0.0.0.0',54321);
$server->run();
