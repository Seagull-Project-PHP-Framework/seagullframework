php_files=$(./etc/mtce/findphp.sh); 

for file in $php_files; do
 tab2space $file $file;
done;
