class Errors {
    constructor() {
        this.errors = {}
    }

    get(field) {
        if (this.errors[field]) {
            return this.errors[field][0]
        }
    }

    setErrors(errors) {
        this.errors = errors
    }

    clear(field) {
        delete this.errors[field]
    }
}

