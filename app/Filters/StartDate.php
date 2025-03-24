<?php
namespace App\Filters;

use Carbon\Carbon;
use Closure;

class StartDate
{
    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        if (!request('startDate')) {
            return $next($request);
        }
        $builder = $next($request);
        return $this->applyFilter($builder);
    }
    public function applyFilter($builder)
    {
        return $builder->whereDate('date', '>=',Carbon::parse(request('startDate'))->addDay()->format('Y-m-d'));
    }
}
