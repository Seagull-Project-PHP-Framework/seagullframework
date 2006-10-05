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
    width: 128px;
    height: 118px;
    border: 2px solid <?php echo $greyLight ?>;
    -moz-border-radius: 0.5em;
}
div.thumbMedia {
    margin: 0 auto;
    padding: 4px 0;
    width: 90px;
    height: 70px;
}
div.thumbMedia:hover {
    background: <?php echo $greyLight ?>;
}
.thumbBox img {
    
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
    font-size: 0.8em;
    font-weight: normal;
    color: <?php echo $greyDark ?>;
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

