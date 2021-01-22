import API from '../../page-objects/API';

describe('Tests Admin Endpoints API Status Code = 200', () => {
	const api = new API();

	it('Login Page', () => {
		api.validateStatusCode200('/');
	});

	it('Admin Homepage - Provider View', () => {
		api.validateStatusCode200('/manage-patients/dashboard');
	});

	it('SuperAdmin Nova Dashboard', () => {
		api.validateStatusCode200('/superadmin/dashboards/main');
	});

	it('CA Director Panel', () => {
		api.validateStatusCode200('/admin/ca-director');
	});

	it('Eligibility Processing Panel', () => {
		api.validateStatusCode200('/eligibility/admin/batches');
	});

	it('EHR Report Writer', () => {
		api.validateStatusCode200('/ehr-report-writer/index');
	});

	it('Manage CPM Problems', () => {
		api.validateStatusCode200('/admin/settings/manage-cpm-problems/index');
	});

	it('Medication Group Map', () => {
		api.validateStatusCode200('/admin/medication-groups-maps');
	});

	it('Manage Report Settings', () => {
		api.validateStatusCode200('/admin/report-settings');
	});

	it('Add New Practice', () => {
		api.validateStatusCode200('/saas/admin/practices/create');
	});

	it('Manage Practices', () => {
		api.validateStatusCode200('/saas/admin/practices');
	});

	it('Practice Billing', () => {
		api.validateStatusCode200('/admin/practice/billing/create');
	});

	it('CCD Importer', () => {
		api.validateStatusCode200('/ccd-importer?v3');
	});

	it('Approve Billable Patients', () => {
		api.validateStatusCode200('/admin/reports/monthly-billing/v2/make');
	});

	it('Unreachable Patients Export', () => {
		api.validateStatusCode200('/admin/excelReportUnreachablePatients');
	});

	it('Print Paused Patients Letters', () => {
		api.validateStatusCode200('/admin/patients/letters/paused');
	});

	it('Ops Dashboard', () => {
		api.validateStatusCode200('/ops-dashboard/index');
	});

	it('Nurse Performance Report', () => {
		api.validateStatusCode200('/reports/nurse/weekly');
	});

	it('Enrollee List', () => {
		api.validateStatusCode200('/admin/enrollment/list');
	});

	it('Care Ambassador KPIs', () => {
		api.validateStatusCode200('/admin/enrollment/ambassador/kpis');
	});

	it('Practice Enrollment KPIs', () => {
		api.validateStatusCode200('/admin/enrollment/practice/kpis');
	});

	it('Offline Activity Time Requests', () => {
		api.validateStatusCode200('/admin/offline-activity-time-requests');
	});

	it('Nurses Schedules', () => {
		api.validateStatusCode200('/admin/nurses/windows');
	});

	it('Nurses Daily Report', () => {
		api.validateStatusCode200('/admin/reports/nurse/daily');
	});

	it('Patient Activity Management', () => {
		api.validateStatusCode200('/admin/calls-v2');
	});

	it('Families', () => {
		api.validateStatusCode200('/admin/families');
	});

	it('Patient List', () => {
		api.validateStatusCode200('/manage-patients/listing');
	});

	it('Patient CarePlan Print List', () => {
		api.validateStatusCode200('/manage-patients/careplan-print-list');
	});

	it('All Patient Notes', () => {
		api.validateStatusCode200('/manage-patients/provider-notes');
	});

	it('Under 20 Minute Report', () => {
		api.validateStatusCode200('/manage-patients/u20');
	});
});
