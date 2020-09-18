/**
 * Helper JS for resolving Days of the Week for CLH
 * 
 * Monday => 1
 * Tues, Wed ...
 * Sunday => 7
 * 
 * This is done this way because of how it was written on the laravel backend
 */

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
        const pickCount = (['T', 'S'].indexOf(day[0]) >= 0) ? 2 : 1;

        return day.slice(0, pickCount)
    }

    return ''
}