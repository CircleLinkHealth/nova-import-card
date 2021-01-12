import API from '../../page-objects/API';

describe('Tests Nurse Endpoints API Status Code = 200', () => {
	const api = new API();

	it('Patient Activities', () => {
		api.validateStatusCode200('/manage-patients/patient-call-list');
	});

	it('Schedule', () => {
		api.validateStatusCode200('/care-center/work-schedule');
	});

	it('Offline Activity Time Requests', () => {
		api.validateStatusCode200(
			'/manage-patients/offline-activity-time-requests'
		);
	});

	it('Hours Pay Invoice', () => {
		api.validateStatusCode200('/nurseinvoices/review');
	});

	it('Email Subscriptions', () => {
		api.validateStatusCode200('/notification-subscriptions-dashboard');
	});
});
