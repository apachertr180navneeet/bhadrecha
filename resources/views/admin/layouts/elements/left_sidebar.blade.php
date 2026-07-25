<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('admin.dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img src="{{ asset('assets/admin/img/logo5.png') }}" alt="Logo" style="height: 32px; width: auto;">
            </span>
        </a>

        <a href="javascript:void(0);" class="menu-close-btn d-xl-none">
            <i class="bx bx-x bx-sm"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <style>
        .sidebar-search-wrapper {
            padding: 0.5rem 0.85rem;
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: inherit;
        }
        .sidebar-search-box {
            background-color: rgba(67, 89, 113, 0.04);
            border: 1px solid rgba(67, 89, 113, 0.15);
            border-radius: 0.375rem;
            transition: all 0.2s ease-in-out;
            display: flex;
            align-items: center;
        }
        .sidebar-search-box:focus-within {
            border-color: #696cff;
            box-shadow: 0 0 0 0.15rem rgba(105, 108, 255, 0.15);
            background-color: #ffffff;
        }
        .sidebar-search-box input::placeholder {
            color: #a1acb8;
            font-size: 0.82rem;
        }
        #menu-search-no-results {
            display: none;
        }
    </style>

    <div class="sidebar-search-wrapper">
        <div class="input-group input-group-merge sidebar-search-box">
            <span class="input-group-text border-0 bg-transparent ps-2 pe-1 py-1" id="menu-search-addon">
                <i class="bx bx-search text-muted" style="font-size: 1.1rem;"></i>
            </span>
            <input type="text" id="menu-search-input" class="form-control border-0 bg-transparent ps-1 pe-1 py-1 shadow-none" placeholder="Search menu items..." aria-label="Search menu items..." style="font-size: 0.83rem; height: 34px;">
            <span class="input-group-text border-0 bg-transparent pe-2 ps-1 py-1 d-none" id="menu-search-clear" style="cursor: pointer;">
                <i class="bx bx-x text-muted" style="font-size: 1.1rem;"></i>
            </span>
        </div>
    </div>

    <ul class="menu-inner py-1">
        <li class="menu-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>

        @canany(['view bulties', 'view trips', 'view billing'])
            <li class="menu-divider"></li>
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Transport Operations</span>
            </li>
        @endcanany

        @can('view bulties')
            <li class="menu-item {{ request()->is('admin/transport/bulties*') ? 'active open' : '' }}">
                <a href="{{ route('admin.transport.bulties.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-receipt"></i>
                    <div data-i18n="Bilties">Bilties (LR)</div>
                </a>
            </li>
        @endcan

        @can('view trips')
            <li class="menu-item {{ request()->is('admin/transport/trips') || (request()->is('admin/transport/trips/*') && !request()->is('admin/transport/trips/fuel-outstanding*') && !request()->is('admin/transport/trips/adblue-outstanding*')) ? 'active' : '' }}">
                <a href="{{ route('admin.transport.trips.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-carousel"></i>
                    <div data-i18n="Trips">Trips</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('admin/transport/trips/fuel-outstanding*') ? 'active' : '' }}">
                <a href="{{ route('admin.transport.trips.fuel-outstanding') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-gas-pump"></i>
                    <div data-i18n="Fuel Outstanding">Fuel Outstanding</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('admin/transport/trips/adblue-outstanding*') ? 'active' : '' }}">
                <a href="{{ route('admin.transport.trips.adblue-outstanding') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-water"></i>
                    <div data-i18n="AdBlue Outstanding">AdBlue Outstanding</div>
                </a>
            </li>
        @endcan

        @can('view billing')
            <li class="menu-item {{ request()->is('admin/transport/billing*') ? 'active' : '' }}">
                <a href="{{ route('admin.transport.billing') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-receipt"></i>
                    <div data-i18n="Generate Bill">Generate Bill</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('admin/transport/invoices*') ? 'active' : '' }}">
                <a href="{{ route('admin.transport.invoices.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-history"></i>
                    <div data-i18n="Invoice History">Invoice History</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('admin/transport/toll-bills*') ? 'active' : '' }}">
                <a href="{{ route('admin.transport.toll-bills.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-road"></i>
                    <div data-i18n="Toll Bills">Toll Bills</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('admin/letterheads*') ? 'active' : '' }}">
                <a href="{{ route('admin.letterheads.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-envelope-open"></i>
                    <div data-i18n="Letterhead Format">Letterhead Format</div>
                </a>
            </li>
        @endcan



        @canany(['view driver salary', 'view driver advances', 'generate driver salary slips', 'view driver salary
            slips'])
            <li class="menu-divider"></li>
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Driver Salary</span>
            </li>
            <li class="menu-item {{ request()->is('admin/driver-management/*') ? 'active open' : '' }} has-sub">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-user-circle"></i>
                    <div data-i18n="Driver Salary">Driver Salary</div>
                </a>
                <ul class="menu-sub">
                    @can('view driver salary')
                        <li
                            class="menu-item {{ request()->is('admin/driver-management/salary') || request()->is('admin/driver-management/salary/*') ? 'active' : '' }}">
                            <a href="{{ route('admin.driver-management.salary') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-money"></i>
                                <div data-i18n="Salary Management">Salary Management</div>
                            </a>
                        </li>
                    @endcan
                    @can('view driver advances')
                        <li class="menu-item {{ request()->is('admin/driver-management/advance*') ? 'active' : '' }}">
                            <a href="{{ route('admin.driver-management.advance') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-coin"></i>
                                <div data-i18n="Advance Management">Advance Management</div>
                            </a>
                        </li>
                    @endcan
                    @can('generate driver salary slips')
                        <li
                            class="menu-item {{ request()->is('admin/driver-management/salary-slip') || request()->is('admin/driver-management/salary-slip?*') ? 'active' : '' }}">
                            <a href="{{ route('admin.driver-management.salary-slip') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-receipt"></i>
                                <div data-i18n="Salary Slip">Generate Slip</div>
                            </a>
                        </li>
                    @endcan
                    @can('view driver salary slips')
                        <li class="menu-item {{ request()->is('admin/driver-management/salary-slip-list*') ? 'active' : '' }}">
                            <a href="{{ route('admin.driver-management.salary-slip.list') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-list-ul"></i>
                                <div data-i18n="All Salary Slips">All Salary Slips</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @canany(['view employee salary', 'view attendance', 'view leaves', 'view employee advances'])
            <li class="menu-divider"></li>
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Employee Salary</span>
            </li>
            <li
                class="menu-item {{ request()->is('admin/employee-salary*') || request()->is('admin/attendance*') || request()->is('admin/leaves*') || request()->is('admin/advances*') ? 'active open' : '' }} has-sub">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-wallet"></i>
                    <div data-i18n="Employee Salary">Employee Salary</div>
                </a>
                <ul class="menu-sub">
                    @can('view employee salary')
                        <li
                            class="menu-item {{ request()->is('admin/employee-salary/employees-list*') || request()->is('admin/employee-salary/employees-list/*/details') ? 'active' : '' }}">
                            <a href="@if (auth()->user()->isSuperAdmin() || auth()->user()->isCompanyAdmin()) {{ route('admin.employee-salary.employees-list') }} @else {{ route('admin.employee-salary.details', auth()->id()) }} @endif"
                                class="menu-link">
                                <i class="menu-icon tf-icons bx bx-group"></i>
                                <div data-i18n="Employee List">Employee List</div>
                            </a>
                        </li>
                    @endcan
                    @can('view attendance')
                        <li class="menu-item {{ request()->is('admin/attendance*') ? 'active' : '' }}">
                            <a href="{{ route('admin.attendance.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-calendar"></i>
                                <div data-i18n="Attendance">Attendance</div>
                            </a>
                        </li>
                    @endcan
                    @can('view leaves')
                        <li class="menu-item {{ request()->is('admin/leaves*') ? 'active' : '' }}">
                            <a href="{{ route('admin.leaves.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-exit"></i>
                                <div data-i18n="Leaves">Leaves</div>
                            </a>
                        </li>
                    @endcan
                        <li class="menu-item {{ request()->is('admin/advances*') ? 'active' : '' }}">
                            <a href="{{ route('admin.advances.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-money"></i>
                                <div data-i18n="Advance">Advance</div>
                            </a>
                        </li>
                </ul>
            </li>
        @endcanany

        @canany(['view company loans', 'view vehicle loans'])
            <li class="menu-divider"></li>
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Loan</span>
            </li>
            <li class="menu-item {{ request()->is('admin/loan/*') ? 'active open' : '' }} has-sub">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-credit-card"></i>
                    <div data-i18n="Loan">Loan</div>
                </a>
                <ul class="menu-sub">
                    @can('view company loans')
                        <li class="menu-item {{ request()->is('admin/loan/company-loan*') ? 'active' : '' }}">
                            <a href="{{ route('admin.loan.company-loan.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-buildings"></i>
                                <div data-i18n="Company Loan">Company Loan</div>
                            </a>
                        </li>
                    @endcan
                    @can('view vehicle loans')
                        <li class="menu-item {{ request()->is('admin/loan/vehicle*') ? 'active' : '' }}">
                            <a href="{{ route('admin.loan.vehicle') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-car"></i>
                                <div data-i18n="Vehicle Loan">Vehicle Loan</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @canany(['view service schedules', 'view spare parts', 'view maintenance history', 'view breakdowns', 'view tyre management'])
            <li class="menu-divider"></li>
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Maintenance</span>
            </li>
            <li class="menu-item {{ request()->is('admin/maintenance/*') ? 'active open' : '' }} has-sub">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-wrench"></i>
                    <div data-i18n="Maintenance">Maintenance</div>
                </a>
                <ul class="menu-sub">
                    @can('view service schedules')
                        <li class="menu-item {{ request()->is('admin/maintenance/service-schedule*') ? 'active' : '' }}">
                            <a href="{{ route('admin.maintenance.service-schedule.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-calendar"></i>
                                <div data-i18n="Service Schedule">Service Schedule</div>
                            </a>
                        </li>
                    @endcan
                    @can('view spare parts')
                        <li class="menu-item {{ request()->is('admin/maintenance/spare-part*') ? 'active' : '' }}">
                            <a href="{{ route('admin.maintenance.spare-part.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-cog"></i>
                                <div data-i18n="Spare Parts">Spare Parts</div>
                            </a>
                        </li>
                    @endcan
                    @can('view maintenance history')
                        <li class="menu-item {{ request()->is('admin/maintenance/maintenance-history*') ? 'active' : '' }}">
                            <a href="{{ route('admin.maintenance.maintenance-history.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-history"></i>
                                <div data-i18n="Maintenance History">Maintenance History</div>
                            </a>
                        </li>
                    @endcan
                    @can('view breakdowns')
                        <li class="menu-item {{ request()->is('admin/maintenance/breakdowns*') ? 'active' : '' }}">
                            <a href="{{ route('admin.maintenance.breakdowns.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-alarm-exclamation"></i>
                                <div data-i18n="Breakdown Management">Breakdown Management</div>
                            </a>
                        </li>
                    @endcan
                    @can('view tyre management')
                        <li class="menu-item {{ request()->routeIs('admin.maintenance.tyre-management.index') ? 'active' : '' }}">
                            <a href="{{ route('admin.maintenance.tyre-management.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-circle"></i>
                                <div data-i18n="Tyre Management">Tyre Management</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('admin.maintenance.tyre-management.layout') ? 'active' : '' }}">
                            <a href="{{ route('admin.maintenance.tyre-management.layout') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-grid-alt"></i>
                                <div data-i18n="Graphic Tyre Layout">Graphic Tyre Layout</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @canany(['view reports'])
            <li class="menu-divider"></li>
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Reports</span>
            </li>
        @endcanany

        @can('view reports')
            <li class="menu-item {{ request()->is('admin/reports/*') ? 'active open' : '' }} has-sub">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
                    <div data-i18n="Reports">Reports</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ request()->is('admin/reports/vehicle') ? 'active' : '' }}">
                        <a href="{{ route('admin.reports.vehicle') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-car"></i>
                            <div data-i18n="Vehicle Report">Vehicle Performance</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/reports/driver-trip*') ? 'active' : '' }}">
                        <a href="{{ route('admin.reports.driver-trip') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-user"></i>
                            <div data-i18n="Driver Trip Report">Driver Trip Report</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/reports/customer-ledger*') ? 'active' : '' }}">
                        <a href="{{ route('admin.reports.customer-ledger') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-book"></i>
                            <div data-i18n="Customer Ledger">Customer Ledger</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/reports/sales-ledger*') ? 'active' : '' }}">
                        <a href="{{ route('admin.reports.sales-ledger') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-rupee"></i>
                            <div data-i18n="Sales Ledger">Sales Ledger</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/reports/tds-report*') ? 'active' : '' }}">
                        <a href="{{ route('admin.reports.tds-report') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-receipt"></i>
                            <div data-i18n="TDS Report">TDS Report</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/reports/trip-reports*') ? 'active' : '' }}">
                        <a href="{{ route('admin.reports.trip-reports') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-detail"></i>
                            <div data-i18n="Trip Reports">Trip Reports</div>
                        </a>
                    </li>
                    <li class="menu-item {{ (request()->is('admin/reports/fuel') || request()->is('admin/reports/fuel/*')) && !request()->is('admin/reports/fuel-outstanding*') ? 'active' : '' }}">
                        <a href="{{ route('admin.reports.fuel') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-gas-pump"></i>
                            <div data-i18n="Fuel Report">Fuel Report</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/reports/fuel-outstanding*') ? 'active' : '' }}">
                        <a href="{{ route('admin.reports.fuel-outstanding') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-wallet"></i>
                            <div data-i18n="Fuel Outstanding">Fuel Outstanding</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/reports/adblue-outstanding*') ? 'active' : '' }}">
                        <a href="{{ route('admin.reports.adblue-outstanding') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-water"></i>
                            <div data-i18n="AdBlue Outstanding">AdBlue Outstanding</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/reports/vehicle-utilization*') ? 'active' : '' }}">
                        <a href="{{ route('admin.reports.vehicle-utilization') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-line-chart"></i>
                            <div data-i18n="Vehicle Utilization">Vehicle Utilization</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/reports/mis*') ? 'active' : '' }}">
                        <a href="{{ route('admin.reports.mis') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-bar-chart-square"></i>
                            <div data-i18n="MIS Report">MIS Report</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/reports/expense-management*') ? 'active' : '' }}">
                        <a href="{{ route('admin.reports.expense-management') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-money"></i>
                            <div data-i18n="Expense Management Report">Expense Management</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/reports/vehicle-documents*') ? 'active' : '' }}">
                        <a href="{{ route('admin.reports.vehicle-documents') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-file-blank"></i>
                            <div data-i18n="Vehicle Document Report">Vehicle Documents</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/reports/gst-tax*') ? 'active' : '' }}">
                        <a href="{{ route('admin.reports.gst-tax') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-receipt"></i>
                            <div data-i18n="GST & Tax Report">GST & Tax</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/reports/profit-loss*') ? 'active' : '' }}">
                        <a href="{{ route('admin.reports.profit-loss') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-trending-up"></i>
                            <div data-i18n="Profit & Loss Report">Profit & Loss</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endcan

        @canany(['view consignors', 'view consignees', 'view vehicles', 'view drivers', 'view companies', 'view
            branches', 'view gst', 'view cities', 'view packagings', 'view units', 'view fuel pumps', 'view items', 'view
            suppliers', 'view vendors', 'view banks', 'view bank branches', 'view bill formats'])
            <li class="menu-divider"></li>
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Masters</span>
            </li>
        @endcanany

        @canany(['view consignors', 'view consignees', 'view vehicles', 'view drivers', 'view companies', 'view
            branches', 'view gst', 'view cities', 'view packagings', 'view units', 'view fuel pumps', 'view items', 'view
            suppliers', 'view vendors', 'view banks', 'view bank branches', 'view bill formats'])
            <li
                class="menu-item {{ request()->is('admin/masters/*') || request()->is('admin/companies*') || request()->is('admin/branches*') ? 'active open' : '' }} has-sub">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-data"></i>
                    <div data-i18n="All Masters">All Masters</div>
                </a>
                <ul class="menu-sub">
                    @can('view consignors')
                        <li class="menu-item {{ request()->is('admin/masters/consignors*') ? 'active' : '' }}">
                            <a href="{{ route('admin.masters.consignors.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-user-plus"></i>
                                <div data-i18n="Consignors">Consignors</div>
                            </a>
                        </li>
                    @endcan
                    @can('view consignees')
                        <li class="menu-item {{ request()->is('admin/masters/consignees*') ? 'active' : '' }}">
                            <a href="{{ route('admin.masters.consignees.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-user-check"></i>
                                <div data-i18n="Consignees">Consignees</div>
                            </a>
                        </li>
                    @endcan
                    @can('view vehicles')
                        <li class="menu-item {{ request()->is('admin/masters/vehicles*') ? 'active' : '' }}">
                            <a href="{{ route('admin.masters.vehicles.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-car"></i>
                                <div data-i18n="Vehicles">Vehicles</div>
                            </a>
                        </li>
                    @endcan
                    @can('view drivers')
                        <li class="menu-item {{ request()->is('admin/masters/drivers*') ? 'active' : '' }}">
                            <a href="{{ route('admin.masters.drivers.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-id-card"></i>
                                <div data-i18n="Drivers">Drivers</div>
                            </a>
                        </li>
                    @endcan
                    @can('view companies')
                        <li class="menu-item {{ request()->is('admin/companies*') ? 'active' : '' }}">
                            <a href="{{ route('admin.companies.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-buildings"></i>
                                <div data-i18n="Companies">Companies</div>
                            </a>
                        </li>
                    @endcan
                    @can('view branches')
                        <li class="menu-item {{ request()->is('admin/branches*') ? 'active' : '' }}">
                            <a href="{{ route('admin.branches.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-store-alt"></i>
                                <div data-i18n="Branches">Branches</div>
                            </a>
                        </li>
                    @endcan
                    @can('view gst')
                        <li class="menu-item {{ request()->is('admin/masters/gst*') ? 'active' : '' }}">
                            <a href="{{ route('admin.masters.gst.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-dollar-circle"></i>
                                <div data-i18n="GST">GST Master</div>
                            </a>
                        </li>
                    @endcan
                    @can('view cities')
                        <li class="menu-item {{ request()->is('admin/masters/city*') ? 'active' : '' }}">
                            <a href="{{ route('admin.masters.city.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-building"></i>
                                <div data-i18n="Cities">Cities</div>
                            </a>
                        </li>
                    @endcan
                    @can('view packagings')
                        <li class="menu-item {{ request()->is('admin/masters/packagings*') ? 'active' : '' }}">
                            <a href="{{ route('admin.masters.packagings.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-package"></i>
                                <div data-i18n="Packagings">Packagings</div>
                            </a>
                        </li>
                    @endcan
                    @can('view units')
                        <li class="menu-item {{ request()->is('admin/masters/units*') ? 'active' : '' }}">
                            <a href="{{ route('admin.masters.units.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-ruler"></i>
                                <div data-i18n="Units">Units</div>
                            </a>
                        </li>
                    @endcan
                    @can('view fuel pumps')
                        <li class="menu-item {{ request()->is('admin/masters/fuel-pumps*') ? 'active' : '' }}">
                            <a href="{{ route('admin.masters.fuel-pumps.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-gas-pump"></i>
                                <div data-i18n="Fuel Pumps">Fuel Pumps</div>
                            </a>
                        </li>
                    @endcan
                    @can('view fuel companies')
                        <li class="menu-item {{ request()->is('admin/masters/fuel-companies*') ? 'active' : '' }}">
                            <a href="{{ route('admin.masters.fuel-companies.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-tag"></i>
                                <div data-i18n="Fuel Companies">Fuel Companies</div>
                            </a>
                        </li>
                    @endcan
                    @can('view adblue companies')
                        <li class="menu-item {{ request()->is('admin/masters/adblue-companies*') ? 'active' : '' }}">
                            <a href="{{ route('admin.masters.adblue-companies.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-droplet"></i>
                                <div data-i18n="AdBlue Companies">AdBlue Companies</div>
                            </a>
                        </li>
                    @endcan
                    <li class="menu-item {{ request()->is('admin/masters/tyre-brands*') ? 'active' : '' }}">
                        <a href="{{ route('admin.masters.tyre-brands.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-disc"></i>
                            <div data-i18n="Tyre Brands">Tyre Brands</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/masters/tyre-models*') ? 'active' : '' }}">
                        <a href="{{ route('admin.masters.tyre-models.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-layer"></i>
                            <div data-i18n="Tyre Models">Tyre Models</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/masters/tyre-sizes*') ? 'active' : '' }}">
                        <a href="{{ route('admin.masters.tyre-sizes.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-ruler"></i>
                            <div data-i18n="Tyre Sizes">Tyre Sizes</div>
                        </a>
                    </li>
                    @can('view items')
                        <li class="menu-item {{ request()->is('admin/masters/items*') ? 'active' : '' }}">
                            <a href="{{ route('admin.masters.items.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-cube"></i>
                                <div data-i18n="Items">Items</div>
                            </a>
                        </li>
                    @endcan
                    @can('view suppliers')
                        <li class="menu-item {{ request()->is('admin/masters/suppliers*') ? 'active' : '' }}">
                            <a href="{{ route('admin.masters.suppliers.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-package"></i>
                                <div data-i18n="Suppliers">Suppliers</div>
                            </a>
                        </li>
                    @endcan
                    @can('view vendors')
                        <li class="menu-item {{ request()->is('admin/masters/vendors*') ? 'active' : '' }}">
                            <a href="{{ route('admin.masters.vendors.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-store"></i>
                                <div data-i18n="Vendors">Vendors</div>
                            </a>
                        </li>
                    @endcan
                    @can('view banks')
                        <li class="menu-item {{ request()->is('admin/masters/banks*') ? 'active' : '' }}">
                            <a href="{{ route('admin.masters.banks.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-building-house"></i>
                                <div data-i18n="Banks">Banks</div>
                            </a>
                        </li>
                    @endcan
                    @can('view bank branches')
                        <li class="menu-item {{ request()->is('admin/masters/bank-branches*') ? 'active' : '' }}">
                            <a href="{{ route('admin.masters.bank-branches.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-git-branch"></i>
                                <div data-i18n="Bank Branches">Bank Branches</div>
                            </a>
                        </li>
                    @endcan
                    @can('view bill formats')
                        <li class="menu-item {{ request()->is('admin/masters/bill-formats*') ? 'active' : '' }}">
                            <a href="{{ route('admin.masters.bill-formats.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-file"></i>
                                <div data-i18n="Bill Formats">Bill Formats</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @canany(['view users', 'view roles', 'view permissions'])
            <li class="menu-divider"></li>
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Access Control</span>
            </li>
        @endcanany

        @can('view users')
            <li class="menu-item {{ request()->is('admin/users*') ? 'active' : '' }}">
                <a href="{{ route('admin.users.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user"></i>
                    <div data-i18n="Users">Users</div>
                </a>
            </li>
        @endcan

        @can('view roles')
            <li class="menu-item {{ request()->is('admin/roles*') ? 'active' : '' }}">
                <a href="{{ route('admin.roles.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-shield-quarter"></i>
                    <div data-i18n="Roles">Roles</div>
                </a>
            </li>
        @endcan

        @can('view permissions')
            <li class="menu-item {{ request()->is('admin/permissions*') ? 'active' : '' }}">
                <a href="{{ route('admin.permissions.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-key"></i>
                    <div data-i18n="Permissions">Permissions</div>
                </a>
            </li>
        @endcan

        <!-- Document Management Section -->
        <li class="menu-divider"></li>
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Document Management</span>
        </li>

        <li class="menu-item {{ request()->is('admin/documents/dashboard') ? 'active' : '' }}">
            <a href="{{ route('admin.documents.dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-grid-alt"></i>
                <div data-i18n="Doc Dashboard">Doc Dashboard</div>
            </a>
        </li>

        <li class="menu-item {{ request()->is('admin/documents') || request()->is('admin/documents/create') || (request()->is('admin/documents/*') && !request()->is('admin/documents/dashboard*') && !request()->is('admin/documents/categories*') && !request()->is('admin/documents/folders*') && !request()->is('admin/documents/trash*') && !request()->is('admin/documents/activity-logs*') && !request()->is('admin/documents/reports*')) ? 'active' : '' }}">
            <a href="{{ route('admin.documents.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-folder-open"></i>
                <div data-i18n="Document Explorer">Document Explorer</div>
            </a>
        </li>

        <li class="menu-item {{ request()->is('admin/documents/categories*') ? 'active' : '' }}">
            <a href="{{ route('admin.documents.categories.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-category"></i>
                <div data-i18n="Doc Categories">Doc Categories</div>
            </a>
        </li>

        <li class="menu-item {{ request()->is('admin/documents/folders*') ? 'active' : '' }}">
            <a href="{{ route('admin.documents.folders.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-folder"></i>
                <div data-i18n="Doc Folders">Doc Folders</div>
            </a>
        </li>

        <li class="menu-item {{ request()->is('admin/documents/reports/expiry*') ? 'active' : '' }}">
            <a href="{{ route('admin.documents.reports.expiry') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-time-five"></i>
                <div data-i18n="Expiry Alerts">Expiry Alerts</div>
            </a>
        </li>

        <li class="menu-item {{ request()->is('admin/documents/reports/storage*') ? 'active' : '' }}">
            <a href="{{ route('admin.documents.reports.storage') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-pie-chart-alt-2"></i>
                <div data-i18n="Storage Usage">Storage Usage</div>
            </a>
        </li>

        <li class="menu-item {{ request()->is('admin/documents/activity-logs*') ? 'active' : '' }}">
            <a href="{{ route('admin.documents.activity-logs') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-list-check"></i>
                <div data-i18n="Doc Audit Logs">Doc Audit Trail</div>
            </a>
        </li>

        <li class="menu-item {{ request()->is('admin/documents/trash*') ? 'active' : '' }}">
            <a href="{{ route('admin.documents.trash') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-trash"></i>
                <div data-i18n="Doc Trash">Doc Trash Bin</div>
            </a>
        </li>

        @canany(['view activity logs', 'manage settings'])
            <li class="menu-divider"></li>
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">System</span>
            </li>
        @endcanany

        @can('view activity logs')
            <li class="menu-item {{ request()->is('admin/activity-logs*') ? 'active' : '' }}">
                <a href="{{ route('admin.activity-logs') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-history"></i>
                    <div data-i18n="Activity Logs">Activity Logs</div>
                </a>
            </li>
        @endcan

        @can('manage settings')
            <li class="menu-item {{ request()->is('admin/settings*') ? 'active' : '' }}">
                <a href="{{ route('admin.settings') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-cog"></i>
                    <div data-i18n="Settings">Settings</div>
                </a>
            </li>
        @endcan

    </ul>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('menu-search-input');
    var searchClear = document.getElementById('menu-search-clear');
    var menuInner = document.querySelector('#layout-menu .menu-inner');
    if (!searchInput || !menuInner) return;

    // Create No Results element dynamically inside menu-inner
    var noResults = document.createElement('li');
    noResults.id = 'menu-search-no-results';
    noResults.className = 'menu-item text-center py-4 px-3 text-muted';
    noResults.style.display = 'none';
    noResults.innerHTML = '<i class="bx bx-search-alt fs-4 d-block mb-1 opacity-50"></i><span style="font-size: 0.82rem;">No matching menu items found</span>';
    menuInner.appendChild(noResults);

    // Track initial 'open' state of top-level items
    var topLevelItems = menuInner.querySelectorAll(':scope > li.menu-item');
    var initialOpenStates = new Map();
    topLevelItems.forEach(function (item) {
        initialOpenStates.set(item, item.classList.contains('open'));
    });

    function filterMenu() {
        var query = searchInput.value.trim().toLowerCase();
        
        if (query.length > 0) {
            searchClear.classList.remove('d-none');
        } else {
            searchClear.classList.add('d-none');
        }

        if (query === '') {
            noResults.style.display = 'none';
            
            // Reset all top-level li elements
            var allListItems = menuInner.querySelectorAll(':scope > li');
            allListItems.forEach(function (li) {
                if (li.id === 'menu-search-no-results') return;
                li.style.display = '';
            });

            // Reset sub-items and parent open states
            topLevelItems.forEach(function (item) {
                var subItems = item.querySelectorAll('.menu-sub > .menu-item');
                subItems.forEach(function (sub) {
                    sub.style.display = '';
                });

                var subList = item.querySelector('.menu-sub');
                if (subList) {
                    subList.style.display = '';
                }

                if (initialOpenStates.get(item)) {
                    item.classList.add('open');
                } else {
                    item.classList.remove('open');
                }
            });
            return;
        }

        var totalVisibleCount = 0;

        // Hide dividers while searching
        var dividers = menuInner.querySelectorAll(':scope > li.menu-divider');
        dividers.forEach(function (divider) {
            divider.style.display = 'none';
        });

        // Filter menu items
        topLevelItems.forEach(function (item) {
            var subList = item.querySelector('.menu-sub');
            var parentLink = item.querySelector(':scope > .menu-link');
            var parentText = parentLink ? parentLink.textContent.trim().toLowerCase() : '';

            if (subList) {
                var subItems = item.querySelectorAll('.menu-sub > .menu-item');
                var parentMatches = parentText.includes(query);
                var visibleSubCount = 0;

                subItems.forEach(function (sub) {
                    var subText = sub.textContent.trim().toLowerCase();
                    if (parentMatches || subText.includes(query)) {
                        sub.style.display = '';
                        visibleSubCount++;
                    } else {
                        sub.style.display = 'none';
                    }
                });

                if (parentMatches || visibleSubCount > 0) {
                    item.style.display = '';
                    item.classList.add('open');
                    subList.style.display = 'block';
                    totalVisibleCount++;
                } else {
                    item.style.display = 'none';
                    item.classList.remove('open');
                }
            } else {
                var itemText = item.textContent.trim().toLowerCase();
                if (itemText.includes(query)) {
                    item.style.display = '';
                    totalVisibleCount++;
                } else {
                    item.style.display = 'none';
                }
            }
        });

        // Toggle section headers visibility based on whether any item in section is visible
        var children = Array.from(menuInner.children);
        var currentHeader = null;
        var currentHeaderHasVisible = false;

        children.forEach(function (child) {
            if (child.classList.contains('menu-header')) {
                if (currentHeader) {
                    currentHeader.style.display = currentHeaderHasVisible ? '' : 'none';
                }
                currentHeader = child;
                currentHeaderHasVisible = false;
            } else if (child.classList.contains('menu-item') && child.id !== 'menu-search-no-results') {
                if (child.style.display !== 'none') {
                    currentHeaderHasVisible = true;
                }
            }
        });
        if (currentHeader) {
            currentHeader.style.display = currentHeaderHasVisible ? '' : 'none';
        }

        // Show/hide no results message
        if (totalVisibleCount === 0) {
            noResults.style.display = 'block';
        } else {
            noResults.style.display = 'none';
        }
    }

    searchInput.addEventListener('input', filterMenu);

    if (searchClear) {
        searchClear.addEventListener('click', function () {
            searchInput.value = '';
            filterMenu();
            searchInput.focus();
        });
    }

    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            searchInput.value = '';
            filterMenu();
            searchInput.blur();
        }
    });
});
</script>
