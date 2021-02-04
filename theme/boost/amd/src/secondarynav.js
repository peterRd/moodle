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
 * Toggling the visibility of the secondary navigation on mobile.
 *
 * @package    theme_boost
 * @copyright  2021 Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {classUtil} from 'core/utils';
import ModalBackdrop from 'core/modal_backdrop';
import Templates from 'core/templates';

let backdropPromise = 0;

const getBackdrop = () => {
    if (!backdropPromise) {
        backdropPromise = Templates.render('core/modal_backdrop', {})
            .then(function(html) {
                return new ModalBackdrop(html);
            })
            .fail(Notification.exception);
    }
    return backdropPromise;
};

const sel = {
    ids: {
        secondarynav: '.secondarynavcontainer',
        pagecontent: '#page-wrapper'
    },
    actions: {
        togglebutton: '[data-action="view-secondary-menu"]',
        closebutton: '[data-action="close-secondary-menu"]'
    }
};

const closeNav = () => {
    const secondaryNav = document.querySelector(sel.ids.secondarynav);
    const pageContent = document.querySelector(sel.ids.pagecontent);

    classUtil('remove', secondaryNav, 'show');
    getBackdrop().then(backdrop => {
        backdrop.hide();
        pageContent.style.overflow = 'auto';
    });
};

const showNav = () => {
    const secondaryNav = document.querySelector(sel.ids.secondarynav);
    const pageContent = document.querySelector(sel.ids.pagecontent);

    classUtil('add', secondaryNav, 'show');
    getBackdrop().then(backdrop => {
        backdrop.setZIndex(1020);
        backdrop.show();
        pageContent.style.overflow = 'hidden';
    });
};

export const togglenavigation = () => {
    const toggleButton = document.querySelector(sel.actions.togglebutton);
    const closeButton = document.querySelector(sel.actions.closebutton);
    const secondaryNav = document.querySelector(sel.ids.secondarynav);

    if (toggleButton) {
        toggleButton.addEventListener('click', () => {
            if (classUtil('has', secondaryNav, 'show')) {
                closeNav();
            } else {
                showNav();
            }
        });
    }
    if (closeButton) {
        closeButton.addEventListener('click', () => {
            closeNav();
        });
    }

    window.addEventListener('resize', function () {
        if (window.outerWidth > 768) {
            closeNav();
        }
    });
};
