<?php

namespace App\Http\Middleware\AU;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AU\AU;


class CheckOpenedAU
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            AU::get_au_en_cours();
        } catch (\Exception $th) {
            if($request->expectsJson())
                return response()->json(['message'=>'au_fermee'], 401);
            else return redirect(route('au_fermee'));
        }
        return $next($request);
    }
}
