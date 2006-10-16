php_files=$(./etc/mtce/findphp.sh);

for file in $php_files; do
 perl -pi -e 's/\r\n?/\n/g' $file
done;
