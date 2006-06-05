
/*******************************************************/
/* EVENTS ONLY CSS  								   */
/*******************************************************/


/*******************************************************/
/* CALENDAR STYLE > MONTH VIEW						   */
/*******************************************************/

	#calendar_area{
		background-color:#F6F5F2;
		padding:10px;
		margin:0;
	}
	#calendar_title{
		font-size:1.2em;
		font-weight:normal;
		color:#333333;
		margin:0 20px 0 20px;
		padding:0;
		vertical-align:baseline;
	}
	#top_nav{
		margin:0 auto 0 auto;
		text-align:center;
	}
	#top_nav img{
		margin:0 0 -6px 0;
	}
	#all_calendars{
		margin:10px 0 10px 0;
	}
	#all_calendars:after{
		content: ".";
		display: block;
		height: 0;
		clear: both;
		visibility:hidden;
	}
	#all_calendars{display: inline-block;}
	/* Hides from IE Mac \*/
	* html #all_calendars {height: 1%;}
	#all_calendars{display:block;}
	/* End Hack */

	.a_calendar{
		float:left;
		margin:0 0 0 0;
	}

/*  Calendars   */
	#all_calendars table{
		width:700px;
		background-color:#FFFFFF;
		padding:1px;
		border:1px solid #CCCCCC;
		margin:0 auto 0 0;
	}
	#all_calendars caption{
		color:#333333;
		font-size:0.9em;
		font-weight:bold;
	}
	#all_calendars table th{
		text-align: center;
		font-size: 1em;
		color:#067CA0;
		background:#666666;
		border-bottom: 1px solid #CCCCCC;
		border-right: 1px solid #CCCCCC;
		border-top: 1px solid #FFFFFF;
		border-left: 1px solid #FFFFFF;
		width: 14%;
		font-weight:normal;
		background-image:url("<?php echo $baseUrl; ?>/images/cal_days_bg.jpg");
		background-position:top left;
		background-repeat:repeat-x;
		padding:4px;
	}
	#all_calendars table td{
		height:100px;
		vertical-align:top;
		text-align:left;
		padding:4px;
	}
	td.day{
		font-size: 0.9em;
		font-weight: bold;
		background:#EFF9FC;
		border:1px solid #A1D8E9;
	}

	td.other_month {
		font-size: 0.9em;
		font-weight: bold;
		background:#FFFBE7;

	}
	td.selected {
		font-size: 0.9em;
		font-weight: bold;
		background: #85C7DC;
		color:#FFFFFF
	}
	td.today {
		font-size: 0.9em;
		font-weight: bold;
		background: #EFDC35;
	}
	td a{
		color:#333333;
		padding:2px;
		text-decoration:none;
	}
	td a:hover{
		background-color:#0B9AC6;
		color:#FFFFFF;
	}
	div.event_drop_shadow{
		padding:0 1px 1px 0;
		background-color:#999999;
		border:1px solid #BCD41B;
	}
	div.eventDiv{
		background-color:#AEEF4F;
		background-image:url("<?php echo $baseUrl; ?>/images/cal_event_bg_1.jpg");
		background-position:top left;
		background-repeat:repeat-x;
		padding:4px;
		border:1px solid #E9FACF;
	}
	div.eventDiv a{
		color:#3A3A3A;
		padding:2px;
	}
	div.eventDiv a:hover{
		color:#FFFFFF;
		background-color:transparent;
	}


/*******************************************************/
/* EACH EVENT TYPE HAS ITS OWN HIGHLIGHT COLOUR		   */
/*******************************************************/
	td.event{
		font-size: 0.9em;
		font-weight: bold;
		background:#D1CDBC;
	}



