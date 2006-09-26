
/************************************/
/*      CORE MODULE OVERWRITES      */
/************************************/
#ajaxIndicator {
    padding: 2px 4px;
    background: #ff3300;
    color: #fff;
    font-size: 0.9em;
}
dt {
    color: <?php echo $greyDark ?>;
}
dt .required {
    color: <?php echo $primaryDark ?>;
}
dt .required:after {
    content: " *";
    color: #ff0000;
}
dt label {
    padding-right: 20px;
}
dt.onSide, dl.onSide dt {
    float: left;
    width: 110px;
    padding-right: 0;
    text-align: left;
}
dd.onSide, dl.onSide dd {
    margin-left: 110px;
    margin-bottom: 0.5em;
}
dt.onTop, dl.onTop dt {
    margin-bottom: 4px;
    font-size: 0.9em;
    font-weight: bold;
}
dd.onTop, dl.onTop dd {
    margin-left: 0;
    margin-bottom: 0.7em;
}
input[type="text"], input[type="password"] {
    font-size: 1em;
}
input.mediumButton {
    width: 8em;
}
.manager-actions {
    padding: 4px 2px;
    /*background: <?php echo $primaryLight ?>;
    border: 1px solid <?php echo $primaryDark ?>;*/
}
.sgl-button {
    padding: 2px;
    background: <?php echo $greyLight ?>;
    border: 1px solid <?php echo $greyDark ?>;
    font-size: 0.9em;
}
.cmsBox {
    margin: 0.5em 0 1em;
    padding: 8px 8px 4px;
}
.boxAlt1 { /*light grey tones*/
    background: #F3F3EC;
    border: 1px solid #999791;
}
.boxAlt2 { /*light green tones*/
    background: #F0FFD9;
    border: 1px solid #C6D7AA;
}
#content h1 {
    padding: 0;
    font-size: 1.4em;
    color: <?php echo $greyDark ?>;
    border-bottom: none;
}
.moduleContainer .extraBar {
    margin-bottom: 10px;
    padding: 5px;
    background: #F0FFD9;
    border: 1px solid #C6D7AA;
}
#content .moduleContainer .extraBar h2 {
    margin: 0 0 1em;
    color: #666;
}
#content .moduleContainer .extraBar dt {
    color: #999999;
}
fieldset {
    padding-top: 0;
}
/*******************************************************/
/* MEDIA / FILE MANAGER                                 */
/*******************************************************/

/*******************************************************/
/* NAVIGATION FOR MODULE                               */
/*******************************************************/

/* Main Module Navigation  */
    #module_navigation {
        margin:10px 0 25px 0;
        padding:5px;
        text-align:center;
        background-color: #F0FFD9;
        border:1px solid #C6D7AA;
        height: 75px;
    }

    #module_navigation li{
        display: inline;
        list-style-type: none;
        float:left;
        margin:0 16px 0 6px;
    }

    #module_navigation span{
        margin:8px 0 0 0;
        display:block;
    }


    /* Hides from IE Mac \*/
    * html #module_navigation {height: 1%;}
    #event_navigation{display:block;}
    /* End Hack */

/*******************************************************/
/* SELECT FILE TYPE - NARROW RESULTS                   */
/*******************************************************/

    #view_type{
        margin:10px 0 25px 0;
        padding:10px;
        background-color:#E0EFB8;
        border:1px solid #C3CEA5;
        height: 75px;
    }
    #view_type img{
        margin:auto 0 -8px 0;
    }
    #view_type a{
        font-size:0.9em;
        color:#333333;
        text-decoration:none;
    }
    #view_type a:hover{
        color:#0066CC;
    }
    #view_type:after{
        content: ".";
        display: block;
        height: 0;
        clear: both;
        visibility:hidden;
    }
    #view_type{
       display: block;
    }
    /* Hides from IE Mac */
    * html #view_type {
       height: 1%;
    }
    #view_type{
       display:block;
    }
    /* End Hack */


    #view_type span{
        float:left;
        color:#5F7032;
        font-size:1em;
        font-weight:bold;
        margin:12px 10px 0 0;
    }
    #view_type input{
        margin:0;
        padding:0;
        border:1px solid #C3CEA5;
    }
    .doc_icon{
        width:70px;
        text-align:center;
        margin:0 0 8px 0;
    }
    .doc_name{
        text-align:center;
        color:#5F7032;
        font-size:0.9em;
    }
    .file_type_icon{
        float:left;
    }

    #keyword_div{
        float:left;
        margin:12px 10px 0 0;
    }
    #keyword_div label{
        text-align:center;
        color:#5F7032;
        font-size:0.9em;
        font-weight:bold;
    }
    #submit_div{
        float:left;
        margin:12px 10px 0 0;

    }


