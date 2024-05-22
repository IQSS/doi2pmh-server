
/**
 * Init autocomplete user with email
 * @type {*|{}}
 */
window.doi2pmh = window.doi2pmh || {};
doi2pmh.doi = window.doi2pmh.doi || {

    url: `/${doi2pmh.params.locale}/admin/configuration/refreshDoi`,

    init: () => {
        doi2pmh.doi.initRefreshDois()
    },

    /**
     * Add event for autocompletion
     */
    initRefreshDois: () => {
        if (! document.getElementById('refreshDois')) {
            return;
        }
        document.getElementById('refreshDois').addEventListener('click', (evt) => {
            evt.preventDefault()
            const source = new EventSource(doi2pmh.doi.url);
            source.addEventListener('message', (ev) => {
                const report = JSON.parse(ev.data);
                console.log(typeof report.progress)
                if (typeof report.progress !== 'undefined') {
                    const progressBar = document.getElementById('progress');
                    document.getElementById('refreshDois').style.display = 'none'
                    progressBar.querySelector('#progressContent').setAttribute('aria-valuenow', report.progress)
                    progressBar.querySelector('#progressContent').setAttribute('aria-valuemax', report.total)
                    progressBar.querySelector('#progressContent').style.width = (report.progress * 100 / report.total) + '%'
                    progressBar.querySelector('#progressContent').innerHTML = report.progress + ' / ' + report.total + ' DOI(s)'
                    document.getElementById('progress').style.display = ''
                    document.getElementById('reportSpan').innerHTML = report.progress + ' / ' + report.total + ' DOI(s) updated'
                }
            });
            source.onerror = function() {
                source.close()
                document.getElementById('refreshDois').style.display = ''
                document.getElementById('progress').style.display = 'none';
                document.getElementById('progress').querySelector('#progressContent').style.width = '0';
                document.getElementById('reportSpan').style.display = ''
            };
        })
    }
}

document.addEventListener("DOMContentLoaded", () => doi2pmh.doi.init());
