for fl in *.php; do
mv $fl $fl.old
sed 's/file_exists/is_file/g' $fl.old > $fl
rm -f $fl.old
done