#!/bin/bash
domain=$1
source=$2
target=$3
date=$4
#./help_gunzip.sh listen.iciba.com /mnt/nginxlog/nginx/ /home/chengxueming/temp_event $date
g_dest="${target}${domain}/"
if [ ! -d "$g_dest" ]
    then
        mkdir "$g_dest"
fi
g_source="${source}${domain}"
cd $g_dest
source_file="${domain}_access.log-${date}"
array=($(find ${g_source} -name "${source_file}*"))
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
