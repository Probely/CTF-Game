<?php
interface IWebSocketResourceHandler{
	public function addUser(IWebSocketUser $user);
	public function removeUser(IWebSocketUser $user);
	public function onMessage(IWebSocketUser $user, IWebSocketMessage $msg);
	public function onAdminMessage(IWebSocketUser $user, stdClass $msg);
	
	public function setServer(WebSocketServer $server);
	
	public function getUsers();
}


abstract class WebSocketResourceHandler implements IWebSocketResourceHandler{
	
	/**
	 * 
	 * Enter description here ...
	 * @var SplObjectStorage
	 */
	protected $users;
	
	/**
	 * 
	 * Enter description here ...
	 * @var WebSocketServer
	 */
	protected $server;
	
	public function __construct(){
		$this->users = new SplObjectStorage();
	}
	
	public function addUser(IWebSocketUser $user){
		$this->users->attach($user);
	}
	
	public function removeUser(IWebSocketUser $user){
		$this->users->detach($user);
	}
	
	public function setServer(WebSocketServer $server){
		$this->server = $server;
	}
	
	public function say($msg =''){
		return $this->server->say($msg);
	}
	
	public function send(IWebSocketUser $client, $str){
		return $this->server->send($client, $str);
	}
	
	public function onMessage(IWebSocketUser $user, IWebSocketMessage $msg){}
	public function onAdminMessage(IWebSocketUser $user, stdClass $msg){}
	
	//abstract public function onMessage(WebSocketUser $user, IWebSocketMessage $msg);
	
	public function getUsers(){
		return $this->users;
	}
} 