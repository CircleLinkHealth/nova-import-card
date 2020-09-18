import EventBus from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/admin/time-tracker/comps/event-bus'

export default (App, VueDropZone) => {
    VueDropZone.$on('vdropzone-success', (file, response) => {
        console.log("vdropzone-success", file, response)

        EventBus.$emit('notifications:create', {
            message: `The CCDA(s) were successfully uploaded. We'll let you know once they've been processed.`,
            type: 'success',
            noTimeout: true
        })

        EventBus.$emit('vdropzone:success', response)
    })

    VueDropZone.$on('vdropzone-error', (file, data, xhr) => {
        console.error("vdropzone-error", file, data, xhr)

        EventBus.$emit('notifications:create', {
            message: `An error occurred in processing ccda`,
            type: 'warning',
            noTimeout: true
        })


        if (!!(data || {}).message) {
            const messageElem = (file.previewElement || document.createElement('div'))
                        .querySelector('[data-dz-errormessage]') || document.createElement('span')

            messageElem.innerText = (data || {}).message
        }
    })

    EventBus.$on('vdropzone:remove-all-files', () => VueDropZone.removeAllFiles())
}