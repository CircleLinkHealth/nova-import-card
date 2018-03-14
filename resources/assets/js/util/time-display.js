export const pad = (num, count = 0) => {
    const $num = num + ''
    return '0'.repeat(Math.max(count - $num.length, 0)) + $num;
}

export const hours = (ss) => pad(Math.floor(ss / 3600), 1)

export const minutes = (ss) => pad((Math.floor(ss / 60) % 60), 2)

export const seconds = (ss) => pad(ss % 60, 2)

export default (ss = 0) => {
    return `${hours(ss)}:${minutes(ss)}:${seconds(ss)}`
}