/*******************************************************/
/* CALENDAR STYLE > DAY VIEW						   */
/*******************************************************/
	#dv_calendar{
		padding:10px;

	}

	div.dv_events{
		float:left;
	}
	#dv_calendar table{
		background-color:#FFFFFF;
		border:1px solid #CCCCCC;
		padding:1px;
		width:700px;
	}
	#dv_calendar tr{
		margin:0;
		padding:0;
	}
	#dv_calendar th{
		background-color:#FFFF99;
		padding:8px;
		width:60px;
		border-top:1px dashed #CCCCCC;
	}
	#dv_calendar td{
		padding:8px;
		height:20px;
		margin:1px 0 1px 0;
		border-top:1px dashed #CCCCCC;
	}

	td.dv_event_block{
		margin:0;
		padding:0;
		width:100px;

		background-image:url("<?php echo $baseUrl; ?>/images/event_dv_event_bg.png");
		background-position:top left;
		background-repeat:repeat;
	}
	td.dv_event_block a{
		color:#3A3A3A;
		padding:2px;
		font-weight:bold;
		font-size:0.9em;
	}
	td.dv_event_block a:hover{
		color:#FFFFFF;
		background-color:transparent;
	}
/*******************************************************/
/* VIEW EVENT		  								   */
/*******************************************************/
	#an_event{
		background-color:#F6F5F2;
		padding:10px;
		margin:0;
		border:0;
	}
	#an_event:after{
		content: ".";
		display: block;
		height: 0;
		clear: both;
		visibility:hidden;
	}
	#an_event{display: inline-block;}
	/* Hides from IE Mac \*/
	* html #an_event {height: 1%;}
	#an_event{display:block;}
	/* End Hack */

	div.left_col{
		float:left;
		width:290px;
		padding:0 10px 0 0;
	}
	div.right_col{
		float:left;

	}

	#event_details{
		padding:12px 6px 6px 6px;
		background-image:url("<?php echo $baseUrl; ?>/images/event_view_desc_bg.jpg");
		background-position:top left;
		background-repeat:repeat-x;
		background-color:#FFFFFF;
	}

	#event_img{
		padding:3px;
	}
	#event_img img{
		padding:0;
		margin:0;
		vertical-align:bottom;
	}

	#event_dates{

	}
	#event_dates:after{
		content: ".";
		display: block;
		height: 0;
		clear: both;
		visibility:hidden;
	}
	#event_dates{display: inline-block;}
	/* Hides from IE Mac \*/
	* html #event_dates {height: 1%;}
	#event_dates{display:block;}
	/* End Hack */

	div.float_half{
		float:left;
		width:45%;
	}


	div.content_box_ds{
		padding:0 1px 1px 0;
		background-color:#ACABA9;
		border-right:1px solid #C0C0C0;
		border-bottom:1px solid #C0C0C0;
		margin:0 0 20px 0;
	}
	div.content_box{
		padding:0;
		border:1px solid #96BBC6;
		background-color:#EFF9FC;
	}
	div.content_box_content{
		padding:6px;
	}
	div.info_section{
		margin:4px 0 10px 0;
	}
	div.content_box h1{
		color:#179DB5;
		font-size:0.8em;
		font-weight:bold;
		margin:0;
		padding:0;
	}
	p.heading_text{
		color:#000000;
		font-weight:bold;
		font-size:1em;
		margin:0;
		padding:0;
	}
	p.body_text{
		padding:4px 4px 4px 0;
		background-color:#FFFFFF;
		border:1px solid #EFF9FC;
		color:#333333;
		font-size:0.9em;
		margin:0;
	}
	p.body_text img{
		margin:0 0 -5px 0;
	}
	p.body_text a{
		color:#CC0066;
		text-decoration:none;
	}
	p.body_text a:hover{
		color:#CC0066;
		text-decoration:underline;
	}
	div.event_status_icon{
		padding:12px 0 12px 0;
		color:#333333;
		font-size:0.9em;
		font-weight:bold;
	}
	div.event_status_icon img{
		margin:0 0 -4px 0;
		vertical-align:bottom;
	}

