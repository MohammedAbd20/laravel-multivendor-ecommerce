<?php

namespace App\View\Components;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class Nav extends Component
{
    public array $groups;

    public function __construct()
    {
        $user = Auth::user();
        $isSuper = (bool) ($user->super_admin ?? false);
        $badges = ['pending_orders' => Order::where('status', 'pending')->count()];

        $this->groups = collect(config('nav'))
            ->map(function ($group) use ($isSuper, $badges) {
                $group['items'] = collect($group['items'])
                    ->reject(fn ($item) => ! empty($item['super_admin']) && ! $isSuper)
                    ->map(function ($item) use ($badges) {
                        $item['count'] = isset($item['badge']) ? ($badges[$item['badge']] ?? 0) : 0;

                        return $item;
                    })
                    ->values()
                    ->all();

                return $group;
            })
            ->filter(fn ($group) => count($group['items']))
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('components.nav');
    }
}
