<?php
namespace App\Filters;
class NFacture extends Filter 
{
    public function applyFilter($builder){
      return  $builder->where($this->filterName(),'=',request($this->filterName()));
    }
}