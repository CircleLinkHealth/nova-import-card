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
        //if smoking (goal.type === 3) then show 0 instead of N/A
        goal.end = () => (goal.type === 3 ? goal.info.target : (goal.info.target == '0') ? 'N/A' : (goal.info.target || 'N/A'))
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
            Object.defineProperty(goal.info, 'verb', {
                get () {
                    const start = (goal.start().split('/')[0] || 0)
                    const end = (goal.end().split('/')[0] || 0)

                    if (parseInt(start) > 130) {
                        return 'Decrease'
                    }
                    else if (!goal.info.starting || (parseInt(start) > 80 && parseInt(start) <= 130)) {
                        return 'Regulate'
                    }
                    else {
                        return 'Increase'
                    }
                }
            })
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
            Object.defineProperty(goal.info, 'verb', {
                get () {
                    const start = (goal.start().split('/')[0] || 0)
                    const end = (goal.end().split('/')[0] || 0)

                    if (goal.info.starting == 'N/A' || goal.info.target == 'TBD' || !goal.info.starting || parseInt(start) < 130) {
                        return 'Regulate'
                    }
                    else if (parseInt(start) >= 130) {
                        return 'Decrease'
                    }
                }
            })
        }
        else {
            Object.defineProperty(goal.info, 'verb', {
                get () {
                    let start = (goal.start().split('/')[0] || 0)
                    let end = (goal.end().split('/')[0] || 0)

                    if (!goal.info.starting || goal.info.starting == 'N/A' || !goal.info.target || (goal.name == 'Weight' && (goal.info.target == '0'))) {
                        return 'Regulate'
                    }

                    start = parseInt(start)
                    end = parseInt(end)

                    if (start > end) {
                        return 'Decrease'
                    }

                    if (start < end) {
                        return  'Increase'
                    }
                    else {
                        return 'Regulate'
                    }

                }
            })
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

        goal.start = () => (goal.info.starting || 'N/A')
        //if smoking (goal.type === 3) then show 0 instead of N/A
        goal.end = () => (goal.type === 3 ? goal.info.target : (goal.info.target == '0') ? 'N/A' : (goal.info.target || 'N/A'))
        goal.active = () => !!(goal.info.starting && goal.info.target)
    }

    const text = JSON.stringify(goal)
    goal.isModified = () => text != JSON.stringify(goal)

    return goal
}

export default transformHealthGoal