/*******************************************************/
/* EVENT LIST		  								   */
/*******************************************************/
	#events_table{
		margin:0;
		padding:10px;
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



	#event_search{
		margin:10px 0  20px 0;
		padding:10px;
		background-color:#FDE7F7;
	}
	#event_search:after{
		content: ".";
		display: block;
		height: 0;
		clear: both;
		visibility:hidden;
	}
	#event_search{display: inline-block;}
	/* Hides from IE Mac \*/
	* html #event_search {height: 1%;}
	#event_search{display:block;}
	/* End Hack */

	#event_search form{
		margin:0;
		padding:2px;
	}
	#event_search label{
		font-weight:bold;
		color:#333333;
		font-size:0.9em;
		margin:0;
		padding:0;
		background-color:#AEEF4F;
		background-image:url("<?php echo $baseUrl; ?>/images/cal_event_bg_3.jpg");
		background-position:top left;
		background-repeat:repeat-x;
		padding:4px;
		border:1px solid #999999;
		border-top:1px solid #FFFFFF;
	}
	#event_search select, input{
		margin:0 0 -3px 0;
		padding:0;
		vertical-align:bottom;
		border:1px solid #666666;
	}
	.a_column{
		width:250px;
		float:left;
	}


	#event_search span{
		text-align: center;
		font-size: 1em;
		color:#333333;
		background:#666666;
		border-bottom: 1px solid #CCCCCC;
		border-right: 1px solid #CCCCCC;
		border-top: 1px solid #FFFFFF;
		border-left: 1px solid #FFFFFF;
		font-weight:normal;
		background-image:url("<?php echo $baseUrl; ?>/images/cal_search_days_bg.jpg");
		background-position:top left;
		background-repeat:repeat-x;
		padding:4px;
		margin:0;
	}
	#event_search img{
		vertical-align: bottom;
		margin:0 0 -6px 0;
		padding:0;
	}
/*******************************************************/
/* ADD / EDIT FORM									   */
/*******************************************************/
	#add_edit_form{


	}
	#add_edit_form form{
		margin:0;
		padding:10px;
		width:550px;
	}
	#add_edit_form label{
		font-weight:bold;
		color:#333333;
		font-size:0.9em;
		margin:0;
		padding:0;
		background-color:#AEEF4F;
		background-image:url("<?php echo $baseUrl; ?>/images/cal_event_bg_2.jpg");
		background-position:top left;
		background-repeat:repeat-x;
		padding:4px;
		border:1px solid #999999;
		border-top:1px solid #FFFFFF;
		border-left:1px solid #FFFFFF;
	}
	#add_edit_form textarea{
		margin:0;
		padding:0;
	}

	div.label_div{
		margin:0 0 8px 0;
	}
	div.form_link{
		color:#333333;
		padding:6px 0 0 0;
	}
	div.form_link a{
		font-size:1em;
		color:#333333;
		text-decoration:none;
	}
	div.form_link a:hover{
		color:#0066CC;
	}

	.help_para{
		color:#333333;
		font-size:0.9em;
		padding:2px;
		background-color:#ffffff;
		width:400px;
		margin:0 0 10px 0;
	}
	.form_section{
		margin:10px 0 0 0;
		background-color:#F1F8FA;
		border:1px solid #D0DCE0;
		padding:10px;
	}
	.a_column{
		width:250px;
		float:left;
	}
	#select_dates{
		margin:0 0 5px 0;
	}
	#select_dates:after{
		content: ".";
		display: block;
		height: 0;
		clear: both;
		visibility:hidden;
	}
	#select_dates{display: inline-block;}
	/* Hides from IE Mac \*/
	* html #select_dates {height: 1%;}
	#select_dates{display:block;}
	/* End Hack */

	#select_dates span{
		text-align: center;
		font-size: 1em;
		color:#333333;
		background:#666666;
		border-bottom: 1px solid #CCCCCC;
		border-right: 1px solid #CCCCCC;
		border-top: 1px solid #FFFFFF;
		border-left: 1px solid #FFFFFF;
		font-weight:normal;
		background-image:url("<?php echo $baseUrl; ?>/images/cal_days_bg.jpg");
		background-position:top left;
		background-repeat:repeat-x;
		padding:4px;
		margin:0;
	}
	#select_dates img{
		vertical-align: bottom;
		margin:0 0 -6px 0;
		padding:0;
	}
	#add_edit_form input, select{
		border:1px solid #999999;
		padding:2px;
		font-size:0.9em;
		color:#333333;
	}