export interface TimeoutOverrideOptions {
    logoutTimeoutCallMode?: number;
    alertTimeoutCallMode?: number;
    logoutTimeout?: number;
    alertTimeout?: number;
}

export interface PatientChargeableService {
    patient_user_id?: number;
    total_time: number;
    chargeable_service: {
        id: number;
        code: string;
        display_name: string;
    }
}

export interface TimeTrackerInfo {
    activity: string;
    isFromCaPanel: boolean;
    providerId: string | number;
    patientId: string | number;
    submitUrl: string;
    timeSyncUrl: string;
    programId: string | number;
    ipAddr: string;
    chargeableServices: PatientChargeableService[];
    noLiveCount: boolean;
    patientFamilyId: string | number;
    enrolleeId: string | number;
    initSeconds: number;
    chargeableServiceId: number;
    modify?: boolean;
    modifyFilter?: string;
    startTime: string;
    forceSkip?: boolean;
}

export interface Activity {
    isActive: boolean;
    enrolleeId?: string | number;
    name: string;
    start_time: string;
    url_short: string;
    url: string;
    duration: number;
    title: string;
    chargeableServiceId?: number;
    sockets: any[];
    callMode: boolean;
    forceSkip: boolean;
    isInActiveModalShown: boolean;
    inactiveModalShowTime: number;
}

export interface TimeEntity {
    chargeable_service_id: number;
    time: number;
}

export interface UsersTimeCollection {
    [key: string]: TimeEntity[];
}
