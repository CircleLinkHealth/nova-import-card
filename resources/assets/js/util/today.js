/**
 * @returns {String} today's date
 */
export function today () {
    const d = new Date()
    return `${d.getFullYear()}-${d.getMonth() + 1}-${d.getDate()}`
}