#!/bin/bash
for i in $(cat $1) ; do
    ./links.php $i
done
