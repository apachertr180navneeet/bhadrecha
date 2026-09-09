<nav class="layout-navbar container-fluid navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme admin-header"
	id="layout-navbar">
	<div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0">
		<a class="nav-item nav-link d-flex align-items-center justify-content-center" href="javascript:void(0)">
			<i class="bx bx-menu bx-sm"></i>
		</a>
	</div>

	<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
		<div class="navbar-nav align-items-center">
			<div class="nav-item admin-header-date">
				<i class="bx bx-calendar"></i>
				<span>{{ date('D') }} {{ date('d M Y') }}</span>
			</div>
		</div>

		<ul class="navbar-nav flex-row align-items-center ms-auto">
			<!-- Notification Bell Dropdown -->
			<li class="nav-item navbar-dropdown dropdown-notifications dropdown me-3">
				<a class="nav-link dropdown-toggle hide-arrow position-relative d-flex align-items-center justify-content-center" href="javascript:void(0);"
					data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="min-width: 44px; min-height: 44px;" id="header-notification-toggle" title="Expiring Alerts">
					<i class="bx bx-bell bx-sm text-secondary"></i>
					<span class="badge bg-danger rounded-pill badge-notifications position-absolute d-none" id="header-unread-count" style="top: 4px; right: 4px; font-size: 10px; padding: 2px 6px;">0</span>
				</a>
				<ul class="dropdown-menu dropdown-menu-end py-0 shadow-lg border-0" style="width: 380px; max-width: 92vw; z-index: 1080;">
					<li class="dropdown-menu-header border-bottom bg-light">
						<div class="dropdown-header d-flex align-items-center py-2 px-3">
							<h6 class="mb-0 me-auto fw-bold text-dark fs-7">
								<i class="bx bx-bell me-1 text-primary"></i> Expiring Alerts
							</h6>
							<button type="button" class="btn btn-xs btn-link text-muted p-0 text-decoration-none" id="header-mark-all-read-btn" title="Mark all as read">
								<i class="bx bx-check-double fs-6 me-1"></i> Mark All Read
							</button>
						</div>
					</li>
					<li class="dropdown-notifications-list" style="max-height: 350px; overflow-y: auto;">
						<ul class="list-group list-group-flush" id="header-notification-list">
							<li class="list-group-item text-center py-4 text-muted border-0">
								<div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
								<div class="small">Checking alerts...</div>
							</li>
						</ul>
					</li>
					<li class="dropdown-menu-footer border-top p-2 bg-light">
						<a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-primary w-100 py-1 fw-bold">
							<i class="bx bx-list-ul me-1"></i> View All Alerts
						</a>
					</li>
				</ul>
			</li>

			<li class="nav-item navbar-dropdown dropdown-user dropdown">
				<a class="nav-link dropdown-toggle hide-arrow d-flex align-items-center justify-content-center" href="javascript:void(0);"
					data-bs-toggle="dropdown" style="min-width: 44px; min-height: 44px;">
					<div class="avatar avatar-online">
						<img src="{{asset('assets/admin/img/avatars/1.png')}}" alt="admin" class="w-px-40 h-auto rounded-circle" />
					</div>
				</a>
				<ul class="dropdown-menu dropdown-menu-end">
					<li>
						<a class="dropdown-item" href="{{route('admin.profile')}}">
							<div class="d-flex">
								<div class="flex-shrink-0 me-3">
									<div class="avatar avatar-online">
										@if(!empty($user->avatar) && file_exists(public_path('/').$user->avatar))
		                                    <img src="{{asset($user->avatar)}}" alt="User Image" class="w-px-40 h-auto rounded-circle">
		                                @else
		                                    <img src="{{asset('assets/admin/img/avatars/1.png')}}"  alt="User Image" class="w-px-40 h-auto rounded-circle">
		                                @endif
									</div>
								</div>
								<div class="flex-grow-1">
									<span class="fw-medium d-block">{{Auth::user()->full_name}}</span>
									<small class="text-muted">{{ucfirst(Auth::user()->role)}}</small>
								</div>
							</div>
						</a>
					</li>
					<li>
						<div class="dropdown-divider"></div>
					</li>
					<li>
						<a class="dropdown-item" href="{{route('admin.profile')}}">
							<i class="bx bx-user me-2"></i>
							<span class="align-middle">My Profile</span>
						</a>
					</li>
					<li>
						<a class="dropdown-item" href="{{route('admin.change.password')}}">
							<i class="bx bx-key me-2"></i>
							<span class="align-middle">Change Password</span>
						</a>
					</li>
					<li>
						<div class="dropdown-divider"></div>
					</li>
					<li>
						<a class="dropdown-item" href="{{route('admin.logout')}}">
							<i class="bx bx-power-off me-2"></i>
							<span class="align-middle">Log Out</span>
						</a>
					</li>
				</ul>
			</li>
		</ul>
	</div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
	function loadHeaderNotifications() {
		fetch('{{ route("admin.notifications.unread") }}', {
			headers: {
				'X-Requested-With': 'XMLHttpRequest',
				'Accept': 'application/json',
			}
		})
		.then(res => res.json())
		.then(data => {
			const badge = document.getElementById('header-unread-count');
			const list = document.getElementById('header-notification-list');
			
			if (data.unread_count > 0) {
				badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
				badge.classList.remove('d-none');
			} else {
				badge.classList.add('d-none');
			}

			if (!data.notifications || data.notifications.length === 0) {
				list.innerHTML = `
					<li class="list-group-item text-center py-4 text-muted border-0">
						<i class="bx bx-check-circle fs-3 text-success mb-1"></i>
						<div class="small fw-semibold">All clear! No pending expiry alerts.</div>
					</li>
				`;
				return;
			}

			let html = '';
			data.notifications.forEach(item => {
				const isUnread = !item.read_at;
				let iconBg = 'bg-label-warning text-warning';
				let iconClass = 'bx bx-file';
				if (item.notification_type === 'eway_bill_expiry') {
					iconBg = 'bg-label-danger text-danger';
					iconClass = 'bx bx-receipt';
				} else if (item.notification_type === 'vehicle_document_expiry') {
					iconBg = 'bg-label-info text-info';
					iconClass = 'bx bx-car';
				} else if (item.notification_type === 'driver_document_expiry') {
					iconBg = 'bg-label-primary text-primary';
					iconClass = 'bx bx-id-card';
				}

				html += `
					<li class="list-group-item list-group-item-action dropdown-notifications-item p-2 border-bottom ${isUnread ? 'bg-light' : ''}" style="cursor: pointer;" onclick="handleHeaderNotifClick('${item.id}', '${item.url || ''}')">
						<div class="d-flex align-items-start gap-2">
							<div class="flex-shrink-0">
								<div class="avatar avatar-sm">
									<span class="avatar-initial rounded-circle ${iconBg}"><i class="${iconClass}"></i></span>
								</div>
							</div>
							<div class="flex-grow-1 overflow-hidden">
								<h6 class="mb-1 small fw-bold text-dark text-truncate">${item.title}</h6>
								<p class="mb-1 text-muted" style="font-size: 11px; line-height: 1.3;">${item.description}</p>
								<small class="text-muted" style="font-size: 10px;"><i class="bx bx-time-five me-1"></i>${item.time_ago}</small>
							</div>
							${isUnread ? '<span class="badge badge-dot bg-danger mt-1"></span>' : ''}
						</div>
					</li>
				`;
			});
			list.innerHTML = html;
		})
		.catch(err => {
			console.error('Failed to fetch notifications:', err);
		});
	}

	window.handleHeaderNotifClick = function(id, url) {
		fetch('{{ url("admin/notifications") }}/' + id + '/read', {
			method: 'POST',
			headers: {
				'X-CSRF-TOKEN': '{{ csrf_token() }}',
				'X-Requested-With': 'XMLHttpRequest',
				'Accept': 'application/json',
			}
		}).finally(() => {
			if (url && url !== '' && url !== 'null') {
				window.location.href = url;
			} else {
				loadHeaderNotifications();
			}
		});
	};

	const markAllBtn = document.getElementById('header-mark-all-read-btn');
	if (markAllBtn) {
		markAllBtn.addEventListener('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			fetch('{{ route("admin.notifications.mark-all-read") }}', {
				method: 'POST',
				headers: {
					'X-CSRF-TOKEN': '{{ csrf_token() }}',
					'X-Requested-With': 'XMLHttpRequest',
					'Accept': 'application/json',
				}
			}).then(() => {
				loadHeaderNotifications();
			});
		});
	}

	// Initial load
	loadHeaderNotifications();

	// Poll every 60 seconds
	setInterval(loadHeaderNotifications, 60000);
});
</script>
