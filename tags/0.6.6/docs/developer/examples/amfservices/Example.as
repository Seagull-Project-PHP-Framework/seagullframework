import mx.remoting.*; 
import mx.rpc.*; 

var gatewayUrl:String = "http://seagull/index.php/yourModule/"; 
service = new Service(gatewayUrl, null, "Example"); 
    
var pc:PendingCall = service.isMember();
pc.responder = new RelayResponder(this, "handleIsMember", null); 

function handleIsMember(re:ResultEvent) 
{ 
	if (re.result == true ) {
		trace("I am a member");
	}
	if (re.result == false ) {
		trace("I am not a member");
	}
} 
