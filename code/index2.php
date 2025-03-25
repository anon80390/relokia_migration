<?php
$array = ['mount', 'ore', 'green', 'road', 'telephone', 'mom', 'more'];
$letter = 'm';

function wordArray($array, $letter){
    $res = [];

    foreach($array as $item){
        if(str_starts_with($item, $letter)){
           array_push($res, $item);
        }

    }
return $res;
}
$output = wordArray($array, $letter);



//$filtered_users = array_filter($users, "updateUsers");

$let = '';