<nav aria-label="Main">
    @foreach ($groups as $group)
        <div class="sb-group">
            <div class="sb-group-title">{{ $group['title'] }}</div>
            @foreach ($group['items'] as $item)
                <a href="{{ route($item['route']) }}" class="sb-link {{ request()->is($item['active']) ? 'active' : '' }}"
                    @if (request()->is($item['active'])) aria-current="page" @endif>
                    <i class="{{ $item['icon'] }}"></i>
                    <span>{{ $item['title'] }}</span>
                    @if ($item['count'] > 0)
                        <span class="sb-badge">{{ $item['count'] }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    @endforeach
</nav>
