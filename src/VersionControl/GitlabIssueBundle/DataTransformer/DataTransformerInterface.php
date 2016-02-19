<?php
namespace VersionControl\GitlabIssueBundle\DataTransformer;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

interface DataTransformerInterface{
    
     public function transform($array);
     
     public function reverseTransform($entiy);
     
}