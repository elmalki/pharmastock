<?php

namespace App\Filters;

use Closure;
use Illuminate\Support\Str;

abstract class Filter 
{
    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        //return request($this->FilterName());
        if (!request($this->FilterName())) {
            return $next($request);
        }
        $builder = $next($request);
        return $this->applyFilter($builder);
    }
    /**
     * 
     * @return String
     */
    public function FilterName()
    {
        return Str::snake(class_basename($this));
    }
    /**
     * @param $builder
     * @return mixed
     */
    public abstract function applyFilter($builder);
}
