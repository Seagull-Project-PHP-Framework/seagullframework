Gallery 2 embedding module

- install gallery 2 (gallery.menalto.com) with an other username than the one that you are using in Seagull (propably "admin" in both by default)
- install this module (untar in seagull root & edit ini file)
- header template needs following custom part to include gallery2 css & javascript 
(example found in package themes/default/gallery2em/header.html):
{foreach:headerExtras,extra}
{extra:h}
{end:}

- login to seagull, go to someaddress/index.php/gallery2em/ -> account should be created in gallery2
- with your gallery 2 account give more (or propably admin rights) to newly created account
- be happy with your embedded gallery 2 install

extra step (clean URLs)
 - difficult part; may break everything
 - enable clean url's on embedded side
 - replace created .htaccess with:
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteRule ^index.php/gallery2em/action/list/v/(.+)$  index.php/gallery2em/action/list/?g2_view=core.ShowItem&g2_path=$1   [QSA,L]
</IfModule> 