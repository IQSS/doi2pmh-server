
// noinspection JSUnresolvedVariable

/**
 * Init autocomplete user with email
 * @type {*|{}}
 */
window.doi2pmh = window.doi2pmh || {};
doi2pmh.user = window.doi2pmh.user || {

    baseUrl: `/${doi2pmh.params.locale}/admin/user/autocomplete/`,
    baseUrlToken: `/${doi2pmh.params.locale}/admin/user/apiToken`,
    userInput: null,
    apiTokenInput: null,
    currentFocus: -1,

    init: () => {
        doi2pmh.user.initAutocompleteUser()
        doi2pmh.user.initEditAdmin()
        doi2pmh.user.initApiToken()
    },

    /**
     * Add event for autocompletion
     */
    initAutocompleteUser: () => {
        doi2pmh.user.userInput = document.getElementById('user_email')

        if (!doi2pmh.user.userInput) {
            return
        }

        doi2pmh.user.userInput.addEventListener('input', doi2pmh.user.handleAutocompleteUser)
    },

    handleAutocompleteUser: (event) => {
        clearTimeout(doi2pmh.user.timeoutFunction)

        doi2pmh.user.timeoutFunction = setTimeout(
            () => {
                doi2pmh.user.autocomplete(event.target.value)
                doi2pmh.user.addShortcut()
            },
            300
        );

    },

    /**
     * Send xhr request, and show the results in container
     * @param inputValue
     */
    autocomplete: (inputValue) => {
        let xhr = new XMLHttpRequest();
        let container = doi2pmh.user.createContainer()
        xhr.onreadystatechange = () => {

            // Check correct response.
            if (xhr.readyState !== 4 || xhr.status !== 200 || !xhr.responseText) {
                return;
            }

            /**
             * @type [{email: string}]
             */
            let results = JSON.parse(xhr.responseText);

            if (results.length === 0){
                if (document.getElementById('noResults')) {
                    doi2pmh.user.cleanupSuggestions()
                    return
                }
                doi2pmh.user.cleanupSuggestions()
                let small = document.createElement('small')
                small.setAttribute('class', 'text-info')
                small.setAttribute('id', 'noResults')
                small.innerHTML = doi2pmh.translations['admin.user.noResults']
                doi2pmh.user.userInput.parentElement.appendChild(small)
                return
            }
            doi2pmh.user.cleanupSuggestions()
            results.forEach((result) => {
                let item = doi2pmh.user.createItem(result.email)

                item.addEventListener('click', (e) => {
                    doi2pmh.user.userInput.value = e.target.innerHTML
                    doi2pmh.user.cleanupSuggestions()
                    console.log(doi2pmh.user.currentFocus)
                })
                container.appendChild(item)
            })
            doi2pmh.user.userInput.parentElement.appendChild(container)
        }

        xhr.open("GET", `${doi2pmh.user.baseUrl}${inputValue}` ,true);
        xhr.send();
    },

    /**
     * Create the suggestions container
     * @returns {HTMLDivElement}
     */
    createContainer: () => {
        const div = document.createElement('div')
        div.setAttribute('id', 'autocomplete-list')
        div.setAttribute('class', 'autocomplete-items')
        return div
    },

    /**
     * Create a suggestion item with its value
     * @param value
     * @returns {HTMLDivElement}
     */
    createItem: (value) => {
        const item = document.createElement('div')
        item.innerHTML = value
        return item
    },

    /**
     * Remove all lists
     */
    cleanupSuggestions: () => {
        [...document.getElementsByClassName('autocomplete-items')].forEach((list) => list.remove())
        doi2pmh.user.userInput.parentNode.querySelector('#noResults') && doi2pmh.user.userInput.parentNode.querySelector('#noResults').remove()
        doi2pmh.user.currentFocus = -1
    },

    /**
     * Add arrow keys keyboard to select an item
     */
    addShortcut: () => {
        doi2pmh.user.userInput.addEventListener('keydown', (e) => {

            let items = document.getElementById('autocomplete-list')
            if (items) items = items.getElementsByTagName('div')

            // If the arrow down key is pressed
            if (e.keyCode === 40) {
                doi2pmh.user.currentFocus ++
                doi2pmh.user.addActive(items)

            // If the arrow up key is pressed
            } else if(e.keyCode === 38) {
                doi2pmh.user.currentFocus --
                doi2pmh.user.addActive(items)

            // If the enter key is pressed
            } else if(e.keyCode === 13) {
                e.preventDefault()
                if (doi2pmh.user.currentFocus > -1) {
                    // simulate a click on the active item
                    if (items) items[doi2pmh.user.currentFocus].click()
                }
            }
        })
    },

    addActive: (items) => {
        if (!items) return false
        doi2pmh.user.removeActive(items)
        if (doi2pmh.user.currentFocus >= items.length) doi2pmh.user.currentFocus = 0
        if (doi2pmh.user.currentFocus < 0) doi2pmh.user.currentFocus = items.length - 1
        items[doi2pmh.user.currentFocus].classList.add("autocomplete-active")
    },

    removeActive: (items) => {
        items.forEach((item) => {
            item.classList.remove("autocomplete-active")
        })
    },

    initEditAdmin: () => {
        const forms = document.querySelectorAll('.isAdminForm')
        forms.forEach((form) => {
            form.addEventListener('change', (e) => {
                console.log(e.target.parentNode)
                e.target.parentNode.submit()
            })
        })
    },

    initApiToken: () => {
        const apiTokenModal = document.getElementById("apiTokenModalToggle")
        doi2pmh.user.apiTokenInput = document.getElementById("apiToken")
        apiTokenModal.addEventListener("click", doi2pmh.user.getApiToken)

        const apiTokenCopyButton = document.getElementById("apiTokenCopy")
        apiTokenCopyButton.addEventListener("click", doi2pmh.user.copyTokenToClipboard)
    },

    getApiToken: () => {
        $.get(
            {
                url: doi2pmh.user.baseUrlToken,
                success: (result) => {
                    doi2pmh.user.apiTokenInput.value = result
                }
            }
        ).done(() => {$("#apiTokenModal").modal('show')})
    },

    copyTokenToClipboard: () => {
        navigator.clipboard.writeText(doi2pmh.user.apiTokenInput.value)
    }
    
}

document.addEventListener("DOMContentLoaded", () => doi2pmh.user.init());
