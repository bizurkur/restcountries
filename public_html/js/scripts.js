'use strict';

(() => {
    class Lookup {
        /**
         * Looks up a country code or name.
         *
         * @param {string} term
         */
        lookup(term) {
            if (!term) {
                document.getElementById('results').innerHTML = 'Please enter a search term.';
                document.querySelector('.results-meta').classList.add('hidden');

                return;
            }

            fetch('/search?q='+encodeURIComponent(term))
                .then(res => res.json())
                .then(response => {
                    this.render(response);
                })
                .catch(err => {
                    alert('Sorry, there are no results for your search.')
                });
        }

        /**
         * Simplistically escape text so it's safe to render as HTML.
         *
         * @param {string} text
         *
         * @return {string}
         */
        escape(text) {
            const el = document.createElement('div');
            el.textContent = text;

            return el.innerHTML.replace(/'/g, '&#39;');
        }

        /**
         * Renders country data.
         *
         * @param {Array} countries
         */
        render(countries) {
            const container = document.getElementById('results');
            const footer = document.querySelector('.results-meta');

            if (!countries.length) {
                container.innerHTML = 'Sorry, there are no results for your search.';
                footer.classList.add('hidden');

                return;
            }

            container.innerHTML = '';
            footer.classList.remove('hidden');

            const regions = {};
            const subregions = {};

            countries.forEach(country => {
                country.region = country.region || '(Unidentified)';
                country.subregion = country.subregion || '(Unidentified)';

                if (!regions[country.region]) {
                    regions[country.region] = 0;
                }
                regions[country.region]++;

                if (!subregions[country.subregion]) {
                    subregions[country.subregion] = 0;
                }
                subregions[country.subregion]++;

                container.innerHTML += `<div class="card">
                        <div class="card-img">
                            <img src="${this.escape(country.flag)}" alt="Flag for ${this.escape(country.name)}">
                        </div>
                        <div class="card-content">
                            <div class="card-title">${this.escape(country.name)}</div>
                            <div class="card-alphas">
                                ${this.escape(country.alpha2Code)} / ${this.escape(country.alpha3Code)}
                            </div>
                            <div class="card-data">
                                <div><strong>Region:</strong> ${this.escape(country.region)}</div>
                                <div><strong>Subregion:</strong> ${this.escape(country.subregion)}</div>
                                <div><strong>Population:</strong> ${this.escape(country.population.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','))}</div>
                                <div><strong>Languages:</strong> ${this.escape(country.languages.map(lang => lang.name).join(', '))}</div>
                            </div>
                        </div>
                    </div>`;
            });

            const getMapKeys = (map) => {
                const keys = Object.keys(map);
                keys.sort();

                return keys;
            };

            footer.innerHTML = `
                    <div class="results-meta-count">Found <strong>${countries.length}</strong> result${countries.length === 1 ? '' : 's'}.</div>
                    <div class="results-meta-regions">
                        <div class="results-meta-title">Regions</div>
                        ${getMapKeys(regions).map(region => {
                            return `<div class="results-meta-data">${this.escape(region)}: <strong>${regions[region]}</strong></div>`;
                        }).join('')}
                    </div>
                    <div class="results-meta-subregions">
                        <div class="results-meta-title">Subregions</div>
                        ${getMapKeys(subregions).map(subregion => {
                            return `<div class="results-meta-data">${this.escape(subregion)}: <strong>${subregions[subregion]}</strong></div>`;
                        }).join('')}
                    </div>
                `;
        }
    }

    const form = document.querySelector('.search-form');
    if (form) {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            const input = document.getElementById('q');
            const term = (input.value || '').trim();
            const lookup = new Lookup();

            lookup.lookup(term);
        });
    }
})();
