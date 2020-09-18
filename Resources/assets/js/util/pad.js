/**
 * @param {Number} num value to be padded
 * @param {Number} count padding places
 */
export const pad = (num, count = 0) => {
    const $num = num + ''
    return '0'.repeat(Math.max(count - $num.length, 0)) + $num;
}