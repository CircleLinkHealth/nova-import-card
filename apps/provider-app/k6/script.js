import http from 'k6/http';
import {check, group} from 'k6';

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
        'http_req_duration{name:ViewLogin}': ["avg<3000", "max<10000"],
        'http_req_duration{name:PostLogin}': ["max<10000", "avg<8000"],
        'http_req_duration{name:ViewDemographics}': ["max<4000", "avg<3000"],
        'http_req_duration{name:ViewCarePlan}': ["max<5000", "avg<4000"],
        'http_req_duration{name:ViewActivityReport}': ["max<3000", "avg<3000"],
        'http_req_duration{name:ViewPatientProfile}': ["max<4000", "avg<3000"],
        'http_req_duration{name:ViewNotes}': ["max<4000", "avg<3000"]
    }
};

export default function () {
    let response;

    group('Login & View Patient Chart', function () {
        response = http.get(baseUrl + routes['login'], {
            tags: {name: 'ViewLogin'}
        });

        check(response, {
            'ViewLogin': (r) => r.status === 200,
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
            fields: {email: 'pagcosma nurse', password: '78Z43ZGsZ9p5!@#AA'},
        }, {
            tags: {name: 'PostLogin'}
        });

        check(response, {
            'PostLogin': (r) => r.status === 200 && r.url === (baseUrl + routes['call-list'])
        });

        goToPage('ViewNotes', routes['notes']);
        goToPage('ViewDemographics', routes['demographics']);
        goToPage('ViewCarePlan', routes['care-plan']);
        goToPage('ViewPatientProfile', routes['profile']);
        goToPage('ViewActivityReport', routes['activities']);

        response = http.get(baseUrl + routes['logout'], {
            tags: {name: 'Logout'}
        });

        check(response, {
            'Logout': (r) => r.status === 200,
        });
    });
}

function goToPage(description, url) {
    const response = http.get(baseUrl + url, {
        tags: {name: description}
    });

    check(response, {
        [description]: (r) => r.status === 200 && r.url === (baseUrl + url)
    });
}
