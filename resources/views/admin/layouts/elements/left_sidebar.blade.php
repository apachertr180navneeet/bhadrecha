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
        <!-- 1. Dashboard -->
        <li class="menu-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>

        <!-- Transport Operations -->
        @canany(['view bulties', 'view trips', 'view trip details'])
            <li class="menu-divider"></li>
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Operations</span>
            </li>
        @endcanany

        <!-- 2. Bilties (LR) -->
        @can('view bulties')
            <li class="menu-item {{ request()->is('admin/transport/bulties*') ? 'active open' : '' }}">
                <a href="{{ route('admin.transport.bulties.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-receipt"></i>
                    <div data-i18n="Bilties">Bilties (LR)</div>
                </a>
            </li>
        @endcan

        <!-- 3. Trip Details -->
        @canany(['view trips', 'view trip details'])
            <li class="menu-item {{ request()->is('admin/transport/trips*') ? 'active' : '' }}">
                <a href="{{ route('admin.transport.trips.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-carousel"></i>
                    <div data-i18n="Trip Details">Trip Details</div>
                </a>
            </li>
        @endcanany

        <!-- Masters Section -->
        <li class="menu-divider"></li>
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Masters</span>
        </li>

        <!-- 4. Consignors -->
        @can('view consignors')
            <li class="menu-item {{ request()->is('admin/masters/consignors*') ? 'active' : '' }}">
                <a href="{{ route('admin.masters.consignors.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user-plus"></i>
                    <div data-i18n="Consignors">Consignors</div>
                </a>
            </li>
        @endcan

        <!-- 5. Consignees -->
        @can('view consignees')
            <li class="menu-item {{ request()->is('admin/masters/consignees*') ? 'active' : '' }}">
                <a href="{{ route('admin.masters.consignees.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user-check"></i>
                    <div data-i18n="Consignees">Consignees</div>
                </a>
            </li>
        @endcan

        <!-- 6. Drivers -->
        @can('view drivers')
            <li class="menu-item {{ request()->is('admin/masters/drivers*') ? 'active' : '' }}">
                <a href="{{ route('admin.masters.drivers.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-id-card"></i>
                    <div data-i18n="Drivers">Drivers</div>
                </a>
            </li>
        @endcan

        <!-- 7. Vehicles -->
        @can('view vehicles')
            <li class="menu-item {{ request()->is('admin/masters/vehicles*') ? 'active' : '' }}">
                <a href="{{ route('admin.masters.vehicles.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-car"></i>
                    <div data-i18n="Vehicles">Vehicles</div>
                </a>
            </li>
        @endcan

        <!-- 8. Fuel Pumps -->
        @can('view fuel pumps')
            <li class="menu-item {{ request()->is('admin/masters/fuel-pumps*') ? 'active' : '' }}">
                <a href="{{ route('admin.masters.fuel-pumps.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-gas-pump"></i>
                    <div data-i18n="Fuel Pumps">Fuel Pumps</div>
                </a>
            </li>
        @endcan

        <!-- 9. Companies -->
        @can('view companies')
            <li class="menu-item {{ request()->is('admin/companies*') ? 'active' : '' }}">
                <a href="{{ route('admin.companies.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-buildings"></i>
                    <div data-i18n="Companies">Companies</div>
                </a>
            </li>
        @endcan

        <!-- 10. AdBlue Companies -->
        @can('view adblue companies')
            <li class="menu-item {{ request()->is('admin/masters/adblue-companies*') ? 'active' : '' }}">
                <a href="{{ route('admin.masters.adblue-companies.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-droplet"></i>
                    <div data-i18n="AdBlue Companies">AdBlue Companies</div>
                </a>
            </li>
        @endcan

        @canany(['view invoices', 'view billing'])
            <li class="menu-item {{ request()->is('admin/transport/invoices*') ? 'active' : '' }}">
                <a href="{{ route('admin.transport.invoices.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-history"></i>
                    <div data-i18n="Invoice History">Invoice History</div>
                </a>
            </li>
        @endcanany

        @canany(['view toll bills', 'view billing'])
            <li class="menu-item {{ request()->is('admin/transport/toll-bills*') ? 'active' : '' }}">
                <a href="{{ route('admin.transport.toll-bills.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calculator"></i>
                    <div data-i18n="Toll Bills">Toll Bills</div>
                </a>
            </li>
        @endcanany

        <li class="menu-item {{ request()->is('admin/fastag*') ? 'active open' : '' }} has-sub">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-credit-card-front"></i>
                <div data-i18n="FASTag Management">FASTag Management</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.fastag.index') || (request()->is('admin/fastag') && !request()->is('admin/fastag/*')) ? 'active' : '' }}">
                    <a href="{{ route('admin.fastag.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-list-check"></i>
                        <div data-i18n="FASTag Transactions">FASTag Transactions</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.fastag.fetch') ? 'active' : '' }}">
                    <a href="{{ route('admin.fastag.fetch') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-cloud-download"></i>
                        <div data-i18n="Fetch Transactions">Fetch Transactions</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.fastag.vehicle-wise') ? 'active' : '' }}">
                    <a href="{{ route('admin.fastag.vehicle-wise') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-truck"></i>
                        <div data-i18n="Vehicle-wise Transactions">Vehicle-wise Transactions</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.fastag.reports') ? 'active' : '' }}">
                    <a href="{{ route('admin.fastag.reports') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-bar-chart"></i>
                        <div data-i18n="Transaction Reports">Transaction Reports</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.fastag.sync-logs') ? 'active' : '' }}">
                    <a href="{{ route('admin.fastag.sync-logs') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-sync"></i>
                        <div data-i18n="Synchronization History">Synchronization History</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.fastag.config') ? 'active' : '' }}">
                    <a href="{{ route('admin.fastag.config') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-cog"></i>
                        <div data-i18n="FASTag API Configuration">FASTag API Configuration</div>
                    </a>
                </li>
            </ul>
        </li>

        @can('view letterheads')
            <li class="menu-item {{ request()->is('admin/letterheads*') ? 'active' : '' }}">
                <a href="{{ route('admin.letterheads.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-envelope-open"></i>
                    <div data-i18n="Letterhead Format">Letterhead Format</div>
                </a>
            </li>
        @endcan

        <!-- Access Control (Admin only) -->
        @canany(['view users', 'view roles'])
            <li class="menu-divider"></li>
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Access Control</span>
            </li>
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
        @endcanany

        <!-- No search results placeholder -->
        <li id="menu-search-no-results" class="menu-item text-center py-3 text-muted" style="display: none;">
            <i class="bx bx-search-alt-2 fs-4 d-block mb-1"></i>
            <small>No matching menu items</small>
        </li>
    </ul>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('menu-search-input');
        const clearBtn = document.getElementById('menu-search-clear');
        const menuInner = document.querySelector('.menu-inner');
        const noResults = document.getElementById('menu-search-no-results');

        if (!searchInput || !menuInner) return;

        searchInput.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();
            if (clearBtn) {
                clearBtn.classList.toggle('d-none', query.length === 0);
            }

            const items = menuInner.querySelectorAll('.menu-item:not(#menu-search-no-results)');
            let visibleCount = 0;

            items.forEach(item => {
                const text = item.innerText.toLowerCase();
                const matches = text.includes(query);
                item.style.display = matches ? '' : 'none';
                if (matches) visibleCount++;
            });

            const headers = menuInner.querySelectorAll('.menu-header, .menu-divider');
            headers.forEach(h => {
                h.style.display = query.length > 0 ? 'none' : '';
            });

            if (noResults) {
                noResults.style.display = (visibleCount === 0 && query.length > 0) ? 'block' : 'none';
            }
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
                searchInput.focus();
            });
        }
    });
</script>
