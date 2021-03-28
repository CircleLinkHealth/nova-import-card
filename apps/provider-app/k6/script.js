import http from 'k6/http';
import { check, group } from 'k6';

const baseUrl = "https://careplanmanager.com/";
const routes = {
    'login': 'login',
    'call-list': 'manage-patients/patient-call-list',
    'notes': 'manage-patients/326/notes',
    'demographics': 'manage-patients/326/careplan/demographics',
    'care-plan': 'manage-patients/326/view-careplan',
    'activities': 'manage-patients/326/activities',
    'profile': 'manage-patients/326/summary',
    'logout': 'auth/logout'
}

export const options = {
    userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 11_2_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.90 Safari/537.36',
    vus: 10,
    duration: '60s',
    thresholds: {
        'http_req_duration{url:https://careplanmanager.com/login}': ["avg<3000", "max<10000"],
        'http_req_duration{url:https://careplanmanager.com/manage-patients/patient-call-list}': ["max<10000", "avg<8000"],
        'http_req_duration{url:https://careplanmanager.com/manage-patients/326/careplan/demographics}': ["max<4000", "avg<3000"],
        'http_req_duration{url:https://careplanmanager.com/manage-patients/326/view-careplan}': ["max<5000", "avg<4000"],
        'http_req_duration{url:https://careplanmanager.com/manage-patients/326/activities}': ["max<3000", "avg<3000"],
        'http_req_duration{url:https://careplanmanager.com/manage-patients/326/summary}': ["max<4000", "avg<3000"],
        'http_req_duration{url:https://careplanmanager.com/manage-patients/326/notes}': ["max<4000", "avg<3000"]
    }
};

export default function () {
    let response;

    group('Login & View Patient Chart', function () {
        response = http.get(baseUrl + routes['login']);

        check(response, {
            'View Login': (r) => r.status === 200,
        });

        /*
        let vars = [];
        vars["_token"] = response
            .html()
            .find("input[name=_token]")
            .first()
            .attr("value");
         */

        response = response.submitForm({
            formSelector: 'form',
            fields: { email: 'pagcosma nurse', password: '78Z43ZGsZ9p5!@#AA' },
        });

        check(response, {
            'POST Login': (r) => r.status === 200 && r.url === (baseUrl + routes['call-list'])
        });

        goToPage('View Notes', routes['notes']);
        goToPage('View Demographics', routes['demographics']);
        goToPage('View CarePlan', routes['care-plan']);
        goToPage('View Patient Profile', routes['profile']);
        goToPage('View Activity Report', routes['activities']);

        response = http.get(
            baseUrl + routes['logout'],
        );

        check(response, {
            'Logout': (r) => r.status === 200,
        });
    });
}

function goToPage(description, url) {
    const response = http.get(
        baseUrl + url,
    );

    check(response, {
        [description]: (r) => r.status === 200 && r.url === (baseUrl + url)
    });
}
