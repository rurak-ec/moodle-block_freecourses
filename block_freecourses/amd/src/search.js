// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Client-side search filtering for the Free courses block.
 *
 * @module     block_freecourses/search
 * @copyright  2026 Rurak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {debounce} from 'core/utils';

const SELECTORS = {
    SEARCH: '[data-region="freecourses-search"] [data-action="search"]',
    CLEAR: '[data-region="freecourses-search"] [data-action="clearsearch"]',
    CARDSWRAPPER: '[data-region="freecourses-cards-wrapper"]',
    NORESULTS: '[data-region="freecourses-noresults"]',
    CARDS: '[data-region="card-deck"] [data-region="course-content"]',
    CARDSFALLBACK: '[data-region="card-deck"] .course-card',
    COURSENAME: '.coursename',
};

const SEARCH_DELAY = 300;

/**
 * Normalise a string for case- and accent-insensitive comparison.
 *
 * @param {string} value The raw value.
 * @return {string} The normalised value.
 */
const normalise = (value) => {
    let text = (value || '').toString().toLowerCase().trim();
    if (typeof text.normalize === 'function') {
        text = text.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }
    return text;
};

/**
 * Initialise the search/filter behaviour for a single block instance.
 *
 * @param {string} uniqid The unique id shared with the rendered template.
 */
export const init = (uniqid) => {
    const root = document.getElementById('block-freecourses-' + uniqid);
    if (!root) {
        return;
    }

    const input = root.querySelector(SELECTORS.SEARCH);
    const clearicon = root.querySelector(SELECTORS.CLEAR);
    const cardswrapper = root.querySelector(SELECTORS.CARDSWRAPPER);
    const noresults = root.querySelector(SELECTORS.NORESULTS);

    if (!input || !clearicon || !cardswrapper || !noresults) {
        return;
    }

    const getCards = () => {
        let cards = root.querySelectorAll(SELECTORS.CARDS);
        if (!cards.length) {
            cards = root.querySelectorAll(SELECTORS.CARDSFALLBACK);
        }
        return Array.prototype.map.call(cards, (card) => card.closest('.col') || card);
    };

    const showAll = () => {
        getCards().forEach((card) => card.classList.remove('d-none'));
        cardswrapper.classList.remove('d-none');
        noresults.classList.add('d-none');
    };

    const filterCards = (term) => {
        const searchterm = normalise(term);
        let visiblecount = 0;
        getCards().forEach((card) => {
            const titleelement = card.querySelector(SELECTORS.COURSENAME);
            const title = normalise(titleelement ? titleelement.textContent : '');
            const visible = !searchterm || title.indexOf(searchterm) !== -1;
            card.classList.toggle('d-none', !visible);
            if (visible) {
                visiblecount++;
            }
        });
        const empty = visiblecount === 0;
        cardswrapper.classList.toggle('d-none', empty);
        noresults.classList.toggle('d-none', !empty);
    };

    const clearSearch = () => {
        clearicon.classList.add('d-none');
        showAll();
    };

    clearicon.addEventListener('click', (e) => {
        e.preventDefault();
        input.value = '';
        input.focus();
        clearSearch();
    });

    input.addEventListener('input', debounce(() => {
        const value = input.value.trim();
        if (value === '') {
            clearSearch();
            return;
        }
        clearicon.classList.remove('d-none');
        filterCards(value);
    }, SEARCH_DELAY));
};
