
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
        document.querySelectorAll('.uri').forEach((element) => {
            element.addEventListener('input', (e) => {
                doi2pmh.citation.initTargets(e.target);
                doi2pmh.citation.resetFields();
                doi2pmh.citation.autocompleteCitation(
                    doi2pmh.citation.uriField.value
                );
            });
        });

        if (document.getElementById('doi_create_submit')){
            document.getElementById('doi_create_submit').addEventListener('click', (e) => {
                document.getElementById('doi_create_uri').value = doi2pmh.citation.transformDoiUri(document.getElementById('doi_create_uri').value)
            })
        }
    },

    initTargets: (target) => {
        let modal = target.closest('.modal-content')
        doi2pmh.citation.uriField = modal.querySelector('.uri')
        doi2pmh.citation.citationField = modal.querySelector('.citation')
        doi2pmh.citation.submitButton = modal.querySelector('.doi_submit')
        doi2pmh.citation.errorBlock = modal.querySelector('.invalid-feedback')
        doi2pmh.citation.spinner = modal.querySelector('.spinner')
    },

    autocompleteCitation: async (doiUri) => {
        if (doiUri.length > 0) {
            try {
                // Throw exception if is not a DOI URL
                doiUri = doi2pmh.citation.transformDoiUri(doiUri);

                doi2pmh.citation.submitButton.style.display = 'none';
                doi2pmh.citation.spinner.style.display = '';

                const response = await fetch(doiUri, {
                    headers: {
                        'Accept': 'text/x-bibliography; style=harvard-cite-them-right'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error ${response.status}`);
                }

                const result = await response.text();

                doi2pmh.citation.citationField.value = result;

            } catch (_) {
                doi2pmh.citation.formatInvalidUri();

            } finally {
                doi2pmh.citation.spinner.style.display = 'none';
                doi2pmh.citation.submitButton.style.display = '';
            }
        }
    },

    formatInvalidUri: () => {
        doi2pmh.citation.citationField.value = ''
        doi2pmh.citation.uriField.addClass('is-invalid')
        doi2pmh.citation.uriField.after(doi2pmh.citation.errorBlock)
        doi2pmh.citation.errorBlock.style.display = ''
        doi2pmh.citation.spinner.style.display = 'none'
        doi2pmh.citation.submitButton.style.display = ''
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
        doi2pmh.citation.citationField.value = ''
        doi2pmh.citation.uriField.classList.remove('is-invalid')
        doi2pmh.citation.errorBlock.style.display = 'none'
        doi2pmh.citation.spinner.style.display = 'none'
        doi2pmh.citation.submitButton.style.display = ''
    }
}

document.addEventListener("DOMContentLoaded", () => doi2pmh.citation.init());
