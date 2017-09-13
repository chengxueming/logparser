#!/bin/bash
domain=$1
source=$2
target=$3
date=$4
depth=${5:-1}
#./help_gunzip.sh listen.iciba.com /mnt/nginxlog/nginx/ /home/chengxueming/temp_event/ $date
g_dest="${target}${domain}/"
if [ ! -d "$g_dest" ]
    then
        mkdir "$g_dest"
fi
g_source="${source}${domain}/"
i=0
array=()
while [ $i -lt ${depth} ]
do
    logdate=$(date -d "${date} -${i} day" +%Y%m%d)
    echo ${logdate}
    source_file="${domain}_access.log-${logdate}"
    array2=($(find ${g_source} -name "${source_file}*"))
    array=(${array[@]} ${array2[@]})
    i=`expr $i + 1`
done
for i in "${array[@]}"
do
    a=(${i//// })
    ip=${a[${#a[*]}-2]}
    dest="${g_dest}${ip}/"
    if [ ! -d "$dest" ]
        then
            mkdir "$dest"
    fi
    cp_target="${dest}${source_file}"
    if [ ! -f "${cp_target}" ]
    then
        cp ${i} ${dest}
        echo "cp ${i} ${dest}"
    fi
done
array=($(find ${g_dest} ))
for file in "${array[@]}"
    do
    if [ "${file##*.}" == "gz" ]
        then
            gunzip $file
    fi
done
