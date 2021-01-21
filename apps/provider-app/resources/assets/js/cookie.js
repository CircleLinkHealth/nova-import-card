export const setCookie = (name, value) => {
    document.cookie = name + '=' + value
}

export const readCookie = (name) => {
    var nameEQ = encodeURIComponent(name) + "=";
    var cookies = document.cookie.split(';').map(item => item.trim().split('='));
	const cookieValue = (cookies.find(cookie => cookie[0] === name && !!cookie[1]) || [])[1];
	if (cookieValue) return decodeURIComponent(cookieValue)
	else return null
}