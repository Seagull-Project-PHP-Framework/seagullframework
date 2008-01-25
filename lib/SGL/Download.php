<?PHP
/* vim: set expandtab shiftwidth=4 softtabstop=4 tabstop=4: */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006, Pierpaolo Toniolo                                     |
// | All rights reserved.                                                      |
// |                                                                           |
// | Redistribution and use in source and binary forms, with or without        |
// | modification, are permitted provided that the following conditions        |
// | are met:                                                                  |
// |                                                                           |
// | o Redistributions of source code must retain the above copyright          |
// |   notice, this list of conditions and the following disclaimer.           |
// | o Redistributions in binary form must reproduce the above copyright       |
// |   notice, this list of conditions and the following disclaimer in the     |
// |   documentation and/or other materials provided with the distribution.    |
// | o The names of the authors may not be used to endorse or promote          |
// |   products derived from this software without specific prior written      |
// |   permission.                                                             |
// |                                                                           |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS       |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT         |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR     |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT      |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,     |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT          |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,     |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY     |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT       |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE     |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.      |
// |                                                                           |
// +---------------------------------------------------------------------------+
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | Download.php                                                              |
// +---------------------------------------------------------------------------+
// | Authors:   Pierpaolo Toniolo <sbaturzio@satellite1.191.it                 |
// +---------------------------------------------------------------------------+

require_once 'HTTP/Download.php';

/**
* Wrapper around PEAR HTTP/Download class to workaround some limits of
* that class.
*
* @package SGL
* @author Pierpaolo Toniolo
* @version $Revision: 1.4 $
*
*/
class SGL_Download extends HTTP_Download
{

    /**
    * set the Accept-Range HTTP header
    * typical values are 'none' or 'bytes'
    *
    * @access  public
    * @param   string   $par  the value for the HTTP header Accept-Ranges
    * @return  void
    */
    function setAcceptRanges($param)
    {
        if ($param == 'bytes') {
            $this->headers['Accept-Ranges'] = $param;
        } else {
            $this->headers['Accept-Ranges'] = 'none';
        }
    }

    /**
    * set the Content-Transfer-Encoding HTTP header
    *
    * @access  public
    * @param   string   $par  the value for the HTTP header Content-Transfer-Encoding
    * @return  void
    */
    function setContentTransferEncoding($param)
    {
        $this->headers['Content-Transfer-Encoding'] = $param;
        return true;
    }
}

?>
