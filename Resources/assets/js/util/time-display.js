import { pad } from '../../../../../../resources/assets/js/util/pad'

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