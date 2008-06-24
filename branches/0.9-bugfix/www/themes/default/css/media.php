/*******************************************************/
/* MEDIA / FILE MANAGER                                */
/*******************************************************/
/*******************************************************/
/* NAVIGATION FOR MODULE                               */
/*******************************************************/

/* Main Module Navigation  */
    #module_navigation{
        margin:10px 0 25px 0;
        padding:5px;
        text-align:center;
        background-color:#f0ffd9;
        border:1px solid #c6d7aa;
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
       display: inline-block;
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

    .default_table{
        margin:0;
        padding:10px;
        background-color:#F3F3EC;
        border:1px solid #999791;
    }
    .default_table table{
        margin:0;
        padding:0;
        border:0;
        width:100%;
    }
    .default_table caption{
        color:#9CB20A;
        font-size:1em;
        text-align:left;
        margin:0 0 10px 0;
    }
    .default_table th{
        margin:0;
        padding:3px;
        text-align:left;
        color:#666666;
        font-weight:bold;
        border:0;
    }
    .default_table tr{
        margin:0;
        padding:0;
        text-align:left;
    }
    .default_table td{
        margin:0;
        padding:3px 10px 3px 3px ;
        border-left:0;
        border-right:0;
        border-top:1px solid #999791;
        border-bottom:6px solid #F3F3EC;
        background-color:#fff;
        color:#855F48;
    }
    .default_table a{
        color:#0066CC;
        text-decoration:underline;
    }
    .default_table a:hover{
        color:#CC3300;
    }
    td.icon_in_table{
        text-align:center;
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
        background-color:#f0ffd9;
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
        background-color:#f0ffd9;
        border:1px solid #D0DCE0;
        padding:10px;
    }