let input = document.querySelector('#cities');
let citiesSelect = new Choices(input, {
    searchResultLimit: 100,
    removeItemButton: true
});

input.addEventListener('search', function (event) {
    let searchQuery = event.detail.value.trim();
    if (searchQuery.length >= 3) {
        citiesSelect.clearChoices();
        search(searchQuery);
    }
});

function search(value) {
    citiesSelect.setChoices(async () => {
        try {
            const items = await fetch('/?search=' + value);
            return items.json();
        } catch (err) {
            console.error(err);
        }
    });
}

const formBtn = document.querySelector('button[name="computePtz"]');
formBtn.addEventListener('click', e => postForm(e));
formBtn.removeEventListener('click', postForm);

function postForm(e) {
    e.preventDefault();
    const form = document.querySelector('form#computePtz');
    const data = new FormData(form);
    data.append('compute', true);
    fetch('/', {
        method: 'POST',
        body: data
    })
        .then(response => response.json())
        .then(data => {
            document.querySelector('.results')
                .innerHTML = data.template;
            if (data.latLng) {
                initSelectionCheckboxes();
                initBtnRemove();
                initBtnExport();
                updateMap(data.latLng);
            }
        })
}


const map = L.map('map');
const markers = [];
L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
    attribution: 'données © <a href="//osm.org/copyright">OpenStreetMap</a>/ODbL - rendu <a href="//openstreetmap.fr">OSM France</a>',
    minZoom: 1,
    maxZoom: 20
}).addTo(map);

//Part with table results

function initBtnRemove() {
    const btnRemoveLines = document.querySelector('button.remove-cities');
    btnRemoveLines.addEventListener('click', e => removeCities(e));
    btnRemoveLines.addEventListener('click', removeCities);
}

function initBtnExport() {
    $('#results-table').DataTable({
        language: {
            processing: "Traitement en cours...",
            search: "Rechercher&nbsp;:",
            lengthMenu: "Afficher _MENU_ &eacute;l&eacute;ments",
            info: "Affichage de l'&eacute;lement _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
            infoEmpty: "Affichage de l'&eacute;lement 0 &agrave; 0 sur 0 &eacute;l&eacute;ments",
            infoFiltered: "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
            infoPostFix: "",
            loadingRecords: "Chargement en cours...",
            zeroRecords: "Aucun &eacute;l&eacute;ment &agrave; afficher",
            emptyTable: "Aucune donnée disponible dans le tableau",
            paginate: {
                first: "Premier",
                previous: "Pr&eacute;c&eacute;dent",
                next: "Suivant",
                last: "Dernier"
            },
            aria: {
                sortAscending: ": activer pour trier la colonne par ordre croissant",
                sortDescending: ": activer pour trier la colonne par ordre décroissant"
            }
        },
        //add "p" to do dom str to add pagination, and "i" to display the number of results
        dom: 'Bfrt',
        pageLength: Infinity,
        searching: true,
        responsive: true,
        buttons: [
            {
                extend: 'pdfHtml5',
                orientation: 'landscape',
                pageSize: 'LEGAL',
                exportOptions: {
                    columns: ':not(.d-print-none)'
                },
                customize: doc => {
                    doc.content[1].table.widths =
                        Array(doc.content[1].table.body[0].length + 1).join('*').split('');
                    doc.defaultStyle.alignment = 'center';
                }
            },
        ]
    });
}

function updateMap(latLng) {
    const mapElt = document.querySelector('#map');
    if (mapElt.classList.contains('hidden')) {
        mapElt.classList.remove('hidden');
    }
    map.setView(latLng, 11);

    //clean map markers
    cleanMarkers();
    document.querySelectorAll('.results table tbody tr')
        .forEach(tr => {
            const latitude = tr.getAttribute('data-lat');
            const longitude = tr.getAttribute('data-lng');
            const id = tr.getAttribute('data-id');

            const minPrice = tr.querySelector('[data-min-price]').textContent;
            const minPtz = tr.querySelector('[data-min-ptz]').textContent;
            const maxPrice = tr.querySelector('[data-max-price]').textContent;
            const maxPtz = tr.querySelector('[data-max-ptz]').textContent;

            let content = `Montant: ${minPrice}<br /> Max: ${maxPrice}<br /> PTZ: ${minPtz}`;
            if (maxPtz !== minPtz) {
                content = `Montant: ${minPrice}<br /> PTZ: ${minPtz}<br /> Max: ${maxPrice} <br /> PTZ: ${maxPtz}`;
            }

            makeMarker(id, latitude, longitude);
        })
}

function initSelectionCheckboxes() {
    document
        .querySelectorAll('table tbody tr')
        .forEach(tr => {
            tr.addEventListener('click', e => selectCheckbox(e));
            tr.removeEventListener('click', selectCheckbox);
        })
}

function selectCheckbox(e) {
    e.preventDefault();
    const target = e.target;
    const tr = target.closest('tr');
    const checkbox = tr.querySelector('input[type=checkbox]');
    checkbox.checked = !(true === checkbox.checked);
}

function removeCities(e) {
    e.preventDefault();
    document
        .querySelectorAll('div.results table tbody td input[type=checkbox]:checked')
        .forEach(checkbox => {
            const tr = checkbox.closest('tr');
            const id = tr.getAttribute('data-id');
            removeMarker(id);
            checkbox.closest('tr').remove();
        })
}

function removeMarker(id) {
    markers[id].remove();
    markers.slice(id, 1);
}

function cleanMarkers() {
    markers.forEach(marker => {
        marker.remove();
    });
    markers.pop();
}

function makeMarker(id, latitude, longitude) {
    const marker = L.marker(L.latLng(latitude, longitude));
    markers[id] = marker;
    marker
        .addTo(map)
        .bindPopup(content);
}

