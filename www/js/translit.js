/*
* (c) WackoWiki team ( http://wackowiki.com/team/ ), 2003-2004
* (c) Pixel-Apes team ( http://pixel-apes.com/ ), 2004
* (c) JetStyle ( http://jetstyle.ru/ ), 2004
* Maintainers -- Roman Ivanov <thingol@mail.ru>,
*                Kuso Mendokusee <mendokusee@gmail.com>
* 
* Redistribution and use in source and binary forms, with or without
* modification, are permitted provided that the following conditions
* are met:
* 1. Redistributions of source code must retain the above copyright
*    notice, this list of conditions and the following disclaimer.
* 2. Redistributions in binary form must reproduce the above copyright
*    notice, this list of conditions and the following disclaimer in the
*    documentation and/or other materials provided with the distribution.
* 3. The name of the author may not be used to endorse or promote products
*    derived from this software without specific prior written permission.
* 
* THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
* IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
* OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
* IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
* INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
* NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
* DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
* THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
* (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
* THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

function Translit()
{
  this.enabled = true;
}

Translit.prototype.UrlTranslit = function( str, allow_slashes)
{
    var slash = "";
    if (allow_slashes) slash = "\\/";

    var LettersFrom = "абвгдезйиклмнопрстуфыщэйх";
    var LettersTo   = "aaaaaaceeeeiiiionooouuyzo";
    var Consonant = "";
    var Vowel = "";
    var BiLetters = {
    "ж" : "zh", "ц" : "ts",  "ч" : "ch",
    "ш" : "sh", "щ" : "sch", "ю" : "ju", "я" : "ja"
    };

    str = str.replace( /[_\.,?!\[\](){}]+/g, "_");
    str = str.replace( /-{2,}/g, "--");
    str = str.replace( /_\-+_/g, "--");
    str = str.replace( /['\s]+/g, "-");

    str = str.toLowerCase();

    //transliterating
    var _str = "";
    for (var x=0; x<str.length; x++) {
        if ((index = LettersFrom.indexOf(str.charAt(x))) > -1) {
            _str+=LettersTo.charAt(index);
        } else {
            _str+=str.charAt(x);
        }
    }
    str = _str;

    var _str = "";
    for (var x=0; x<str.length; x++) {
        if (BiLetters[str.charAt(x)]) {
            _str+=BiLetters[str.charAt(x)];
        } else {
            _str+=str.charAt(x);
        }
    }
    str = _str;

    str = str.replace( /j{2,}/g, "j");
    
    str = str.replace( new RegExp( "[^"+slash+"0-9a-z_\\-]+", "g"), "");
    
    return str;
}
