<?php
namespace App\Filters;


class FournisseurId extends Filter{

    public function applyFilter($builder){
        return $builder->where($this->FilterName(),request($this->FilterName()));
    }
}
