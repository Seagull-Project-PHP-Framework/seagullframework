if (!document.all) onError=null
else window.onerror=handleError;
 window.onerror=function(){ return true;}
function handleError() {
 return true;
}
