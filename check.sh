clear
#if [[ -e ok.txt ]]; then
#rm -f ok.txt
#fi
while ISF= read -r site ;do
status=$(curl -o /dev/null --silent --head --write-out '%{http_code}\n' -A "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1" --max-time 50 -s -L -k $site/$2)
if [[ "$status" == `echo '2**'` || "$status" == `echo '3**'` ]]; then
echo -e " \e[1;32m[ $status ]  $site/$2\e[0m"
echo "http://$site/$2" >> ru.txt
else
#printf ''
echo -e " \e[1;31m[ $status ]  $site/$2\e[0m"
#echo $site/$2 >> deface.txt
fi
done < $1
