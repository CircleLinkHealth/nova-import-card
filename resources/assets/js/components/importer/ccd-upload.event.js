export default (App, VueDropZone) => {
    VueDropZone.$on('vdropzone-success', (file, response) => {
        console.log("vdropzone-success", file, response)
    })

    VueDropZone.$on('vdropzone-error', (file, data, xhr) => {
        console.log("vdropzone-error", file, data, xhr)

        if (!!(data || {}).message) {
            const messageElem = (file.previewElement || document.createElement('div'))
                        .querySelector('[data-dz-errormessage]') || document.createElement('span')

            messageElem.innerText = (data || {}).message
        }
    })
}