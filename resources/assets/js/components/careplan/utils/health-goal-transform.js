const transformHealthGoal = (goal) => {
    goal.created_at = new Date(goal.created_at)
    goal.updated_at = new Date(goal.updated_at)
    goal.enabled = goal.enabled || false

    if (goal.info) {
        goal.info.created_at = new Date(goal.info.created_at)
        goal.info.updated_at = new Date(goal.info.updated_at)
        goal.info.monitor_changes_for_chf = goal.info.monitor_changes_for_chf || false
        if (goal.info.starting === '0') {
            goal.info.starting = ''
        }
        goal.start = () => (goal.info.starting || 'N/A')
        goal.end = () => (goal.info.target || '0')
        goal.active = () => !!(goal.info.starting && goal.info.target)
        
        const start = (goal.start().split('/')[0] || 0)
        const end = (goal.end().split('/')[0] || 0)

        if (!goal.name) {
            throw new Error('the [name] property is required')
        }

        if ((goal.name === 'Blood Sugar')) {
            goal.info.target = goal.info.target || '120'
            if (goal.info.target === '0') {
                goal.info.target = '120'
            }
            goal.info.high_alert = (Number(goal.info.high_alert) || '350') + ''
            goal.info.low_alert = (Number(goal.info.low_alert) || '60') + ''
            if (start > 130) {
                goal.info.verb = 'Decrease'
            }
            else if (!goal.info.starting || (start > 80 && start <= 130)) {
                goal.info.verb = 'Regulate'
            }
            else {
                goal.info.verb = 'Increase'
            }
        }
        else if (goal.name === 'Blood Pressure') {
            goal.info.target = goal.info.target || '130/80'
            if (goal.info.target === '0') {
                goal.info.target = '130/80'
            }
            goal.info.systolic_high_alert = (Number(goal.info.systolic_high_alert) || '180') + ''
            goal.info.systolic_low_alert = (Number(goal.info.systolic_low_alert) || '80') + ''
            goal.info.diastolic_high_alert = (Number(goal.info.diastolic_high_alert) || '90') + ''
            goal.info.diastolic_low_alert = (Number(goal.info.diastolic_low_alert) || '40') + ''
            if (goal.info.starting == 'N/A' || goal.info.target == 'TBD' || !goal.info.starting || start < 130) {
                goal.info.verb = 'Regulate'
            }
            else if (start >= 130) {
                goal.info.verb = 'Decrease'
            }
        }
        else {
            if (start > end) {
                goal.info.verb = 'Decrease'
            }
            else {
                if (start > 0 && start < end) {
                    goal.info.verb = 'Increase'
                }
                else {
                    goal.info.verb = 'Regulate'
                }
            }
        }
    }
    else {
        goal.info = {
            starting: null,
            target: 0
        }
        if (goal.type === 0) {
            goal.info.monitor_changes_for_chf = 0
        }
        else if (goal.type === 1) {
            goal.info.systolic_high_alert = 180
            goal.info.systolic_low_alert = 80
            goal.info.diastolic_high_alert = 90
            goal.info.diastolic_low_alert = 40
            goal.info.target = '130/80'
        }
        else if (goal.type === 2) {
            goal.info.high_alert = 350
            goal.info.low_alert = 60
            goal.info.starting_a1c = 0
            goal.info.target = '120'
        }
    }
    return goal
}

export default transformHealthGoal