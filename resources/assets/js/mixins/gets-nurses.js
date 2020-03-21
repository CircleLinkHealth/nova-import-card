import {rootUrl} from "../app.config";

export default {
    data() {
        return {
            loaders: {
                nurses: false,
            },
            nurses: [],
        }
    },
    methods: {
        getNurses(cache = false) {
            this.loaders.nurses = true
            return (cache ? this.cache().get(rootUrl('api/nurses?compressed')) : this.axios.get(rootUrl('api/nurses?compressed'))).then(response => {
                const pagination = (response || {}).data
                this.nurses = ((pagination || {}).data || []).filter(nurse => nurse.practices).map(nurse => {

                    const roles = nurse.user.roles.map(r => r.name);
                    const rolesSet = Array.from(new Set(roles));

                    let displayName = (nurse.user || {}).display_name || '';
                    const suffix = (nurse.user || {}).suffix;
                    if (suffix) {
                        const suffixPos = displayName.indexOf(suffix);
                        if (suffixPos === -1 || suffixPos + suffix.length !== displayName.length) {
                            displayName = `${displayName} ${suffix}`;
                        }
                    }
                    if (roles.includes('care-center-external')) {
                        displayName = displayName + ' (in-house)';
                    }

                    return {
                        id: nurse.user_id,
                        nurseId: nurse.id,
                        roles: rolesSet,
                        display_name: displayName,
                        states: nurse.states,
                        practiceId: (nurse.user || {}).program_id,
                        practices: (nurse.practices || [])
                    }
                })
                //console.log('calls:nurses', pagination)
                this.loaders.nurses = false
                return this.nurses
            }).catch(err => {
                console.error('calls:nurses', err)
                this.loaders.nurses = false
            })
        },
    },
}
