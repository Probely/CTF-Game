<html>
<head>

<script type="text/javascript" src="https://js.sapo.pt/SAPO/"></script>
<script type="text/javascript" src="https://js.sapo.pt/SAPO/Utility/Serialize/0.1/"></script>

<script type="text/javascript">
var host = 'ws://10.134.132.48:12345/echo'; 
var socket = false;
 
if(("WebSocket" in window)) {
    socket = new WebSocket(host);
}else if(("MozWebSocket" in window)) {
    socket = new MozWebSocket(host);
} else {
    alert('your browser does not support websockets... choose another on :]');
}

if(!socket) {
    alert('error');
}

socket.onopen = function() { console.log('connected'); };
socket.onmessage = function(m) { console.log('message received'); };
socket.onclose = function() { console.log('closed'); };

function sendMessage() {
    var elm = document.getElementById('msg');
    if(elm == null) {
        return false;
    }
    
    var msgToSend = elm.value;
    
    var oObj = {
        sekjdfSAEwelnfsdWT: msgToSend
    };
    
    var str = SAPO.Utility.Serialize.get(oObj);
    
    //alert('will send: '+str);
    
    socket.send(str);
}
</script>

</head>
<body>

<h1>Send Message</h1>
<hr />
<div>
<b>Send a message to all users:</b><br />
<textarea id="msg" cols="100" rows="5"></textarea><br />
<input type="button" value="Send" onclick="sendMessage(); return false;" />
</div>

</body>
</html>