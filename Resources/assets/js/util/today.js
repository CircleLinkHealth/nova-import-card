import { pad } from '../../../../../../resources/assets/js/util/pad'

/**
 * @returns {String} today's date
 */
export function today () {
    const d = new Date()
    return `${d.getFullYear()}-${pad(d.getMonth() + 1, 2)}-${pad(d.getDate(), 2)}`
}