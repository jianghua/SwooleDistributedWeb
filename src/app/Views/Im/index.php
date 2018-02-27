<!DOCTYPE html>  
<meta charset="utf-8" />  
<title>WebSocket Test</title>  
<script language="javascript"type="text/javascript">  
    var wsUri ="ws://192.168.44.129:8083"; 
    var output;  
    
    function init() { 
        output = document.getElementById("output"); 
        testWebSocket(); 
    }  
 
    function testWebSocket() { 
        websocket = new WebSocket(wsUri); 
        websocket.onopen = function(evt) { 
            onOpen(evt) 
        }; 
        websocket.onclose = function(evt) { 
            onClose(evt) 
        }; 
        websocket.onmessage = function(evt) { 
            onMessage(evt) 
        }; 
        websocket.onerror = function(evt) { 
            onError(evt) 
        }; 
    }  
 
    function onOpen(evt) { 
        writeToScreen("CONNECTED"); 
//        doSend("WebSocket rocks"); 
    }  
 
    function onClose(evt) { 
        writeToScreen("DISCONNECTED"); 
    }  
 
    function onMessage(evt) { 
		var data = evt.data;
		var jsn = JSON.parse(data);
		if (jsn && jsn.message)
		{
			data = jsn.message;
		}
        writeToScreen('<span style="color: blue;">RESPONSE: '+data+'</span>'); 
 //       websocket.close(); 
    }  
 
    function onError(evt) { 
        writeToScreen('<span style="color: red;">ERROR:</span> '+ evt.data); 
    }  
 
    function doSend(message) { 
        writeToScreen("SENT: " + message);  
        websocket.send('{"type": "message","message": "'+message+'"}'); 
    }  
 
    function writeToScreen(message) { 
        var pre = document.createElement("p"); 
        pre.style.wordWrap = "break-word"; 
        pre.innerHTML = message; 
        output.appendChild(pre); 
    }  

	function sendMsg(){
		var msg = document.getElementById('msg').value;
		if (msg)
		{
			doSend(msg);
		}
		document.getElementById('msg').value = '';
	}
 
    window.addEventListener("load", init, false);  
</script>  
<h2>WebSocket Test</h2>  
<div id="output"></div>  
<input type='text' id='msg'>
<input type="button" value='发布' onclick='sendMsg()'>
</html>