/*******************************************************/
/* THUMBNAIL CREATION                                  */
/*******************************************************/
.complete_thumb{
    float:left;
    margin:0;
    padding:10px 0 10px 0;
}
.complete_thumb_highlighted{
    float:left;
    margin:0 10px 10px 0;
    padding:14px 10px 4px 10px;
    background-color:#F6F5F2;
    border:1px solid #D9D8CB;
    height:100px;
    width:20%;
}
div.complete_thumb_highlighted input{
    margin:0 0 4px 0;
    padding:0;
    border:1px solid #C3CEA5;
}
.thumb img{
    border:1px solid #666666;
    margin:0;
    padding:1px;
    background-color:#FFFFFF;
    vertical-align:bottom;
}
div.thumb{
    float:left;
    border-bottom:1px solid #E5E5E5;
    border-right:1px solid #E5E5E5;
    background-color:#CCCCCC;
    margin:0;
    padding:0 2px 2px 0;
}
.thumb img:hover{
    border:1px solid #000000;
}

.thumb_title{
    margin:0;
    padding:8px 0 0 0;
    clear:both;
    float:left;
}

.thumb_title h1{
    margin:0;
    padding:0 0 0 0;
    color:#333333;
    font-weight:bold;
    font-size:1em;
}
.thumb_title h2{
    margin:0;
    padding:0 0 8px 0;
    color:#666666;
    font-weight:normal;
    font-size:1em;
}
.thumb_title h3{
    margin:0;
    padding:0;
    color:#666666;
    font-weight:normal;
    font-size:1em;
}
.thumb_title a{
    text-decoration:none;
    color:#666666;
}
.thumb_title a:hover{
    text-decoration:underline;
}
img.overlap{
    margin:-40px 0 0 0;
    display:block;
    position:relative;
    top:42px;
    left:2px;
    z-index:3;
}
div.cb{
    clear:both;
}
div.selection_options{
    float:left;
    margin:0 0 0 10px;
}
div.selection_options > input{
    margin:0;
    padding:0;
}
div.selection_options > label{
    color:#666666;
    font-size:0.9em;
    font-weight:bold;
}


/*******************************************************/
/* MEDIA LIST                                          */
/*******************************************************/

#mediaFilter-combox dl {

}
#mediaFilter-combox dt {
    float: left;
}
#mediaFilter-combox dd {
    display: inline;
}

/*******************************************************/
/* MEDIA ADD                                          */
/*******************************************************/
    #add_edit_form form{
        margin:0;
        padding:10px;
    }
    #add_edit_form label{
        font-weight:bold;
        color:#333333;
        font-size:0.9em;
        margin:0;
        padding:0;
        background-color:<?php echo $primaryLight ?>;
        background-image:url("<?php echo $baseUrl; ?>/images/cal_event_bg_2.jpg");
        background-position:top left;
        background-repeat:repeat-x;
        padding:4px;
        border:1px solid #999999;
        border-top:1px solid #fff;
        border-left:1px solid #fff;
    }
    #add_edit_form textarea{
        margin:0;
        padding:0;
    }
    #add_edit_form input, select{
        border:1px solid #999999;
        padding:2px;
        font-size:0.9em;
        color:#333333;
    }
    .form_section{
        margin:10px 0 0 0;
        background-color:<?php echo $primaryLight ?>;
        border:1px solid #D0DCE0;
        padding:10px;
    }