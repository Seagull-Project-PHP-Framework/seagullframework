//  IE hack to simulate :hover on "li" tags
//  As IE only accept :hover css pseudo element on "a" tags, this hack automatically
//  adds a "sfhover" class name to all li tags.
/******************************************************************************************/
sfHover = function() {
	var sfEls = document.getElementsByTagName("li");
	for (var i=0; i<sfEls.length; i++) {
		sfEls[i].onmouseover=function() {
			this.className+=" sfhover";
		}
		sfEls[i].onmouseout=function() {
			this.className=this.className.replace(new RegExp(" sfhover\\b"), "");
		}
	}
}
if (window.attachEvent) window.attachEvent("onload", sfHover);
