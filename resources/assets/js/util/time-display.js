/**
 * @param {Number} num value to be padded
 * @param {Number} count padding places
 */
export const pad = (num, count = 0) => {
    const $num = num + ''
    return '0'.repeat(Math.max(count - $num.length, 0)) + $num;
}

/**
 * @param {Number} ss seconds value
 */
export const hours = (ss) => pad(Math.floor(ss / 3600), 1)

/**
 * @param {Number} ss seconds value
 */
export const minutes = (ss) => pad((Math.floor(ss / 60) % 60), 2)

/**
 * @param {Number} ss seconds value
 */
export const seconds = (ss) => pad(ss % 60, 2)

/**
 * @param {Number} ss seconds value
 */
export default (ss = 0) => {
    return `${hours(ss)}:${minutes(ss)}:${seconds(ss)}`
}