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
            if (data.hasData) {
                initSelectionCheckboxes();
                initBtnRemove();
                initBtnExport();
                updateMap();
            }
        })
}


const map = L.map('map');
const markers = [];
let table = null;
L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
    attribution: 'données © <a href="//osm.org/copyright">OpenStreetMap</a>/ODbL - rendu <a href="//openstreetmap.fr">OSM France</a>',
    minZoom: 5,
    maxZoom: 20
}).addTo(map);

//Part with table results

function initBtnRemove() {
    document
        .querySelectorAll('button.remove-cities')
        .forEach(btn => {
            btn.addEventListener('click', e => removeCities(e));
            btn.addEventListener('click', removeCities);
        })
}

function initBtnExport() {
    table = $('#results-table').DataTable({
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
                    columns: ':not(.not-export)'
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

function updateMap() {
    const mapElt = document.querySelector('#map');
    if (mapElt.classList.contains('hidden')) {
        mapElt.classList.remove('hidden');
    }

    //clean map markers
    cleanMarkers();
    const latLngs = [];
    document.querySelectorAll('.results table tbody tr')
        .forEach(tr => {
            const trDataSet = tr.dataset;
            console.log(tr);

            const ptz = {
                city : {
                    id: trDataSet.id,
                    lat: trDataSet.lat,
                    lng: trDataSet.lng,
                    name: tr.querySelector('[data-city]').textContent
                },
                minPrice: tr.querySelector('[data-min-price]').textContent,
                minPtz: tr.querySelector('[data-min-ptz]').textContent,
                maxPrice: tr.querySelector('[data-max-price]').textContent,
                maxPtz: tr.querySelector('[data-max-ptz]').textContent
            };

            makeMarker(ptz);
            latLngs.push([ptz.city.lat, ptz.city.lng]);
        });

    1 === latLngs.length
        ? map.setView(latLngs[0], 11)
        : map.fitBounds(L.latLngBounds(latLngs));
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
            table
                .row(tr)
                .remove()
                .draw();
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

function makeMarker(ptz) {
    let popUpContent = `<strong>${ptz.city.name}</strong><br />`;
    let content = `Montant: ${ptz.minPrice}<br /> Max: ${ptz.maxPrice}<br /> PTZ: ${ptz.minPtz}`;
    if (ptz.maxPrice !== ptz.minPrice) {
        content = `Montant: ${ptz.minPrice}<br /> PTZ: ${ptz.minPtz}<br /> Max: ${ptz.maxPrice} <br /> PTZ: ${ptz.maxPtz}`;
    }

    popUpContent += content;

    const marker = L.marker(L.latLng(ptz.city.lat, ptz.city.lng));
    markers[ptz.city.id] = marker;
    marker
        .addTo(map)
        .bindPopup(popUpContent);
}

