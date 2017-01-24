/*************************/
/*      MEDIA LIST       */
/*************************/

.moduleToolbar {
    float: left;
    width: 100%;
    padding-bottom: 5px;
    text-align: center;
}
.moduleToolbar li {
    display: inline;
    float:left;
    list-style-type: none;
    margin: 0 15px 0 5px;
}
.moduleToolbar a:hover {
    text-decoration: none;
}
.moduleToolbar a span {
    display: block;
    margin: 0;
    color: <?php echo $greyDark ?>;
    font-weight: bold;
}

.extrabar {
    float: left;
    width: 100%;
    background: <?php echo $primaryLight ?>;
    border: 1px solid <?php echo $primary ?>;
    border-top: 3px solid <?php echo $primary ?>;
}
.extrabarInner {
    padding: 4px;
}
.extrabar h2 {
    color: <?php echo $greyDark ?>;
}

.moduleContent {
    float: left;
    width: 100%;
    margin: 10px 0;
}

/*****************************/
/*   MEDIA LIST THUMB VIEW   */
/*****************************/
div.thumb {
    float: left;
    margin: 0 0 5px 5px;
    text-align: center;
}

div.thumbBox {
    position: relative;
    /* TODO This should be more flexible */
    /* and not depend on a 70px maxsize */
    width: 128px;
    height: 118px;
    border: 1px solid <?php echo $greyLight ?>;
    -moz-border-radius: 0.5em;
}
div.thumbMedia {
    margin: 0 auto;
    padding: 4px 0;
    width: 90px;
    height: 70px;
}
div.thumbBox:hover {
    background: <?php echo $greyLight ?>;
}
.thumbMedia img {
    border: 2px solid <?php echo $greyLightest ?>;
}

div.thumbToolbar {
    height: 1.8em;
    line-height: 1.8em;
}
div.thumbToolbar a {
    color: <?php echo $greyDark ?>;
    text-decoration: underline;
}
div.thumbToolbar a:hover {
    color: <?php echo $primaryDark ?>;
}
div.thumbToolbar span {
    vertical-align: middle;
}
div.thumbInfo {
    display: none;
}

div.thumb:hover .thumbBox {

}
div.thumb:hover .thumbToolbar span {
    visibility: visible;
}

div.thumbMedia {

}

.thumb h3 {
    width: 124px;
    overflow: hidden;
    font-size: 0.9em;
    margin-top: 0.8em;
    font-weight: normal;
    color: <?php echo $greyDark ?>;
}

/************************************/
/*           IMG ASSOC MGR          */
/************************************/
.complete_thumb{
    float:left;
    margin:0;
    padding:10px 0 10px 0;
}
.complete_thumb_highlighted{
    float:left;
    margin:0 10px 10px 0;
    padding:14px 10px 4px 10px;
    background-color:#f6f5f2;
    border:1px solid #d9d8cb;
    height:100px;
    width:20%;
}
div.complete_thumb_highlighted input{
    margin:0 0 4px 0;
    padding:0;
    border:1px solid #c3cea5;
}
/*
.thumb img{
    border:1px solid #666666;
    margin:0;
    padding:1px;
    background-color:#ffffff;
    vertical-align:bottom;
}
*/

.thumb img:hover{
    /*border:1px solid #000000;*/
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

/*************************/
/*      MEDIA EDIT       */
/*************************/
#content form li {
    margin-right: 290px;
}

#mediaImage {
    position: absolute;
    width: 270px;
    top: 1em;
    right: 0;
}
div.mediaDetail {
    padding: 2px 10px;
}

