<?php
    namespace App\Services;

    function createSlug($Repository,$entity){
    //setting a unique slug to stage
    $i=1;
            
    $slug = preg_replace('/[^a-z0-9]+/i','-',trim(strtolower($entity->getName())));
    $baseSlug  = $slug;// retaining the value of simple slug
   
    //searching if there is a slug in database like this and while it is adding 1 to last character
    while($Repository->findOneBy(['slug' => $slug])){ 
        $slug = $baseSlug ."-".$i++;       
    } 
    return $slug;
}