export default (App, VueDropZone) => {
    VueDropZone.$on('vdropzone-success', (file, response) => {
        console.log("vdropzone-success", file, response)
    })

    VueDropZone.$on('vdropzone-error', (file, message, xhr) => {
        console.log("vdropzone-error", file, message, xhr)
    })
}