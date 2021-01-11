export const BILLING_PROVIDER = 'billing_provider';
export const REGULAR_DOCTOR = 'regular_doctor';
export const MEMBER = 'member';
export const RELATION_VALID_DROPDOWN_OPTIONS = [BILLING_PROVIDER, REGULAR_DOCTOR];
export const relationToPatientOptions = {
    minimumResultsForSearch: -1, //hide search input
    data: [
        {id: MEMBER, text: 'Care Team Member'},
        {id: REGULAR_DOCTOR, text: 'Regular Dr.'},
        {id: BILLING_PROVIDER, text: 'Billing Dr.'}
    ]
};

export function checkCareTeamRelations(patientCareTeam, formData) {

    let hasError = false;
    let hasWarning = false;
    let message = null;

    //check if we have two billing providers
    //if we do, we have to show a warning and let
    //the user know that we will set only this billing provider

    //check if we do not have a billing provider
    //if we do not have, we let the user know,
    //that they must edit the care team, choose a different
    //billing provider and then edit this member

    const billingProviderMembers = patientCareTeam.filter(x => x.id !== formData.id && x.type === BILLING_PROVIDER);
    //exactly 2
    if (billingProviderMembers.length === 1 && formData.typeForDropdown === BILLING_PROVIDER) {
        hasWarning = true;
        const bProviderName = billingProviderMembers[0].user.full_name;
        const name = formData.user.first_name + ' ' + formData.user.last_name;
        message = `Dr. ${bProviderName} is currently listed as the Billing Dr. for this patient. If you continue, you will appoint Dr. ${name} as the Billing Dr. for this patient.`;
    }
    else if (billingProviderMembers.length === 0 && formData.typeForDropdown !== BILLING_PROVIDER) {
        hasError = true;
        message = "A care team must have at least one Billing Dr. Please select the new Billing Dr. from the care team to un-assign this member.";
    }

    //check if we already have a regular doctor. if we do, show warning
    if (!message) {
        const regularDoctorMembers = patientCareTeam.filter(x => x.id !== formData.id && x.type === REGULAR_DOCTOR);
        if (regularDoctorMembers.length === 1 && formData.typeForDropdown === REGULAR_DOCTOR) {
            hasWarning = true;
            const regularDrName = regularDoctorMembers[0].user.full_name;
            const name = formData.user.first_name + ' ' + formData.user.last_name;
            message = `Dr. ${regularDrName} is currently listed as the Regular Dr. for this patient. If you continue, you will appoint Dr. ${name} as the Regular Dr. for this patient.`;
        }
    }

    return {
        hasError,
        hasWarning,
        message
    }
}