
/**
 * Init autocomplete citation with doi uri
 * @type {*|{}}
 */
window.doi2pmh = window.doi2pmh || {};
doi2pmh.citation = window.doi2pmh.citation || {
    uriField: null,
    citationField: null,
    submitButton: null,
    errorBlock: null,
    spinner: null,

    init: () => {
        doi2pmh.citation.initAutocompleteCitation()
    },

    initAutocompleteCitation: () => {
        $('.uri').on('input', (e) => {
            doi2pmh.citation.initTargets(e.target)
            doi2pmh.citation.resetFields()
            doi2pmh.citation.autocompleteCitation(doi2pmh.citation.uriField.val())
        })

        if (document.getElementById('doi_create_submit')){
            document.getElementById('doi_create_submit').addEventListener('click', (e) => {
                document.getElementById('doi_create_uri').value = doi2pmh.citation.transformDoiUri(document.getElementById('doi_create_uri').value)
            })
        }
    },

    initTargets: (target) => {
        let modal = $(target.closest('.modal-content'))
        doi2pmh.citation.uriField = modal.find('.uri')
        doi2pmh.citation.citationField = modal.find('.citation')
        doi2pmh.citation.submitButton = modal.find('.doi_submit')
        doi2pmh.citation.errorBlock = modal.find('.invalid-feedback')
        doi2pmh.citation.spinner = modal.find('.spinner')
    },

    autocompleteCitation: (doiUri) => {
        if (doiUri.length > 0) {
            try {
                // Throw exception if is not a doi url
                doiUri = doi2pmh.citation.transformDoiUri(doiUri)
                doi2pmh.citation.submitButton.hide()
                doi2pmh.citation.spinner.show()
                $.get(
                    {
                        url: doiUri,
                        success: (result) => {
                            doi2pmh.citation.citationField.val(result)
                        },
                        error: () => {
                            doi2pmh.citation.formatInvalidUri()
                        },
                        beforeSend: (xhr) => xhr.setRequestHeader('Accept', 'text/x-bibliography; style=harvard-cite-them-right'),
                    }
                ).done(() => {doi2pmh.citation.spinner.hide(); doi2pmh.citation.submitButton.show()})
            } catch (_) {
                doi2pmh.citation.formatInvalidUri()
            }
        }
    },

    formatInvalidUri: () => {
        doi2pmh.citation.citationField.val('')
        doi2pmh.citation.uriField.addClass('is-invalid')
        doi2pmh.citation.uriField.after(doi2pmh.citation.errorBlock)
        doi2pmh.citation.errorBlock.show()
        doi2pmh.citation.spinner.hide();
        doi2pmh.citation.submitButton.show()
    },

    transformDoiUri: (doiUri) => {
        const path = doiUri.match(/(10.*)$/);

        if (!path || !path[0]) {
            throw "Bad url"
        }

        const url = new URL("https://doi.org");
        url.pathname = path[0];
        return url.toString();
    },

    resetFields: () => {
        doi2pmh.citation.citationField.val('')
        doi2pmh.citation.uriField.removeClass('is-invalid')
        doi2pmh.citation.errorBlock.hide()
        doi2pmh.citation.spinner.hide();
        doi2pmh.citation.submitButton.show()
    }
}

document.addEventListener("DOMContentLoaded", () => doi2pmh.citation.init());
