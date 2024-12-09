<div class="row  dashboard_heading mb-3">
    <div class="card fixed-tab col-12 col-md-12">
        <ul class="nav nav-tabs">
            @can('read_payroll_report')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('reports.npf3-soc-sec-tax-report') ? 'active' : '' }}"
                        href="{{ route('reports.npf3-soc-sec-tax-report') }}">salary report</a>
                </li>
            @endcan
        </ul>
    </div>
</div>
