<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $memberId = $request->session()->get('member_id');
        $memberRole = $memberId
            ? DB::table('member')->where('id_member', $memberId)->value('role')
            : null;

        if ($memberRole !== $role) {
            if ($memberRole === 'merchant') {
                return redirect()->route('merchant.dashboard');
            }

            if ($memberRole === 'customer') {
                return redirect()->route('customer.dashboard');
            }

            $request->session()->invalidate();

            return redirect()->route('login');
        }

        $request->attributes->set('member_role', $memberRole);

        return $next($request);
    }
}
