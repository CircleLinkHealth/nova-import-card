export const DayOfWeek = {
    1: 'Monday',
    2: 'Tuesday',
    3: 'Wednesday',
    4: 'Thursday',
    5: 'Friday',
    6: 'Saturday',
    7: 'Sunday'
}

export const ShortDayOfWeek = (code) => {
    const day = DayOfWeek[code];

    if (day) {
        const pickCount = day[0] === 'T' ? 2 : 1;

        return day.slice(0, pickCount)
    }

    return ''
}