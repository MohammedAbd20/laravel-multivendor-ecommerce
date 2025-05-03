    <!-- Sidebar Menu -->
    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            @foreach ($items as $item)
                <li class="nav-item">
                    <a href="{{ route($item['route']) }}" class="nav-link {{ request()->is($item['active'].'*') ? 'active' : '' }}">
                        <i class="{{ $item['icon'] }}"></i>
                        <p>{{ $item['title'] }}</p>
                        @if (isset($item['badge']))
                            <span class="right badge badge-danger">New</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <!-- /.sidebar-menu -->
