<div class="row  dashboard_heading mb-3">
    <div class="card fixed-tab col-12 col-md-12">
        <ul class="nav nav-tabs">
            @can('read_salary_advance')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('salary-advance.index') ? 'active' : '' }}"
                        href="{{ route('salary-advance.index') }}">{{ localize('salary') }}
                        {{ localize('advance') }}</a>
                </li>
            @endcan
        </ul>
    </div>
</div>
