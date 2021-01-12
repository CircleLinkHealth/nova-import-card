import API from '../../page-objects/API';

describe('Tests API Status Code 200 for SuperAdmin Nova Pages', () => {
	const api = new API();

	it('All Users', () => {
		api.validateStatusCode200('/superadmin/resources/users');
	});
	it('Bonuses', () => {
		api.validateStatusCode200('/superadmin/resources/nurse-invoice-extras');
	});
	it('Invoice Daily Disputes', () => {
		api.validateStatusCode200(
			'/superadmin/resources/invoice-daily-disputes-approvals'
		);
	});
	it('Invoice Disputes', () => {
		api.validateStatusCode200('/superadmin/resources/disputes');
	});
	it('Invoices', () => {
		api.validateStatusCode200('/superadmin/resources/nurse-invoices');
	});
	it('Notes', () => {
		api.validateStatusCode200('/superadmin/resources/notes');
	});
	it('Nurse-Patient Assignment', () => {
		api.validateStatusCode200('/superadmin/resources/nurse-patients');
	});
	it('Pay Details', () => {
		api.validateStatusCode200('/superadmin/resources/nurses');
	});
	it('Report Settings', () => {
		api.validateStatusCode200('/superadmin/resources/report-settings');
	});
	it('Time Tracking', () => {
		api.validateStatusCode200('/superadmin/resources/time-trackers');
	});
	it('Users', () => {
		api.validateStatusCode200('/superadmin/resources/care-coach-users');
	});
	it('CCDAs', () => {
		api.validateStatusCode200('/superadmin/resources/ccdas');
	});
	it('Care Ambassador Scripts', () => {
		api.validateStatusCode200('/superadmin/resources/care-ambassador-scriptss');
	});
	it('Enrollees', () => {
		api.validateStatusCode200('/superadmin/resources/enrollees');
	});
	it('Invitations Dashboard', () => {
		api.validateStatusCode200(
			'/superadmin/resources/self-enrollment-metrics-resources'
		);
	});
	it('Non Responsive Enrollees', () => {
		api.validateStatusCode200('/superadmin/resources/non-responsive-enrollees');
	});
	it('Patients Update Information', () => {
		api.validateStatusCode200('/superadmin/resources/enrolee-statuses');
	});
	it('SMS', () => {
		api.validateStatusCode200('/superadmin/resources/outgoing-sms');
	});
	it('Supplemental Data', () => {
		api.validateStatusCode200(
			'/superadmin/resources/supplemental-patient-data-resources'
		);
	});
	it('Upload Patient Preferences', () => {
		api.validateStatusCode200(
			'/superadmin/resources/patient-contact-preferences'
		);
	});
	it('Browsers', () => {
		api.validateStatusCode200('/superadmin/resources/browsers');
	});
	it('CPM Problem Instruction', () => {
		api.validateStatusCode200('/superadmin/resources/cpm-instructables');
	});
	it('CPM Problems', () => {
		api.validateStatusCode200('/superadmin/resources/cpm-problems');
	});
	it('Customer Notification Contact Time Preferences', () => {
		api.validateStatusCode200(
			'/superadmin/resources/customer-notification-contact-time-preferences'
		);
	});
	it('Direct Mail Messages', () => {
		api.validateStatusCode200('/superadmin/resources/direct-mail-messages');
	});
	it('Eligibility Checks', () => {
		api.validateStatusCode200('/superadmin/resources/eligibility-checks');
	});
	it('G2065 Eligible', () => {
		api.validateStatusCode200(
			'/superadmin/resources/commonwealth-p-c-m-eligibles'
		);
	});
	it('Importer Problem Codes', () => {
		api.validateStatusCode200('/superadmin/resources/importer-problem-codes');
	});
	it('Notifications', () => {
		api.validateStatusCode200('/superadmin/resources/notifications');
	});
	it('Provider Details', () => {
		api.validateStatusCode200('/superadmin/resources/provider-infos');
	});
	it('Twilio Calls', () => {
		api.validateStatusCode200('/superadmin/resources/twilio-calls');
	});
	it('Allergies', () => {
		api.validateStatusCode200('/superadmin/resources/practice-pull-allergies');
	});
	it('Demographics', () => {
		api.validateStatusCode200(
			'/superadmin/resources/practice-pull-demographics'
		);
	});
	it('Medications', () => {
		api.validateStatusCode200(
			'/superadmin/resources/practice-pull-medications'
		);
	});
	it('Problems', () => {
		api.validateStatusCode200('/superadmin/resources/practice-pull-problems');
	});
	it('Notes Success Stories', () => {
		api.validateStatusCode200('/superadmin/resources/notes-success-stories');
	});
	it('Practices', () => {
		api.validateStatusCode200('/superadmin/resources/practices');
	});
	it('Staff Members', () => {
		api.validateStatusCode200('/superadmin/resources/practice-staffs');
	});
	it('CPM Configuration', () => {
		api.validateStatusCode200('/superadmin/resources/app-configs');
	});
	it('Database Logs', () => {
		api.validateStatusCode200('/superadmin/resources/database-logs');
	});
});
