
/**
 * Init autocomplete user with email
 * @type {*|{}}
 */
window.doi2pmh = window.doi2pmh || {};
doi2pmh.configuration = window.doi2pmh.configuration || {
    init: () => {
        doi2pmh.configuration.initExcludedTypes()
    },
    initExcludedTypes: () => {
        const excludedTypesHolder = document.getElementById("excluded-types")
        const excludedTypesAddButton = document.getElementById('excluded-types-add')
        if (!excludedTypesAddButton) {
            return;
        }
        excludedTypesAddButton
            .addEventListener("click", (e) => doi2pmh.configuration.addFormToCollection(excludedTypesHolder))
        const excludedTypes = excludedTypesHolder.querySelectorAll('li')
        if (excludedTypes.length){
            excludedTypes.forEach((excludedType) => {
                doi2pmh.configuration.addFormDeleteLink(excludedType)
            })
        } else {
            doi2pmh.configuration.addFormToCollection(excludedTypesHolder)
        }
            
    },
    addFormToCollection: (collectionHolder) => {
      
        const item = document.createElement('li')
      
        item.innerHTML = collectionHolder
          .dataset
          .prototype
          .replace(
            /__name__/g,
            collectionHolder.dataset.index
          );
        item.classList.add("input-group")
      
        collectionHolder.appendChild(item)
        doi2pmh.configuration.addFormDeleteLink(item)      
        collectionHolder.dataset.index++
      },
      addFormDeleteLink: (item) => {
        const removeFormButton = document.getElementById('excluded-types-delete').cloneNode(true)
        removeFormButton.id = null
        removeFormButton.classList.remove("d-none")

        item.append(removeFormButton);

        removeFormButton.addEventListener('click', (e) => {
            e.preventDefault();
            item.remove();
    });
    }
}

document.addEventListener("DOMContentLoaded", () => doi2pmh.configuration.init());
