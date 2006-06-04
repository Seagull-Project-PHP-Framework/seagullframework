/* 
Notes about this style sheet:

Some other styles are affecting this one so for the time being I have commented out the following:

core.php line:192


*/


/*******************************************************/
/* EVENTS HEADING									   */
/*******************************************************/
#event_module_header{
	font-size:1em;
	color:#98D318;
	margin:4px 0 10px 0;
	font-weight:normal;
}


/*******************************************************/
/* EVENTS NAVIGATION								   */
/*******************************************************/
#event_module_navigation{
		background-image:url("<?php echo $baseUrl; ?>/images/event/nav_bar_bg.png");
		background-position:top left;
		background-repeat:repeat-x;
		border-right:1px solid #B1B1B1;
		border-left:1px solid #B1B1B1;

}
	
#event_module_navigation ul{
	margin:0;
	padding:0;
	list-style-type:none;
}
#event_module_navigation li{
	margin:0 0 0 6px;
	padding:0;
	float:left;
	list-style-type:none;
}

#event_module_navigation a{

}
#event_module_navigation a:hover{
	cursor:pointer;
}
#event_module_navigation img{
	vertical-align:bottom;
}


/*******************************************************/
/* EVENT LIST TABLE									   */
/*******************************************************/
	#events_table{
		margin:0;
		padding:0;
		background-color:#F3F3EC;
		border:1px solid #999791;
	}
	#events_table table{
		margin:0;
		padding:0;
		border:0;
		width:100%;
	}
	#events_table caption{
		color:#9CB20A;
		font-size:1em;
		text-align:left;
		margin:0 0 10px 0;
	}
	#events_table th{
		margin:0;
		padding:3px;
		text-align:left;
		color:#666666;
		font-weight:bold;
		border:0;
	}
	#events_table tr{
		margin:0;
		padding:0;
		text-align:left;
	}
	#events_table td{
		margin:0;
		padding:3px 10px 3px 3px ;
		border-left:0;
		border-right:0;
		border-top:1px solid #999791;
		border-bottom:6px solid #F3F3EC;
		background-color:#FFFFFF;
		color:#92A60C;
	}
	#events_table a{
		color:#0066CC;
		text-decoration:underline;
	}
	#events_table a:hover{
		color:#CC3300;
		background-color:transparent;
	}
	td.icon_in_table{
		text-align:center;
	}
	.highlight{
		color:#336600;
	}
	.edit{
		color:#0066CC;
	}
	.delete{
		color:#CC0000;
	}
	.email{
		color:#FF6600;
	}
	.preview{
		color:#6C9B11;
	}
