<?php
namespace App\Filters;
class NBon extends Filter 
{
    public function applyFilter($builder){
      return  $builder->where($this->filterName(),'like','%'.request($this->filterName()).'%');
    }
}
